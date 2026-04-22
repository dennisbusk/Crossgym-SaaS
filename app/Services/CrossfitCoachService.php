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

        return <<<PROMPT
Create one CrossFit WOD with the following parameters:

Intensity: {$parameters['intensity']}
Equipment: {$equipment}
Focus Area: {$parameters['focus_area']}
Difficulty: {$parameters['difficulty']}
Duration: {$parameters['duration']} minutes

Make the workout practical, well-balanced, and appropriate for the requested duration. Output well-formed HTML only.
PROMPT;
    }
}
