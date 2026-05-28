<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    public function faqs(Request $request)
    {
        return response()->json([
            [
                'category' => __('Membership'),
                'questions' => [
                    [
                        'q' => __('How to cancel?'),
                        'a' => __('You can cancel in the app under profile settings or contact support.'),
                    ],
                    [
                        'q' => __('Can I pause my membership?'),
                        'a' => __('Yes, memberships can be paused for up to 3 months.'),
                    ],
                ],
            ],
            [
                'category' => __('Classes'),
                'questions' => [
                    [
                        'q' => __('What is the cancellation policy?'),
                        'a' => __('Classes must be cancelled at least 2 hours before start.'),
                    ],
                ],
            ],
        ]);
    }

    public function storeTicket(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        /** @var \App\Models\User $user */
        $user = auth()->user();

        // In a real app, this would create a SupportTicket model
        // For now, we'll just simulate success

        return response()->json([
            'status' => 'success',
            'message' => __('Support ticket created successfully'),
            'ticket_id' => rand(1000, 9999),
        ], 201);
    }
}
