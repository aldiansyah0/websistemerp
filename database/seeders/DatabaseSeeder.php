<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
        ]);

        $tenantId = (int) Tenant::query()->firstOrCreate(
            ['code' => 'default'],
            ['name' => 'Default Tenant', 'is_active' => true]
        )->id;

        $adminName = (string) env('ERP_ADMIN_NAME', 'Owner ERP');
        $adminEmail = (string) env('ERP_ADMIN_EMAIL', 'admin@webstellar.local');
        $adminPassword = (string) env('ERP_ADMIN_PASSWORD', 'Admin12345!');

        $admin = User::query()->updateOrCreate(
            ['email' => $adminEmail],
            [
                'name' => $adminName,
                'password' => $adminPassword,
                'tenant_id' => $tenantId,
                'location_id' => null,
                'active_location_id' => null,
                'access_scope' => User::ACCESS_SCOPE_ALL,
                'email_verified_at' => now(),
            ]
        );

        $ownerRoleId = Role::query()
            ->where('slug', Role::OWNER)
            ->value('id');

        if ($ownerRoleId !== null) {
            $admin->roles()->syncWithoutDetaching([(int) $ownerRoleId]);
        }

        $admin->allowedLocations()->sync([]);
    }
}
