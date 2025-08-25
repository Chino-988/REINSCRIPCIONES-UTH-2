<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('referencia_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pago_id')->constrained('pagos')->cascadeOnDelete();
            $table->string('referencia', 80)->index();
            $table->string('status_before', 30)->nullable();
            $table->string('status_after', 30);
            $table->string('source', 30)->default('API'); // API | EXTENSION | CSV
            $table->string('actor')->nullable();         // opcional: quién ejecuta
            $table->string('ip')->nullable();
            $table->jsonb('meta')->nullable();           // {observacion, monto, fecha_pago, ...}
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referencia_updates');
    }
};
