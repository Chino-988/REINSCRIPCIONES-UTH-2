<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('reinscripciones', function (Blueprint $table) {
            if (!Schema::hasColumn('reinscripciones', 'token_verificacion')) {
                $table->string('token_verificacion', 64)->unique()->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('reinscripciones', function (Blueprint $table) {
            if (Schema::hasColumn('reinscripciones', 'token_verificacion')) {
                $table->dropColumn('token_verificacion');
            }
        });
    }
};
