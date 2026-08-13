<?php

namespace Database\Seeders;

use App\Models\PpstStandard;
use Illuminate\Database\Seeder;

class PpstStandardSeeder extends Seeder
{
    public function run(): void
    {
        $domains = config('ppst.domains', []);

        $sortOrder = 0;

        foreach ($domains as $domain) {
            $name = "Domain {$domain['number']}: {$domain['name']}";

            foreach ($domain['indicators'] as $indicator) {
                $strand = implode('.', array_slice(explode('.', $indicator['code']), 0, 2));

                PpstStandard::firstOrCreate(
                    ['indicator_code' => $indicator['code']],
                    [
                        'domain' => $name,
                        'strand' => $strand,
                        'description' => $indicator['description'],
                        'sort_order' => $sortOrder,
                        'is_active' => true,
                    ]
                );

                $sortOrder++;
            }
        }

        $this->command->info('PPST standards seeded successfully.');
    }
}
