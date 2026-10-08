<?php

namespace App\Http\Controllers\Supervisor\Concerns;

use Carbon\Carbon;

trait ResolvesSchoolYearTerm
{
    protected function getCurrentSchoolYear(): string
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        if ($currentMonth >= 6) {
            return $currentYear.'-'.($currentYear + 1);
        }

        return ($currentYear - 1).'-'.$currentYear;
    }

    /**
     * Get current DepEd term (trimester).
     *
     * TERM 1: Jun 8 – Sep 15
     * TERM 2: Sep 16 – Dec 18
     * TERM 3: Jan 4 – Apr 8
     */
    protected function getCurrentTerm(): int
    {
        $now = now();
        $boundaries = [
            1 => [Carbon::create($now->year, 6, 8), Carbon::create($now->year, 9, 15)],
            2 => [Carbon::create($now->year, 9, 16), Carbon::create($now->year, 12, 18)],
            3 => [Carbon::create($now->year, 1, 4), Carbon::create($now->year, 4, 8)],
        ];

        foreach ($boundaries as $term => [$start, $end]) {
            if ($now->between($start, $end)) {
                return $term;
            }
        }

        // Break gaps map to the upcoming term: year-end break (Dec 19-Jan 3)
        // belongs to Term 3, pre-school-year break (Apr 9-Jun 7) to Term 1.
        if ($now->month === 12 && $now->day >= 19) {
            return 3;
        }

        if ($now->month === 1 && $now->day < 4) {
            return 3;
        }

        return 1;
    }
}
