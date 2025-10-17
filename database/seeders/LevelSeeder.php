<?php

namespace Database\Seeders;

use App\Models\Level;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class LevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Levels (Roles)
        $admin = Level::create(['level_name' => 'Administrator']);
        $manager = Level::create(['level_name' => 'Manager']);
        $employee = Level::create(['level_name' => 'Employee']);

        // 2. Permissions
        Permission::create(['permission_name' => 'View Reports', 'level_id' => $manager->id]);
        Permission::create(['permission_name' => 'Edit Invoices', 'level_id' => $employee->id]);
        Permission::create(['permission_name' => 'Manage Users', 'level_id' => $admin->id]);
        Permission::create(['permission_name' => 'Full Access', 'level_id' => $admin->id]);
    }
}
