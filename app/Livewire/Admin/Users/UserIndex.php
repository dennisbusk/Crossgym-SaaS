<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Users;

use App\Exports\UsersExport;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class UserIndex extends Component
{
    use AuthorizesRequests;

    public function mount(): void
    {
        $this->authorize('viewAny', User::class);
    }

    public function delete(User $user): void
    {
        $this->authorize('delete', $user);
        $user->delete();
        session()->flash('status', __('User deleted.'));
    }

    public function export()
    {
        $this->authorize('viewAny', User::class);

        return Excel::download(new UsersExport(), 'users.xlsx');
    }

    public function render()
    {
        return view('livewire.admin.users.index', [
            'users' => User::query()->with(['role', 'tenant'])->latest()->get(),
        ]);
    }
}
