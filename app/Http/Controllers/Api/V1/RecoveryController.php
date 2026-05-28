<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RecoveryController extends Controller
{
    public function show(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $score = $user->recovery_score ?? 70;

        $status = 'green';
        $message = __('You\'re ready for heavy training today');
        $insights = [];

        if ($score < 40) {
            $status = 'red';
            $message = __('Your recovery is low. Consider a rest day.');
            $insights[] = __('High training load recently');
        } elseif ($score < 70) {
            $status = 'yellow';
            $message = __('Moderate recovery. Listen to your body.');
            $insights[] = __('Slightly lower HRV than usual');
        } else {
            $insights[] = __('Sleep was better than average');
            $insights[] = __('HRV is stable');
        }

        return response()->json([
            'score' => $score,
            'status' => $status,
            'message' => $message,
            'insights' => $insights,
        ]);
    }
}
