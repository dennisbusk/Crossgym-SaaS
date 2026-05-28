<?php

declare(strict_types=1);

namespace App\Services;

use App\Ai\Agents\WodCoach;

class CrossfitCoachService
{
    public function generateWod(array $parameters): string
    {
        $prompt = $this->buildPrompt($parameters);

        $response = (new WodCoach)->prompt($prompt);

        return trim((string) $response);
    }

    /**
     * Refine an existing WOD based on trainer feedback.
     */
    public function refineWod(string $currentWodHtml, string $feedback, array $parameters): string
    {
        $paramsContext = $this->buildPrompt($parameters);
        $prompt = <<<PROMPT
{$paramsContext}

---

Current WOD (HTML):
{$currentWodHtml}

---

Trainer feedback: {$feedback}

Please regenerate the WOD incorporating this feedback. Return well-formed HTML only.
PROMPT;

        $response = (new WodCoach)->prompt($prompt);

        return trim((string) $response);
    }

    /**
     * Stream WOD generation. Returns a stream that can be returned from a route.
     */
    public function streamWod(array $parameters)
    {
        $prompt = $this->buildPrompt($parameters);

        return (new WodCoach)->stream($prompt);
    }

    /**
     * Stream WOD refinement.
     */
    public function streamRefineWod(string $currentWodHtml, string $feedback, array $parameters)
    {
        $paramsContext = $this->buildPrompt($parameters);
        $prompt = <<<PROMPT
{$paramsContext}

---

Current WOD (HTML):
{$currentWodHtml}

---

Trainer feedback: {$feedback}

Please regenerate the WOD incorporating this feedback. Return well-formed HTML only.
PROMPT;

        return (new WodCoach)->stream($prompt);
    }

    protected function buildPrompt(array $parameters): string
    {
        $equipment = is_array($parameters['equipment'])
            ? implode(', ', $parameters['equipment'])
            : $parameters['equipment'];

        $recoveryContext = '';
        if (auth()->check()) {
            /** @var \App\Models\User $user */
            $user = auth()->user();
            $recoveryScore = $user->recovery_score ?? 100;
            $recoveryContext = "User Recovery Score: {$recoveryScore}% (100% is fully recovered, < 50% means high fatigue).\n";

            // Get recent workouts context
            $recentWorkouts = \App\Models\WorkoutLog::with('exercise')
                ->where('user_id', $user->id)
                ->latest()
                ->limit(3)
                ->get()
                ->map(fn($log) => ($log->exercise?->getTranslation('name', 'da') ?? 'Exercise') . " (Intensity: {$log->intensity}/10)")
                ->implode(', ');

            if ($recentWorkouts) {
                $recoveryContext .= "Recent workouts: {$recentWorkouts}.\n";
            }
        }

        return <<<PROMPT
{$recoveryContext}
Create one CrossFit WOD with the following parameters:

Intensity: {$parameters['intensity']}
Equipment: {$equipment}
Focus Area: {$parameters['focus_area']}
Difficulty: {$parameters['difficulty']}
Duration: {$parameters['duration']} minutes

Make the workout practical, well-balanced, and appropriate for the requested duration.
If the Recovery Score is low, suggest a more mobility-focused or lower-intensity session even if high intensity was requested.
Output well-formed HTML only.
PROMPT;
    }
}
