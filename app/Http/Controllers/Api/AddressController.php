<?php

namespace App\Http\Controllers\Api;

use App\Helper\Countries;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AddressController
{
    public function store(Request $request)
    {
        $countries = Countries::LIST;
        $countryKeys = array_keys($countries);
        $user = $request->user();
        $validated = $request->validate([
            'country' => ['required', 'string', 'max:2', Rule::in($countryKeys)],
            'city' => 'required|string|min:2|max:50',
            'street' => 'required|string|min:5|max:255',
            'is_default' => 'sometimes|boolean',
            'note' => 'sometimes|string|min:5|max:255',
            'latitude' => 'sometimes|numeric',
            'longitude' => 'sometimes|numeric',
        ]);
        $validated['user_id'] = $user->id;

        $has_address = $user->addresses()->exists();
        if (! $has_address) {
            $validated['is_default'] = true;
            $new_address = Address::create($validated);

            return $new_address;
        }

        $address = $user->addresses()->where('is_default', true)->first();
        if ($address->is_default == true && $validated['is_default'] == true) {
            $address->update(['is_default' => false]);
            $new_address = Address::create($validated);

            return $new_address;
        } else {
            $new_address = Address::create($validated);

            return $new_address;
        }

    }

    public function index(Request $request)
    {
        return $request->user()->addresses()->get();
    }

    public function show(Request $request, Address $address)
    {
        abort_if($request->user()->id != $address->user_id, 403, 'You are not allowed to access this address.');

        return $address;
    }

    public function update(Request $request, Address $address)
    {
        abort_if($request->user()->id != $address->user_id, 403, 'You are not allowed to access this address.');
        $countries = Countries::LIST;
        $countryKeys = array_keys($countries);
        $validated = $request->validate([
            'country' => ['sometimes', 'string', 'max:2', Rule::in($countryKeys)],
            'city' => 'sometimes|string|min:2|max:50',
            'street' => 'sometimes|string|min:5|max:255',
            'is_default' => 'sometimes|boolean:true',
            'note' => 'sometimes|string|min:5|max:255',
            'latitude' => 'sometimes|numeric',
            'longitude' => 'sometimes|numeric',
        ]);

        if (isset($validated['is_default']) && $validated['is_default'] != true) {
            unset($validated['is_default']);
        }

        if (isset($validated['is_default']) && $validated['is_default']) {
            $request->user()->addresses()->where('is_default', true)->update(['is_default' => false]);
        }

        $address->update($validated);

        return $address->fresh();
    }

    public function destroy(Request $request, Address $address)
    {
        abort_if($request->user()->id != $address->user_id, 403, 'You are not allowed to access this address.');
        if ($address->is_default) {
            return response()->json(['message' => "can't delete default address"], 400);
        }
        $address->delete();

        return response()->json([], 204);
    }
}
