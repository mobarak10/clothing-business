<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AppointmentController extends Controller
{
    /** Boutique visits and made-to-measure fittings. */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'store_id' => ['required', 'exists:stores,id'],
            'type' => ['required', Rule::in(['boutique', 'made_to_measure'])],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'phone' => ['required', 'string', 'max:32'],
            'preferred_at' => ['required', 'date', 'after:+2 hours'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ], [
            'preferred_at.after' => 'Please choose a time at least two hours from now.',
        ]);

        $store = Store::active()->findOrFail($validated['store_id']);

        // Not every boutique has a Made to Measure room.
        if ($validated['type'] === 'made_to_measure' && ! $store->offers_made_to_measure) {
            throw ValidationException::withMessages([
                'store_id' => "{$store->name} does not offer Made to Measure appointments.",
            ]);
        }

        $appointment = Appointment::create($validated + [
            'user_id' => $request->user()?->id,
            'status' => 'requested',
        ]);

        return response()->json([
            'message' => 'Your request has been received. The boutique will confirm by email.',
            'data' => [
                'id' => $appointment->id,
                'store' => $store->name,
                'preferred_at' => $appointment->preferred_at->toIso8601String(),
            ],
        ], 201);
    }
}
