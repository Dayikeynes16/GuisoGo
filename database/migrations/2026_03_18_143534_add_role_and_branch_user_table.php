<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add role column to users — existing users default to 'admin'.
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('admin')->after('restaurant_id');
        });

        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('admin', 'operator'))");

        // Pivot table: which branches an operator can access.
        Schema::create('branch_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->unique(['user_id', 'branch_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_user');

        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
