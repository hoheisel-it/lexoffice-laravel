<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lexoffice_sync_log', function (Blueprint $table) {
            $table->id();
            $table->string('model_type');
            $table->string('model_id');
            $table->string('lexoffice_id')->nullable();
            $table->string('sync_type'); // contact, invoice, product
            $table->string('status'); // success, failed
            $table->json('payload')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();

            $table->index(['model_type', 'model_id']);
            $table->index('lexoffice_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lexoffice_sync_log');
    }
};
