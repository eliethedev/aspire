<?php

namespace Database\Seeders;

use App\Models\EpocTemplate;
use Illuminate\Database\Seeder;

class EpocTemplateSeeder extends Seeder
{
    /**
     * Seed the built-in DepEd CID EPOC template so evaluations keep working
     * even before an admin creates a custom template.
     */
    public function run(): void
    {
        if (EpocTemplate::query()->exists()) {
            return;
        }

        $currentYear = (int) date('Y');
        $schoolYear = "{$currentYear}-" . ($currentYear + 1);

        $template = EpocTemplate::create([
            'name' => "EPOC Default {$schoolYear}",
            'description' => 'Built-in DepEd CID post-observation conference evaluation domains.',
            'school_year' => $schoolYear,
            'is_active' => true,
        ]);

        $order = 0;
        foreach (EpocTemplate::defaultDomains() as $domain => $indicators) {
            foreach ($indicators as $indicator) {
                $template->indicators()->create([
                    'domain' => $domain,
                    'indicator' => $indicator,
                    'order' => $order++,
                ]);
            }
        }
    }
}
