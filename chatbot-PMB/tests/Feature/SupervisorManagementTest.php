<?php

use App\Enums\UserRole;
use App\Models\User;
use Livewire\Livewire;

test('admin can view supervisor management page', function () {
    $admin = User::factory()->create(['role' => UserRole::ADMIN]);

    $this->actingAs($admin)
        ->get(route('admin.supervisors'))
        ->assertOk();
});

test('supervisor cannot access admin only pages and gets redirected to dashboard', function () {
    $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR]);

    $this->actingAs($supervisor)
        ->get(route('admin.supervisors'))
        ->assertRedirect(route('dashboard'));

    $this->actingAs($supervisor)
        ->get(route('admin.knowledge-base'))
        ->assertRedirect(route('dashboard'));

    $this->actingAs($supervisor)
        ->get(route('admin.chatbot-settings'))
        ->assertRedirect(route('dashboard'));

    $this->actingAs($supervisor)
        ->get(route('admin.dashboard'))
        ->assertRedirect(route('dashboard'));
});

test('admin is redirected from /dashboard to /admin/dashboard', function () {
    $admin = User::factory()->create(['role' => UserRole::ADMIN]);

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertRedirect(route('admin.dashboard'));

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk();
});

test('supervisor can access dashboard and chat history without admin prefix', function () {
    $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR]);

    $this->actingAs($supervisor)
        ->get(route('dashboard'))
        ->assertOk();

    $this->actingAs($supervisor)
        ->get(route('supervisor.chat-history'))
        ->assertOk();
});

test('admin can create a new supervisor', function () {
    $admin = User::factory()->create(['role' => UserRole::ADMIN]);

    Livewire::actingAs($admin)
        ->test('pages::admin.supervisors')
        ->set('name', 'Budi Supervisor')
        ->set('email', 'budi@test.com')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->call('createSupervisor')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('users', [
        'email' => 'budi@test.com',
        'role' => UserRole::SUPERVISOR->value,
    ]);
});

test('admin can update supervisor password', function () {
    $admin = User::factory()->create(['role' => UserRole::ADMIN]);
    $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR]);

    Livewire::actingAs($admin)
        ->test('pages::admin.supervisors')
        ->set('editingSupervisorId', $supervisor->id)
        ->set('new_password', 'newsecret123')
        ->set('new_password_confirmation', 'newsecret123')
        ->call('updateSupervisorPassword')
        ->assertHasNoErrors();
});

test('admin can update user details name and email', function () {
    $admin = User::factory()->create(['role' => UserRole::ADMIN]);
    $supervisor = User::factory()->create(['name' => 'Nama Lama', 'email' => 'lama@test.com', 'role' => UserRole::SUPERVISOR]);

    Livewire::actingAs($admin)
        ->test('pages::admin.supervisors')
        ->set('editingSupervisorId', $supervisor->id)
        ->set('edit_name', 'Nama Baru')
        ->set('edit_email', 'baru@test.com')
        ->call('updateUser')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('users', [
        'id' => $supervisor->id,
        'name' => 'Nama Baru',
        'email' => 'baru@test.com',
    ]);
});

test('admin can delete a supervisor', function () {
    $admin = User::factory()->create(['role' => UserRole::ADMIN]);
    $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR]);

    Livewire::actingAs($admin)
        ->test('pages::admin.supervisors')
        ->call('deleteSupervisor', $supervisor->id);

    $this->assertDatabaseMissing('users', [
        'id' => $supervisor->id,
    ]);
});
