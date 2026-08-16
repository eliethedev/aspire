<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('observations', function (Blueprint $table) {
            $table->string('cot_document_path')->nullable()->after('evidence_files');
            $table->timestamp('cot_document_generated_at')->nullable()->after('cot_document_path');
        });
    }

    public function down(): void
    {
        Schema::table('observations', function (Blueprint $table) {
            $table->dropColumn(['cot_document_path', 'cot_document_generated_at']);
        });
    }
};
