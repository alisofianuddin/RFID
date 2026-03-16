<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('rfid_cards', function (Blueprint $table) {
            $table->string('no_card')->nullable()->after('uid');
            $table->string('bn')->nullable()->after('no_card');
            $table->dropColumn('nama');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rfid_cards', function (Blueprint $table) {
            $table->string('nama')->nullable()->after('uid');
            $table->dropColumn(['no_card', 'bn']);
        });
    }
};
