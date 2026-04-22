<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\CrossfitCoachService;
use Illuminate\Http\Request;

class WodController extends Controller
{
    public function __construct(
        protected CrossfitCoachService $coachService
    ) {}

    /**
     * Stream WOD generation. Returns SSE stream.
     */
    public function stream(Request $request)
    {
        $request->validate([
            'intensity' => 'required|string',
            'equipment' => 'required|array',
            'equipment.*' => 'string',
            'focus_area' => 'required|string',
            'difficulty' => 'required|string',
            'duration' => 'required|integer|min:5|max:90',
        ]);

        $parameters = $request->only(['intensity', 'equipment', 'focus_area', 'difficulty', 'duration']);

        return $this->coachService->streamWod($parameters);
    }

    /**
     * Stream WOD refinement.
     */
    public function streamRefine(Request $request)
    {
        $request->validate([
            'current_wod' => 'required|string',
            'feedback' => 'required|string|max:2000',
            'intensity' => 'required|string',
            'equipment' => 'required|array',
            'equipment.*' => 'string',
            'focus_area' => 'required|string',
            'difficulty' => 'required|string',
            'duration' => 'required|integer|min:5|max:90',
        ]);

        $parameters = $request->only(['intensity', 'equipment', 'focus_area', 'difficulty', 'duration']);

        return $this->coachService->streamRefineWod(
            $request->input('current_wod'),
            $request->input('feedback'),
            $parameters
        );
    }
}
