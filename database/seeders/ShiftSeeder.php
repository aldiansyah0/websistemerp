<?php

namespace Database\Seeders;

use App\Models\Shift;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class ShiftSeeder extends Seeder
{
    public function run(): void
    {
        $tenantId = Tenant::query()->where('code', 'default')->value('id');

        $templates = [
            ['code' => 'SHIFT-MORNING', 'name' => 'Morning Shift', 'start_time' => '08:00:00', 'end_time' => '17:00:00', 'is_overnight' => false, 'grace_minutes' => 10],
            ['code' => 'SHIFT-AFTERNOON', 'name' => 'Afternoon Shift', 'start_time' => '12:00:00', 'end_time' => '21:00:00', 'is_overnight' => false, 'grace_minutes' => 10],
            ['code' => 'SHIFT-OFFICE', 'name' => 'Office Shift', 'start_time' => '08:30:00', 'end_time' => '17:30:00', 'is_overnight' => false, 'grace_minutes' => 5],
            ['code' => 'SHIFT-WAREHOUSE', 'name' => 'Warehouse Shift', 'start_time' => '07:30:00', 'end_time' => '16:30:00', 'is_overnight' => false, 'grace_minutes' => 5],
        ];

        foreach ($templates as $template) {
            Shift::query()->updateOrCreate(
                [
                    'tenant_id' => $tenantId,
                    'code' => $template['code'],
                ],
                [
                    'location_id' => null,
                    'name' => $template['name'],
                    'start_time' => $template['start_time'],
                    'end_time' => $template['end_time'],
                    'is_overnight' => $template['is_overnight'],
                    'grace_minutes' => $template['grace_minutes'],
                    'max_overtime_minutes' => 240,
                    'is_active' => true,
                    'notes' => 'Template shift retail.',
                ]
            );
        }
    }
}
