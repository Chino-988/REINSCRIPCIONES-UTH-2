<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->index('referencia');
            $table->index('estudiante_id');
            $table->index('estatus_caja');
            $table->index('estatus_admin');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropIndex(['referencia']);
            $table->dropIndex(['estudiante_id']);
            $table->dropIndex(['estatus_caja']);
            $table->dropIndex(['estatus_admin']);
            $table->dropIndex(['created_at']);
        });
    }
};
