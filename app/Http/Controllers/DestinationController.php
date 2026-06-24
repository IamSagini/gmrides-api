<?php
namespace App\Http\Controllers;

use App\Models\Destination;
use Illuminate\Http\Request;

class DestinationController extends Controller
{
    public function index() {
        $destinations = Destination::orderBy('created_at', 'desc')->get();
        return response()->json(['success' => true, 'destinations' => $destinations]);
    }

    public function store(Request $request) {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'location'      => 'nullable|string|max:255',
            'from_location' => 'nullable|string|max:255',
            'to_location'   => 'nullable|string|max:255',
            'description'   => 'nullable|string',
            'image_url'     => 'nullable|string|max:1000',
            'tag'           => 'nullable|string|max:100',
        ]);
        $destination = Destination::create($data);
        return response()->json(['success' => true, 'destination' => $destination]);
    }

    public function update(Request $request, $id) {
        $destination = Destination::findOrFail($id);
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'location'      => 'nullable|string|max:255',
            'from_location' => 'nullable|string|max:255',
            'to_location'   => 'nullable|string|max:255',
            'description'   => 'nullable|string',
            'image_url'     => 'nullable|string|max:1000',
            'tag'           => 'nullable|string|max:100',
        ]);
        $destination->update($data);
        return response()->json(['success' => true, 'destination' => $destination]);
    }

    public function destroy($id) {
        $destination = Destination::findOrFail($id);
        $destination->delete();
        return response()->json(['success' => true]);
    }
}
