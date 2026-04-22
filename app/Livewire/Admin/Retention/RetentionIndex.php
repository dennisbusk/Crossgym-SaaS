<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Retention;

use App\Models\EmailLog;
use App\Models\User;
use App\Traits\WithSorting;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class RetentionIndex extends Component
{
    use WithPagination;
    use WithSorting;

    public function mount(): void
    {
        $this->sortField = 'last_check_in_at';
        $this->sortDirection = 'asc';
    }

    public function sendRecallEmail(User $user)
    {
        // Log the email
        \App\Models\EmailLog::create([
            'tenant_id' => auth()->user()->tenant_id,
            'user_id' => $user->id,
            'subject' => __('Vi savner dig!'),
            'to' => $user->email,
            'sent_at' => now(),
            'type' => 'retention',
        ]);

        event(new \App\Events\RetentionTriggered($user));

        session()->flash('status', __('Email sent to :name', ['name' => $user->name]));
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        // Users who haven't checked in for 14 days
        $inactiveUsersQuery = User::query()
            ->where(function ($q) {
                $q->where('last_check_in_at', '<', now()->subDays(14))
                    ->orWhereNull('last_check_in_at')
                    ->where('created_at', '<', now()->subDays(14));
            })
            ->with(['role']);

        $inactiveUsers = $this->applySorting($inactiveUsersQuery)->paginate(10);

        $emailLogs = EmailLog::query()
            ->where('type', 'retention')
            ->with('user')
            ->latest()
            ->take(10)
            ->get();

        return view('livewire.admin.retention.index', [
            'inactiveUsers' => $inactiveUsers,
            'emailLogs' => $emailLogs,
        ]);
    }
}
