<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            ['name' => 'Childhood Immunizations', 'code' => 'CHIM'],
            ['name' => 'Family Planning Services', 'code' => 'FPLAN'],
            ['name' => 'Basic Laboratory Diagnostics', 'code' => 'LAB'],
            ['name' => 'Infectious Disease Management', 'code' => 'IDM'],
            ['name' => 'Free Senior Maintenance Medicines', 'code' => 'SMM'],
        ];
        $inactiveDefaultServices = [
            'General Medical Consultations',
            'Senior Citizen Vaccinations',
            'Senior Home Health Visits',
            'Prenatal Check-ups and Supplements',
            'Safe Birthing and Postnatal Care',
        ];

        foreach ($services as $service) {
            Service::query()->firstOrCreate(
                ['name' => $service['name']],
                [
                    'code' => $service['code'],
                    'estimated_minutes' => 10,
                    'is_active' => true,
                ],
            );
        }

        Service::query()
            ->whereIn('name', $inactiveDefaultServices)
            ->update(['is_active' => false]);
    }
}
