<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // --- Permissions ---
        $viewPerm             = Permission::firstOrCreate(['name' => 'view commission notes']);
        $managePerm           = Permission::firstOrCreate(['name' => 'manage commission notes']);
        $manageCompaniesPerm  = Permission::firstOrCreate(['name' => 'manage companies']);
        $manageBranchesPerm   = Permission::firstOrCreate(['name' => 'manage branches']);
        $manageEmployeesPerm  = Permission::firstOrCreate(['name' => 'manage employees']);

        // --- Roles ---
        $viewerRole  = Role::firstOrCreate(['name' => 'viewer']);
        $managerRole = Role::firstOrCreate(['name' => 'manager']);

        $viewerRole->syncPermissions([$viewPerm]);
        $managerRole->syncPermissions([
            $viewPerm,
            $managePerm,
            $manageCompaniesPerm,
            $manageBranchesPerm,
            $manageEmployeesPerm,
        ]);

        // --- Users ---
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Admin User', 'password' => bcrypt('password')]
        );
        $admin->assignRole($managerRole);

        $admin2 = User::firstOrCreate(
            ['email' => 'davie@testmail.com'],
            ['name' => 'Davie Root', 'password' => bcrypt('password')]
        );
        $admin2->assignRole($managerRole);

        $viewer = User::firstOrCreate(
            ['email' => 'viewer@example.com'],
            ['name' => 'Viewer User', 'password' => bcrypt('password')]
        );
        $viewer->assignRole($viewerRole);

        // --- Company: Spar ---
        $spar = Company::firstOrCreate(
            ['name' => 'Spar'],
            ['registration_number' => 'ZA123456', 'created_by' => $admin->id]
        );

        // --- Two branches ---
        $bellville = Branch::firstOrCreate(
            ['company_id' => $spar->id, 'name' => 'Spar Bellville'],
            ['address' => '1 Voortrekker Road, Bellville, Cape Town', 'created_by' => $admin->id]
        );

        $claremont = Branch::firstOrCreate(
            ['company_id' => $spar->id, 'name' => 'Spar Claremont'],
            ['address' => '15 Main Road, Claremont, Cape Town', 'created_by' => $admin->id]
        );

        // --- Employees (one per branch) ---
        $alice = Employee::firstOrCreate(
            ['email' => 'alice@spar.example.com'],
            [
                'company_id' => $spar->id,
                'branch_id'  => $bellville->id,
                'name'       => 'Alice Botha',
                'created_by' => $admin->id,
            ]
        );

        $bob = Employee::firstOrCreate(
            ['email' => 'bob@spar.example.com'],
            [
                'company_id' => $spar->id,
                'branch_id'  => $claremont->id,
                'name'       => 'Bob Dlamini',
                'created_by' => $admin->id,
            ]
        );

        // --- Commission notes (the exercise scenario) ---
        \App\Models\CommissionNote::firstOrCreate(
            ['employee_id' => $alice->id, 'payment_date' => '2026-04-21'],
            [
                'company_id' => $spar->id,
                'branch_id'  => $bellville->id,
                'created_by' => $admin->id,
                'amount'     => 10000.00,
                'notes'      => 'April commission payment — Alice Botha, Spar Bellville.',
            ]
        );

        \App\Models\CommissionNote::firstOrCreate(
            ['employee_id' => $bob->id, 'payment_date' => '2026-04-21'],
            [
                'company_id' => $spar->id,
                'branch_id'  => $claremont->id,
                'created_by' => $admin->id,
                'amount'     => 20000.00,
                'notes'      => 'April commission payment — Bob Dlamini, Spar Claremont.',
            ]
        );

        // --- Ensure all seed data is owned by Admin User ---
        // firstOrCreate won't update rows that already existed before created_by was added.
        Company::whereNull('created_by')->update(['created_by' => $admin->id]);
        Branch::whereNull('created_by')->update(['created_by' => $admin->id]);
        Employee::whereNull('created_by')->update(['created_by' => $admin->id]);
        \App\Models\CommissionNote::whereNull('created_by')->update(['created_by' => $admin->id]);
    }
}

