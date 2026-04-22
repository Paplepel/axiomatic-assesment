<?php

use App\Models\Branch;
use App\Models\CommissionNote;
use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

// --- Helpers ---

function setupPermissions(): void
{
    Permission::firstOrCreate(['name' => 'view commission notes']);
    Permission::firstOrCreate(['name' => 'manage commission notes']);

    $viewerRole  = Role::firstOrCreate(['name' => 'viewer']);
    $managerRole = Role::firstOrCreate(['name' => 'manager']);

    $viewerRole->syncPermissions(['view commission notes']);
    $managerRole->syncPermissions(['view commission notes', 'manage commission notes']);
}

function makeContext(): array
{
    $company  = Company::factory()->create();
    $branch   = Branch::factory()->create(['company_id' => $company->id]);
    $employee = Employee::factory()->create(['company_id' => $company->id, 'branch_id' => $branch->id]);

    return compact('company', 'branch', 'employee');
}

function managerUser(): User
{
    $user = User::factory()->create();
    $user->assignRole('manager');
    return $user;
}

function viewerUser(): User
{
    $user = User::factory()->create();
    $user->assignRole('viewer');
    return $user;
}

// ============================================================
// Access control
// ============================================================

test('guest cannot access commission notes', function () {
    $this->get(route('commission-notes.index'))->assertRedirect(route('login'));
});

test('viewer can access commission notes index', function () {
    setupPermissions();
    ['company' => $company, 'branch' => $branch] = makeContext();

    $this->actingAs(viewerUser())
        ->get(route('commission-notes.index', ['company_id' => $company->id, 'branch_id' => $branch->id]))
        ->assertOk();
});

test('viewer cannot create a commission note', function () {
    setupPermissions();
    ['company' => $company, 'branch' => $branch, 'employee' => $employee] = makeContext();

    $this->actingAs(viewerUser())
        ->post(route('commission-notes.store'), [
            'company_id'   => $company->id,
            'branch_id'    => $branch->id,
            'employee_id'  => $employee->id,
            'amount'       => 5000,
            'payment_date' => '2026-04-21',
        ])
        ->assertForbidden();
});

test('viewer cannot update a commission note', function () {
    setupPermissions();
    ['company' => $company, 'branch' => $branch, 'employee' => $employee] = makeContext();

    $manager = managerUser();
    $viewer  = viewerUser();

    $note = CommissionNote::factory()->create([
        'company_id'  => $company->id,
        'branch_id'   => $branch->id,
        'employee_id' => $employee->id,
        'created_by'  => $manager->id,
    ]);

    $this->actingAs($viewer)
        ->put(route('commission-notes.update', $note), [
            'employee_id'  => $employee->id,
            'amount'       => 9999,
            'payment_date' => '2026-04-21',
        ])
        ->assertForbidden();
});

// ============================================================
// Happy path: create
// ============================================================

test('manager can create a commission note', function () {
    setupPermissions();
    ['company' => $company, 'branch' => $branch, 'employee' => $employee] = makeContext();

    $manager = managerUser();

    $this->actingAs($manager)
        ->post(route('commission-notes.store'), [
            'company_id'   => $company->id,
            'branch_id'    => $branch->id,
            'employee_id'  => $employee->id,
            'amount'       => 10000,
            'payment_date' => '2026-04-21',
            'notes'        => 'Test commission',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('commission_notes', [
        'company_id'  => $company->id,
        'branch_id'   => $branch->id,
        'employee_id' => $employee->id,
        'created_by'  => $manager->id,
        'amount'      => 10000,
    ]);
});

// ============================================================
// Business rule: author-only edit
// ============================================================

test('author can edit their own note', function () {
    setupPermissions();
    ['company' => $company, 'branch' => $branch, 'employee' => $employee] = makeContext();

    $manager = managerUser();

    $note = CommissionNote::factory()->create([
        'company_id'  => $company->id,
        'branch_id'   => $branch->id,
        'employee_id' => $employee->id,
        'created_by'  => $manager->id,
        'amount'      => 5000,
        'payment_date' => '2026-04-01',
    ]);

    $this->actingAs($manager)
        ->put(route('commission-notes.update', $note), [
            'employee_id'  => $employee->id,
            'amount'       => 7500,
            'payment_date' => '2026-04-21',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('commission_notes', ['id' => $note->id, 'amount' => 7500]);
});

test('non-author viewer cannot edit another users note', function () {
    setupPermissions();
    ['company' => $company, 'branch' => $branch, 'employee' => $employee] = makeContext();

    $manager = managerUser();
    $viewer  = viewerUser();

    $note = CommissionNote::factory()->create([
        'company_id'  => $company->id,
        'branch_id'   => $branch->id,
        'employee_id' => $employee->id,
        'created_by'  => $manager->id,
    ]);

    $this->actingAs($viewer)
        ->put(route('commission-notes.update', $note), [
            'employee_id'  => $employee->id,
            'amount'       => 1,
            'payment_date' => '2026-04-21',
        ])
        ->assertForbidden();
});

test('manager cannot edit another users note', function () {
    setupPermissions();
    ['company' => $company, 'branch' => $branch, 'employee' => $employee] = makeContext();

    $author  = managerUser();
    $manager = managerUser();

    $note = CommissionNote::factory()->create([
        'company_id'  => $company->id,
        'branch_id'   => $branch->id,
        'employee_id' => $employee->id,
        'created_by'  => $author->id,
        'amount'      => 5000,
        'payment_date' => '2026-04-01',
    ]);

    $this->actingAs($manager)
        ->put(route('commission-notes.update', $note), [
            'employee_id'  => $employee->id,
            'amount'       => 8000,
            'payment_date' => '2026-04-21',
        ])
        ->assertForbidden();

    $this->assertDatabaseHas('commission_notes', ['id' => $note->id, 'amount' => 5000]);
});
