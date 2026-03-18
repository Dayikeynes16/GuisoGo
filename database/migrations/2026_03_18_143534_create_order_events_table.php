<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 30);
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['order_id', 'created_at']);
        });

        DB::statement("ALTER TABLE order_events ADD CONSTRAINT order_events_action_check CHECK (action IN ('created', 'status_changed', 'cancelled'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('order_events');
    }
};
