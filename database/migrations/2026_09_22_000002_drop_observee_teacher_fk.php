<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * observee_id is polymorphic (teachers.id OR school_head_profiles.id),
     * so a single-table foreign key to teachers.id is wrong: it rejects
     * valid school-head observations and can cascade-delete them when an
     * unrelated teacher is removed. Drop the constraint, keep the index.
     */
    public function up(): void
    {
        if ($this->hasObserveeFk()) {
            Schema::table('observations', function (Blueprint $table) {
                $table->dropForeign(['observee_id']);
            });
        }
    }

    public function down(): void
    {
        if (! $this->hasObserveeFk()) {
            Schema::table('observations', function (Blueprint $table) {
                $table->foreign('observee_id')->references('id')->on('teachers')->onDelete('cascade');
            });
        }
    }

    protected function hasObserveeFk(): bool
    {
        $driver = DB::getDriverName();

        try {
            if ($driver === 'mysql') {
                return DB::selectOne(
                    'SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL',
                    ['observations', 'observee_id']
                ) !== null;
            }

            if ($driver === 'sqlite') {
                foreach (DB::select("PRAGMA foreign_key_list('observations')") as $fk) {
                    if (($fk->from ?? null) === 'observee_id') {
                        return true;
                    }
                }
            }
        } catch (\Throwable) {
            return true; // Fall back to attempting the drop.
        }

        return $driver !== 'sqlite';
    }
};
