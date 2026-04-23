<?php

use App\Livewire\Admin\Subscriptions\SubscriptionIndex;
use App\Models\User;
use App\Models\Role;
use App\Models\Subscription;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;

it('can export subscriptions', function () {
    $role = Role::create(['name' => 'Admin', 'slug' => 'admin']);
    $admin = User::factory()->create(['role_id' => $role->id]);

    $this->artisan('permissions:sync');
    $admin->permissions()->sync(\App\Models\Permission::all());

    Subscription::factory()->count(3)->create();

    Excel::fake();

    Livewire::actingAs($admin)
        ->test(SubscriptionIndex::class)
        ->call('export');

    Excel::assertDownloaded('subscriptions.xlsx');
});
