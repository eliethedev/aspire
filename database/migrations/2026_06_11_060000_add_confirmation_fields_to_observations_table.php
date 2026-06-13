<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('observations', function (Blueprint $table) {
            $table->enum('confirmation_status', ['pending', 'confirmed', 'rejected'])
                ->default('pending')
                ->after('status');
            $table->string('rejection_reason')->nullable()->after('confirmation_status');
            $table->text('rejection_notes')->nullable()->after('rejection_reason');
            $table->timestamp('confirmed_at')->nullable()->after('rejection_notes');
            $table->timestamp('rejected_at')->nullable()->after('confirmed_at');
        });
    }

    public function down(): void
    {
        Schema::table('observations', function (Blueprint $table) {
            $table->dropColumn([
                'confirmation_status',
                'rejection_reason',
                'rejection_notes',
                'confirmed_at',
                'rejected_at',
            ]);
        });
    }
};
