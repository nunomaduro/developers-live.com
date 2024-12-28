<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('broadcaster_id');
            $table->string('type');
            $table->uuid('subscription_id')->unique();
            $table->string('status');
            $table->timestamps();

            $table->index(['type', 'broadcaster_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_subscriptions');
    }
};
