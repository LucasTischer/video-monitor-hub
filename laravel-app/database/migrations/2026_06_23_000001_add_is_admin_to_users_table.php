<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false)->after('password');
        });

        if (! DB::table('users')->where('is_admin', true)->exists()) {
            $oldestUser = DB::table('users')
                ->orderBy('created_at')
                ->orderBy('id')
                ->first();

            if ($oldestUser) {
                DB::table('users')
                    ->where('id', $oldestUser->id)
                    ->update(['is_admin' => true]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_admin');
        });
    }
};
