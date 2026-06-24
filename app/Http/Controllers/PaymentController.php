<?php
namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use AfricasTalking\SDK\AfricasTalking;

class PaymentController extends Controller {

    // ─── M-PESA ───────────────────────────────────────────────
    private function getMpesaToken() {
        $key    = env('MPESA_CONSUMER_KEY');
        $secret = env('MPESA_CONSUMER_SECRET');
        $env    = env('MPESA_ENV', 'sandbox');
        $url    = $env === 'production'
            ? 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials'
            : 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';

        $response = Http::withBasicAuth($key, $secret)->timeout(15)->connectTimeout(15)->get($url);
        return $response->json()['access_token'] ?? null;
    }

    public function mpesaInitiate(Request $request) {
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'phone'      => 'required|string',
            'amount'     => 'required|numeric',
        ]);

        $booking   = Booking::findOrFail($request->booking_id);
        $token     = $this->getMpesaToken();
        $env       = env('MPESA_ENV', 'sandbox');
        $shortcode = env('MPESA_SHORTCODE');
        $passkey   = env('MPESA_PASSKEY');
        $timestamp = now()->format('YmdHis');
        $password  = base64_encode($shortcode . $passkey . $timestamp);
        $phone     = preg_replace('/^0/', '254', preg_replace('/^\+/', '', $request->phone));
        $amount    = (int) ceil($request->amount);

        $url = $env === 'production'
            ? 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest'
            : 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';

        $response = Http::withToken($token)->timeout(15)->connectTimeout(15)->post($url, [
            'BusinessShortCode' => $shortcode,
            'Password'          => $password,
            'Timestamp'         => $timestamp,
            'TransactionType'   => 'CustomerPayBillOnline',
            'Amount'            => $amount,
            'PartyA'            => $phone,
            'PartyB'            => $shortcode,
            'PhoneNumber'       => $phone,
            'CallBackURL'       => env('MPESA_CALLBACK_URL'),
            'AccountReference'  => $booking->booking_reference,
            'TransactionDesc'   => "GM Rides - {$booking->from_location} to {$booking->to_location}",
        ]);

        $data = $response->json();

        if (isset($data['ResponseCode']) && $data['ResponseCode'] === '0') {
            Payment::create([
                'booking_id'        => $booking->id,
                'payment_method'    => 'mpesa',
                'gateway_reference' => $data['CheckoutRequestID'],
                'amount'            => $amount,
                'currency'          => 'KES',
                'status'            => 'pending',
                'gateway_response'  => $data,
            ]);
            return response()->json(['success' => true, 'message' => 'STK Push sent. Enter your M-Pesa PIN.', 'checkout_request_id' => $data['CheckoutRequestID']]);
        }

        return response()->json(['success' => false, 'message' => 'M-Pesa request failed. Please try again.', 'data' => $data], 400);
    }

    public function mpesaCallback(Request $request) {
        $data   = $request->all();
        $result = $data['Body']['stkCallback'] ?? null;
        if (!$result) return response()->json(['success' => false]);

        $checkoutId = $result['CheckoutRequestID'];
        $resultCode = $result['ResultCode'];

        $payment = Payment::where('gateway_reference', $checkoutId)->first();
        if (!$payment) return response()->json(['success' => false]);

        if ($resultCode === 0) {
            $items = collect($result['CallbackMetadata']['Item']);
            $transactionId = $items->firstWhere('Name', 'MpesaReceiptNumber')['Value'] ?? null;
            $payment->update(['status' => 'completed', 'transaction_id' => $transactionId, 'paid_at' => now(), 'gateway_response' => $data]);
            $payment->booking->update(['status' => 'confirmed']);
            $this->sendSmsConfirmation($payment->booking);
        } else {
            $payment->update(['status' => 'failed', 'gateway_response' => $data]);
        }

        return response()->json(['success' => true]);
    }

    public function mpesaQuery(Request $request) {
        $request->validate(['checkout_request_id' => 'required|string']);
        $token     = $this->getMpesaToken();
        $env       = env('MPESA_ENV', 'sandbox');
        $shortcode = env('MPESA_SHORTCODE');
        $passkey   = env('MPESA_PASSKEY');
        $timestamp = now()->format('YmdHis');
        $password  = base64_encode($shortcode . $passkey . $timestamp);

        $url = $env === 'production'
            ? 'https://api.safaricom.co.ke/mpesa/stkpushquery/v1/query'
            : 'https://sandbox.safaricom.co.ke/mpesa/stkpushquery/v1/query';

        $response = Http::withToken($token)->timeout(15)->connectTimeout(15)->post($url, [
            'BusinessShortCode' => $shortcode,
            'Password'          => $password,
            'Timestamp'         => $timestamp,
            'CheckoutRequestID' => $request->checkout_request_id,
        ]);

        $data = $response->json();
        $payment = Payment::where('gateway_reference', $request->checkout_request_id)->first();

        // ResultCode 0 means the STK payment completed successfully
        if (isset($data['ResultCode']) && (string) $data['ResultCode'] === '0' && $payment && $payment->status !== 'completed') {
            $payment->update(['status' => 'completed', 'paid_at' => now(), 'gateway_response' => $data]);
            $payment->booking->update(['status' => 'confirmed']);
            $this->sendSmsConfirmation($payment->booking);
        }

        $payment = $payment ? $payment->fresh() : null;
        return response()->json(['success' => true, 'status' => $payment?->status, 'booking_status' => $payment?->booking?->status, 'data' => $data]);
    }

    // ─── STRIPE ───────────────────────────────────────────────
    public function stripeIntent(Request $request) {
        $request->validate(['booking_id' => 'required|exists:bookings,id', 'amount' => 'required|numeric', 'currency' => 'required|string']);
        $booking = Booking::findOrFail($request->booking_id);

        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

        $rates    = ['KES' => 1, 'GBP' => 0.0058, 'USD' => 0.0077, 'EUR' => 0.0071];
        $currency = strtolower($request->currency);
        $amount   = $currency === 'kes'
            ? (int)($request->amount * 100)
            : (int)($request->amount * ($rates[strtoupper($request->currency)] ?? 1) * 100);

        $intent = \Stripe\PaymentIntent::create([
            'amount'   => $amount,
            'currency' => $currency === 'kes' ? 'kes' : $currency,
            'metadata' => ['booking_reference' => $booking->booking_reference],
        ]);

        Payment::create([
            'booking_id'        => $booking->id,
            'payment_method'    => 'card',
            'gateway_reference' => $intent->id,
            'amount'            => $request->amount,
            'currency'          => strtoupper($request->currency),
            'status'            => 'pending',
            'gateway_response'  => ['intent_id' => $intent->id],
        ]);

        return response()->json(['success' => true, 'client_secret' => $intent->client_secret, 'intent_id' => $intent->id]);
    }
    public function stripeConfirm(Request $request) {
        $request->validate(['intent_id' => 'required|string']);
        $payment = Payment::where('gateway_reference', $request->intent_id)->first();
        if (! $payment) {
            return response()->json(['success' => false, 'message' => 'Payment not found'], 404);
        }

        // Verify with Stripe that the intent actually succeeded
        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
        $intent = \Stripe\PaymentIntent::retrieve($request->intent_id);

        if ($intent->status === 'succeeded' && $payment->status !== 'completed') {
            $payment->update(['status' => 'completed', 'transaction_id' => $intent->id, 'paid_at' => now()]);
            $payment->booking->update(['status' => 'confirmed']);
            $this->sendSmsConfirmation($payment->booking);
        }

        $payment = $payment->fresh();
        return response()->json([
            'success' => true,
            'status' => $payment->status,
            'booking_status' => $payment->booking->status,
        ]);
    }


    public function stripeWebhook(Request $request) {
        $payload = $request->getContent();
        $sig     = $request->header('Stripe-Signature');

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sig, env('STRIPE_WEBHOOK_SECRET'));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }

        if ($event->type === 'payment_intent.succeeded') {
            $intent  = $event->data->object;
            $payment = Payment::where('gateway_reference', $intent->id)->first();
            if ($payment) {
                $payment->update(['status' => 'completed', 'transaction_id' => $intent->id, 'paid_at' => now()]);
                $payment->booking->update(['status' => 'confirmed']);
                $this->sendSmsConfirmation($payment->booking);
            }
        }

        return response()->json(['success' => true]);
    }

    // ─── PAYPAL ───────────────────────────────────────────────
    public function paypalCreateOrder(Request $request) {
        $request->validate(['booking_id' => 'required|exists:bookings,id', 'amount' => 'required|numeric', 'currency' => 'required|string']);
        $booking = Booking::findOrFail($request->booking_id);

        $token    = $this->getPaypalToken();
        $env      = env('PAYPAL_MODE', 'sandbox');
        $base     = $env === 'production' ? 'https://api.paypal.com' : 'https://api.sandbox.paypal.com';
        $rates    = ['KES' => 1, 'GBP' => 0.0058, 'USD' => 0.0077, 'EUR' => 0.0071];
        $currency = strtoupper($request->currency) === 'KES' ? 'USD' : strtoupper($request->currency);
        $amount   = strtoupper($request->currency) === 'KES'
            ? number_format($request->amount * 0.0077, 2, '.', '')
            : number_format($request->amount, 2, '.', '');

        $response = Http::withToken($token)->post("{$base}/v2/checkout/orders", [
            'intent'         => 'CAPTURE',
            'purchase_units' => [[
                'reference_id' => $booking->booking_reference,
                'amount'       => ['currency_code' => $currency, 'value' => $amount],
                'description'  => "GM Rides - {$booking->from_location} to {$booking->to_location}",
            ]],
            'application_context' => [
                'return_url' => env('FRONTEND_URL') . '/booking/success',
                'cancel_url' => env('FRONTEND_URL') . '/booking/cancel',
            ],
        ]);

        $data = $response->json();

        Payment::create([
            'booking_id'        => $booking->id,
            'payment_method'    => 'paypal',
            'gateway_reference' => $data['id'],
            'amount'            => $request->amount,
            'currency'          => strtoupper($request->currency),
            'status'            => 'pending',
            'gateway_response'  => $data,
        ]);

        $approvalUrl = collect($data['links'])->firstWhere('rel', 'approve')['href'] ?? null;
        return response()->json(['success' => true, 'order_id' => $data['id'], 'approval_url' => $approvalUrl]);
    }

    public function paypalCapture(Request $request) {
        $request->validate(['order_id' => 'required|string']);
        $token = $this->getPaypalToken();
        $env   = env('PAYPAL_MODE', 'sandbox');
        $base  = $env === 'production' ? 'https://api.paypal.com' : 'https://api.sandbox.paypal.com';

        $response = Http::withToken($token)->post("{$base}/v2/checkout/orders/{$request->order_id}/capture");
        $data     = $response->json();

        if ($data['status'] === 'COMPLETED') {
            $payment = Payment::where('gateway_reference', $request->order_id)->first();
            if ($payment) {
                $payment->update(['status' => 'completed', 'transaction_id' => $data['id'], 'paid_at' => now(), 'gateway_response' => $data]);
                $payment->booking->update(['status' => 'confirmed']);
                $this->sendSmsConfirmation($payment->booking);
            }
            return response()->json(['success' => true, 'message' => 'Payment completed']);
        }

        return response()->json(['success' => false, 'message' => 'Payment not completed'], 400);
    }

    private function getPaypalToken() {
        $env    = env('PAYPAL_MODE', 'sandbox');
        $base   = $env === 'production' ? 'https://api.paypal.com' : 'https://api.sandbox.paypal.com';
        $res    = Http::withBasicAuth(env('PAYPAL_CLIENT_ID'), env('PAYPAL_CLIENT_SECRET'))
            ->asForm()->post("{$base}/v1/oauth2/token", ['grant_type' => 'client_credentials']);
        return $res->json()['access_token'] ?? null;
    }

    // ─── SMS ──────────────────────────────────────────────────
    private function sendSmsConfirmation(Booking $booking) {
        try {
            $AT  = new AfricasTalking(env('AT_USERNAME'), env('AT_API_KEY'));
            $sms = $AT->sms();
            $msg = "GM Rides Booking Confirmed!\nRef: {$booking->booking_reference}\nRoute: {$booking->from_location} to {$booking->to_location}\nDate: {$booking->travel_date}\nSeats: " . implode(', ', $booking->seats) . "\nHave a safe journey!";
            $sms->send(['to' => $booking->passenger_phone, 'message' => $msg]);
        } catch (\Exception $e) {
            Log::error('SMS failed: ' . $e->getMessage());
        }
    }
}
