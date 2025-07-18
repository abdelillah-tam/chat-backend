<?php

use App\Models\ChatChannel;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //
        Schema::table('messages', function (Blueprint $table) {

            $table->foreignUuid('channel')->references('id')->on('chat_channels');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['channel']);
        });
    }
};
