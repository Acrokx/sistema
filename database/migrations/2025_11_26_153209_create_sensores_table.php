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
        Schema::create('sensores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipo_id')->constrained('equipos')->onDelete('cascade');
            $table->string('tipo_sensor', 50);
            $table->decimal('rango_min', 8, 2)->nullable();
            $table->decimal('rango_max', 8, 2)->nullable();
            $table->decimal('limite_alerta_bajo', 8, 2)->nullable();
            $table->decimal('limite_alerta_alto', 8, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sensores');
    }
};
