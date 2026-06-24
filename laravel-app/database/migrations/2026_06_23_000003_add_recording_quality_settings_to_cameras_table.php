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
            $table->unsignedSmallInteger('recording_resolution_height')->nullable()->after('pre_motion_buffer_seconds');
            $table->unsignedSmallInteger('recording_fps')->default(20)->after('recording_resolution_height');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cameras', function (Blueprint $table) {
            $table->dropColumn([
                'recording_resolution_height',
                'recording_fps',
            ]);
        });
    }
};
