<?php

namespace App\Http\Controllers;

use App\Models\EmailLog;

class EmailTrackingController extends Controller
{
    public function open(string $trackingId)
    {
        $log = EmailLog::where('tracking_id', $trackingId)->first();

        if ($log && ! $log->opened_at) {
            $log->update(['opened_at' => now()]);
        }

        // Return a transparent 1x1 pixel
        return response(base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'), 200)
            ->header('Content-Type', 'image/gif');
    }
}
