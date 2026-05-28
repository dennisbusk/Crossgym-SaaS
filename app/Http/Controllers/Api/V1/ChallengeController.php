<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ChallengeResource;
use App\Models\Challenge;
use Illuminate\Http\Request;

class ChallengeController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $challenges = Challenge::where('tenant_id', $user->tenant_id)
            ->where('is_active', true)
            ->with(['users' => function ($query) use ($user) {
                $query->where('users.id', $user->id);
            }])
            ->get();

        return ChallengeResource::collection($challenges);
    }
}
