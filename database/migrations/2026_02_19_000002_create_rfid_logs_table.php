<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rfid_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rfid_card_id')->nullable()->constrained('rfid_cards')->nullOnDelete();
            $table->string('uid')->comment('UID kartu yang di-scan');
            $table->enum('status', ['registered', 'unregistered'])->default('unregistered');
            $table->timestamp('scanned_at')->useCurrent();
            $table->timestamps();

            $table->index('uid');
            $table->index('scanned_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rfid_logs');
    }
};
