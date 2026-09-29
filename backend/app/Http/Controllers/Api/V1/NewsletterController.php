<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email:rfc', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'source' => ['nullable', 'string', 'max:64'],
        ]);

        $subscriber = NewsletterSubscriber::firstOrNew(['email' => $validated['email']]);

        // Re-subscribing after an opt-out is a normal thing to do, so clear
        // the unsubscribe rather than treating the address as spent.
        $subscriber->fill([
            'name' => $validated['name'] ?? $subscriber->name,
            'source' => $validated['source'] ?? $subscriber->source ?? 'storefront',
            'subscribed_at' => $subscriber->subscribed_at ?? now(),
            'unsubscribed_at' => null,
        ])->save();

        return response()->json([
            'message' => 'Thank you. Please check your inbox to confirm.',
        ], 201);
    }

    public function destroy(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email:rfc'],
        ]);

        NewsletterSubscriber::where('email', $validated['email'])
            ->update(['unsubscribed_at' => now()]);

        // Always the same response: confirming which addresses are on the list
        // would leak them to anyone who cares to probe.
        return response()->json(['message' => 'You have been unsubscribed.']);
    }
}
