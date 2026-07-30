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
    Schema::create('subscriptions', function (Blueprint $table) {
        $table->id();

        $table->foreignId('user_id')
            ->constrained()
            ->cascadeOnDelete();

        $table->foreignId('membership_plan_id')
            ->constrained()
            ->cascadeOnDelete();

        $table->enum('status', [
            'active',
            'expired',
            'cancelled',
            'pending'
        ])->default('pending');

        $table->date('starts_at');

        $table->date('ends_at');

        $table->date('next_billing_date');

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
