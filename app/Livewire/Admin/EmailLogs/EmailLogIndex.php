<?php

declare(strict_types=1);

namespace App\Livewire\Admin\EmailLogs;

use App\Models\EmailLog;
use App\Traits\WithSorting;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class EmailLogIndex extends Component
{
    use WithPagination;
    use WithSorting;

    public string $search = '';

    public function mount(): void
    {
        $this->sortField = 'sent_at';
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        $logs = EmailLog::query()
            ->with('user')
            ->when($this->search, function ($q) {
                $q->where('to', 'like', "%{$this->search}%")
                    ->orWhere('subject', 'like', "%{$this->search}%")
                    ->orWhereHas('user', function ($uq) {
                        $uq->where('name', 'like', "%{$this->search}%");
                    });
            });

        $logs = $this->applySorting($logs)->paginate(20);

        return view('livewire.admin.email-logs.index', [
            'logs' => $logs,
        ]);
    }
}
