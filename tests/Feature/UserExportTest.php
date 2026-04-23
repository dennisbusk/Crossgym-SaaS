<?php

use App\Livewire\Admin\Users\UserIndex;
use App\Models\User;
use App\Models\Role;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;

it('can export users', function () {
    $role = Role::create(['name' => 'Admin', 'slug' => 'admin']);
    $admin = User::factory()->create(['role_id' => $role->id]);

    $this->artisan('permissions:sync');
    $admin->permissions()->sync(\App\Models\Permission::all());

    Excel::fake();

    Livewire::actingAs($admin)
        ->test(UserIndex::class)
        ->call('export');

    Excel::assertDownloaded('users.xlsx');
});
