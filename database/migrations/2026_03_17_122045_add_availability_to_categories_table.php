<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->json('available_days')->nullable()->after('is_active');
            $table->time('available_from')->nullable()->after('available_days');
            $table->time('available_until')->nullable()->after('available_from');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['available_days', 'available_from', 'available_until']);
        });
    }
};
