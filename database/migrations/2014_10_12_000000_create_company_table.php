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
        Schema::create('tb_company', function (Blueprint $table) {
            $table->bigIncrements('companyid');
            $table->string('cnpj', 14)->unique();
            $table->string('type'); // 'matriz' ou 'filial'
            $table->string('matrizname')->nullable();
            $table->string('companyname');
            $table->string('tradename');
            $table->string('logotipo')->nullable();
            $table->string('cep', 8)->nullable();
            $table->string('rua')->nullable();
            $table->string('cidade')->nullable();
            $table->string('uf', 2)->nullable();
            $table->string('pais')->nullable();
            $table->string('complemento')->nullable();
            $table->string('numero')->nullable();
            $table->string('celular')->nullable();
            $table->string('whatsapp')->nullable();
            $table->double('latitude', 10, 6)->nullable();
            $table->double('longitude', 10, 6)->nullable();
            $table->unsignedBigInteger('useradminid')->nullable();
            $table->unsignedBigInteger('parentid')->nullable();
            $table->unsignedBigInteger('matrizid')->nullable();
            $table->boolean('is_matriz')->default(false);
            $table->timestamps();

            $table->foreign('useradminid')->references('useradminid')->on('tb_users');
            $table->foreign('parentid')->references('companyid')->on('tb_company');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_company');
    }
};
