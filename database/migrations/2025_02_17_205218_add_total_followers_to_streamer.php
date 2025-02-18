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
        Schema::table('streamers', function (Blueprint $table) {
            $table->integer('total_followers')->after('is_live')->default(0);
        });
    }
};
