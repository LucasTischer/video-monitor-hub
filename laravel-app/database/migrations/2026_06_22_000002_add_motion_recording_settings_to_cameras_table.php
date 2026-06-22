<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cameras', function (Blueprint $table) {
            $table->unsignedSmallInteger('record_after_motion_seconds')->default(2)->after('motion_detection_enabled');
            $table->unsignedSmallInteger('pre_motion_buffer_seconds')->default(2)->after('record_after_motion_seconds');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cameras', function (Blueprint $table) {
            $table->dropColumn([
                'record_after_motion_seconds',
                'pre_motion_buffer_seconds',
            ]);
        });
    }
};
