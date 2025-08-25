<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('estudiantes')->cascadeOnDelete();
            $table->string('referencia', 30);
            $table->enum('estatus_caja', ['PENDIENTE','VALIDADO','RECHAZADO'])->default('PENDIENTE');
            $table->enum('estatus_admin', ['PENDIENTE','VALIDADO','RECHAZADO'])->default('PENDIENTE');
            $table->string('comprobante_path')->nullable();
            $table->text('observaciones_caja')->nullable();
            $table->text('observaciones_admin')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('pagos');
    }
};
