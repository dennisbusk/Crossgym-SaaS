<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Classes;

use App\Models\GymClass;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class ClassShow extends Component
{
    use AuthorizesRequests;

    public GymClass $gymClass;

    public string $trialName = '';

    public string $userSearch = '';

    public ?int $selectedUserId = null;

    public function mount(GymClass $gymClass): void
    {
        $this->refreshClass($gymClass);
    }

    public function refreshClass(GymClass $gymClass): void
    {
        $this->gymClass = $gymClass->load(['trainer', 'classType', 'participants', 'trials']);
        $this->authorize('view', $this->gymClass);
    }

    public function checkInParticipant(int $userId): void
    {
        $this->authorize('update', $this->gymClass);

        $participant = $this->gymClass->participants()->where('users.id', $userId)->first();
        if (! $participant) {
            return;
        }

        $now = now();
        $checkInId = $participant->pivot->check_in_id;

        if ($checkInId) {
            $checkIn = \App\Models\CheckIn::find($checkInId);
            if ($checkIn) {
                $checkIn->update(['checked_at' => $now]);
            }
        } else {
            $checkIn = \App\Models\CheckIn::create([
                'tenant_id' => $this->gymClass->tenant_id,
                'user_id' => $userId,
                'gym_class_id' => $this->gymClass->id,
                'checked_at' => $now,
            ]);
            $this->gymClass->participants()->updateExistingPivot($userId, ['check_in_id' => $checkIn->id]);
        }

        $this->refreshClass($this->gymClass);
        session()->flash('success', __('Participant checked in'));
    }

    public function removeParticipant(int $userId): void
    {
        $this->authorize('update', $this->gymClass);

        $this->gymClass->participants()->detach($userId);

        $this->refreshClass($this->gymClass);
        session()->flash('success', __('Participant removed'));
    }

    public function addParticipant(): void
    {
        $this->authorize('update', $this->gymClass);

        if (! $this->selectedUserId) {
            return;
        }

        if ($this->gymClass->participants->contains($this->selectedUserId)) {
            session()->flash('error', __('User is already booked'));

            return;
        }

        $this->gymClass->participants()->attach($this->selectedUserId);
        $this->selectedUserId = null;
        $this->userSearch = '';

        $this->refreshClass($this->gymClass);
        session()->flash('success', __('User added to class'));
    }

    public function addTrial(): void
    {
        $this->authorize('update', $this->gymClass);

        $this->validate([
            'trialName' => 'required|string|max:255',
        ]);

        $this->gymClass->trials()->create([
            'tenant_id' => $this->gymClass->tenant_id,
            'name' => $this->trialName,
        ]);

        $this->trialName = '';
        $this->refreshClass($this->gymClass);
        session()->flash('success', __('Trial booking added'));
    }

    public function checkInTrial(int $trialId): void
    {
        $this->authorize('update', $this->gymClass);

        $trial = $this->gymClass->trials()->findOrFail($trialId);

        $now = now();
        $checkInId = $trial->check_in_id;

        if ($checkInId) {
            $checkIn = \App\Models\CheckIn::find($checkInId);
            if ($checkIn) {
                $checkIn->update(['checked_at' => $now]);
            }
        } else {
            $checkIn = \App\Models\CheckIn::create([
                'tenant_id' => $this->gymClass->tenant_id,
                'user_id' => null,
                'gym_class_id' => $this->gymClass->id,
                'checked_at' => $now,
            ]);
            $trial->update(['check_in_id' => $checkIn->id]);
        }

        $this->refreshClass($this->gymClass);
        session()->flash('success', __('Trial participant checked in'));
    }

    public function removeTrial(int $trialId): void
    {
        $this->authorize('update', $this->gymClass);

        $trial = $this->gymClass->trials()->findOrFail($trialId);
        $trial->delete();

        $this->refreshClass($this->gymClass);
        session()->flash('success', __('Trial booking removed'));
    }

    public function getUsersProperty()
    {
        if (strlen($this->userSearch) < 2) {
            return collect();
        }

        return \App\Models\User::where('tenant_id', $this->gymClass->tenant_id)
            ->where(function ($query) {
                $query->where('name', 'like', '%'.$this->userSearch.'%')
                    ->orWhere('email', 'like', '%'.$this->userSearch.'%');
            })
            ->limit(10)
            ->get();
    }

    public function selectUser(int $userId): void
    {
        $user = \App\Models\User::find($userId);
        if ($user) {
            $this->selectedUserId = $user->id;
            $this->userSearch = $user->name;
        }
    }

    public function render()
    {
        return view('livewire.admin.classes.show', [
            'users' => $this->users,
        ]);
    }
}
