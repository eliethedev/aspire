<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Canonical list of common DepEd subjects. Seeded so teachers can pick
     * their assigned subjects from a list. Additional free-text subjects
     * entered by teachers or supervisors are created on the fly.
     */
    protected array $seedSubjects = [
        'Filipino',
        'English',
        'Mathematics',
        'Science',
        'ICT',
        'TLE',
        'Araling Panlipunan',
        'Social Studies',
        'MAPEH',
        'Music',
        'Arts',
        'Physical Education',
        'Health',
        'ESP',
        'Values Education',
        'Reading and Literacy',
        'Mother Tongue',
        'General Mathematics',
        'Statistics and Probability',
        'Earth Science',
        'Earth and Life Science',
        'Physical Science',
        'Biology',
        'Chemistry',
        'Physics',
        'Personal Development',
        'Empowerment Technologies',
        'Media and Information Literacy',
        'Research',
    ];

    /**
     * Normalise subject names so identical subjects written differently
     * (e.g. " mathematics, " vs "Mathematics") collapse into one row.
     */
    protected function normalize(string $name): string
    {
        $name = trim($name);
        $name = preg_replace('/\s+/', ' ', $name) ?? $name;

        return mb_strtolower($name);
    }

    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('teacher_subjects', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['teacher_id', 'subject_id']);
            $table->index('subject_id');
        });

        $this->seedAndBackfill();
    }

    protected function seedAndBackfill(): void
    {
        // Normalised name => subject id.
        $byKey = [];

        $ensure = function (string $name) use (&$byKey) {
            $key = $this->normalize($name);
            if (isset($byKey[$key])) {
                return $byKey[$key];
            }

            $subjectId = DB::table('subjects')->insertGetId([
                'name' => trim($name),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $byKey[$key] = $subjectId;

            return $subjectId;
        };

        // 1. Seed the canonical list (skipping any subject that already exists).
        foreach ($this->seedSubjects as $name) {
            $key = $this->normalize($name);
            if (! DB::table('subjects')->whereRaw('LOWER(TRIM(name)) = ?', [$key])->exists()) {
                $ensure($name);
            }
        }

        // 2. Backfill subject rows from existing teacher / observation data so
        //    historical records keep resolving to a subject in the list.
        $legacyNames = DB::table('teachers')
            ->whereNotNull('subject')
            ->where('subject', '!=', '')
            ->pluck('subject')
            ->merge(
                DB::table('observations')
                    ->whereNotNull('subject')
                    ->where('subject', '!=', '')
                    ->pluck('subject')
            )
            ->unique();

        foreach ($legacyNames as $name) {
            $this->normalize((string) $name);
            $ensure((string) $name);
        }

        // 3. Populate the pivot for every teacher that already has a subject.
        $now = now();
        foreach (DB::table('teachers')->whereNotNull('subject')->where('subject', '!=', '')->get() as $teacher) {
            if (! isset($byKey[$this->normalize((string) $teacher->subject)])) {
                continue;
            }

            DB::table('teacher_subjects')->insertOrIgnore([
                'teacher_id' => $teacher->id,
                'subject_id' => $byKey[$this->normalize((string) $teacher->subject)],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_subjects');
        Schema::dropIfExists('subjects');
    }
};