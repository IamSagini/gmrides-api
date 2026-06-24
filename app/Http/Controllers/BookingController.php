<?php
namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller {

    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'from_location'    => 'required|string',
            'to_location'      => 'required|string',
            'trip_type'        => 'required|string',
            'travel_date'      => 'required|date',
            'departure_time'   => 'required|string',
            'arrival_time'     => 'required|string',
            'duration'         => 'required|string',
            'passengers'       => 'required|integer|min:1',
            'seats'            => 'required|array',
            'passenger_name'   => 'required|string',
            'passenger_email'  => 'required|email',
            'passenger_phone'  => 'required|string',
            'fare_kes'         => 'required|numeric',
            'total_kes'        => 'required|numeric',
            'currency'         => 'required|string',
            'total_paid'       => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        // Server-side authoritative pricing
        $fare = (float) $request->fare_kes;
        $passengers = (int) $request->passengers;
        $baseTotal = $fare * $passengers;

        // Loyalty: 10% off if this phone has 6+ confirmed trips
        $confirmedTrips = Booking::where('passenger_phone', $request->passenger_phone)
            ->where('status', 'confirmed')
            ->count();
        $discountPercent = $confirmedTrips >= 6 ? 10 : 0;
        $discountAmount = round($baseTotal * $discountPercent / 100, 2);
        $finalTotal = $baseTotal - $discountAmount;

        $booking = Booking::create(array_merge($request->all(), [
            'user_id'          => optional(\Laravel\Sanctum\PersonalAccessToken::findToken($request->bearerToken() ?? ''))->tokenable_id,
            'total_kes'        => $finalTotal,
            'total_paid'       => $finalTotal,
            'discount_percent' => $discountPercent,
            'discount_amount'  => $discountAmount,
            'status'           => 'pending',
        ]));

        return response()->json([
            'success' => true,
            'booking' => $booking,
            'discount_percent' => $discountPercent,
            'discount_amount' => $discountAmount,
            'message' => 'Booking created successfully'
        ], 201);
    }
    // Loyalty: count confirmed bookings for a phone, return discount eligibility
    public function loyalty(Request $request) {
        $phone = $request->query('phone');
        if (! $phone) {
            return response()->json(['success' => false, 'message' => 'Phone required'], 422);
        }

        $confirmedTrips = Booking::where('passenger_phone', $phone)
            ->where('status', 'confirmed')
            ->count();

        $qualifies = $confirmedTrips >= 6;

        return response()->json([
            'success' => true,
            'confirmed_trips' => $confirmedTrips,
            'qualifies' => $qualifies,
            'discount_percent' => $qualifies ? 10 : 0,
            'trips_until_discount' => $qualifies ? 0 : max(0, 6 - $confirmedTrips),
        ]);
    }
    // Authenticated user's own bookings + loyalty status
    public function myBookings(Request $request) {
        $user = $request->user();

        // Match bookings by user_id OR by the account's email (covers guest bookings made before linking)
        $bookings = Booking::where('user_id', $user->id)
            ->orWhere('passenger_email', $user->email)
            ->orderBy('created_at', 'desc')
            ->get();

        // Loyalty counts confirmed trips by the phone numbers this user has booked with
        $phones = $bookings->pluck('passenger_phone')->filter()->unique();
        $confirmedTrips = Booking::whereIn('passenger_phone', $phones)
            ->where('status', 'confirmed')
            ->count();

        $qualifies = $confirmedTrips >= 6;

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'bookings' => $bookings,
            'loyalty' => [
                'confirmed_trips' => $confirmedTrips,
                'qualifies' => $qualifies,
                'discount_percent' => $qualifies ? 10 : 0,
                'trips_until_discount' => $qualifies ? 0 : max(0, 6 - $confirmedTrips),
            ],
        ]);
    }



    public function show($reference) {
        $booking = Booking::with('payment')->where('booking_reference', $reference)->firstOrFail();
        return response()->json(['success' => true, 'booking' => $booking]);
    }

    public function index(Request $request) {
        $bookings = Booking::with('payment')
            ->where('passenger_email', $request->email)
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json(['success' => true, 'bookings' => $bookings]);
    }

    public function adminBookings() {
        $bookings = Booking::with('payment')
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json(['success' => true, 'bookings' => $bookings]);
    }

    public function adminUsers() {
        $users = User::orderBy('created_at', 'desc')->get()->map(function ($u) {
            $bookings = Booking::where('user_id', $u->id)
                ->orWhere('passenger_email', $u->email)
                ->get();
            $totalSpent = $bookings->where('status', 'confirmed')->sum('total_kes');
            return [
                'id'             => $u->id,
                'name'           => $u->name,
                'email'          => $u->email,
                'role'           => $u->role,
                'created_at'     => $u->created_at,
                'bookings_count' => $bookings->count(),
                'total_spent'    => (float) $totalSpent,
            ];
        });
        return response()->json(['success' => true, 'users' => $users]);
    }
}
