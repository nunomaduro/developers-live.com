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
        Schema::create('streamers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('twitch_username')->unique();
            $table->string('twitch_id')->nullable()->unique();
            $table->string('status');
            $table->boolean('is_live')->default(false);
            $table->timestamps();
        });
    }
};
