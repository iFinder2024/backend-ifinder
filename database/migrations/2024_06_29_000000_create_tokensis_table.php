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
        Schema::create('tb_tokensis', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('useradminid');
            $table->string('tradename');
            $table->string('token');
            $table->boolean('bloqueio')->default(0);
            $table->date('previsao_pagamento')->nullable();
            $table->string('tipo_bloqueio')->default('AUTOMÁTICO');
            $table->boolean('block_manual')->default(false);
            $table->integer('companyid')->nullable();
            $table->timestamps();

            $table->foreign('useradminid')->references('useradminid')->on('tb_users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_tokensis');
    }
};
