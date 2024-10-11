<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tb_users', function (Blueprint $table) {
            $table->bigIncrements('userid')->unique();
            $table->unsignedBigInteger('useradminid')->unique()->nullable();
            $table->integer('profileid')->default(2);
            $table->string('name');
            $table->string('email')->unique();
            $table->string('cpf', 11)->unique();
            $table->date('nascimento');
            $table->string('sexo');
            $table->string('whatsapp')->nullable();
            $table->string('cep', 8)->nullable();
            $table->string('rua')->nullable();
            $table->string('cidade')->nullable();
            $table->string('uf', 2)->nullable();
            $table->string('pais')->nullable();
            $table->string('complemento')->nullable();
            $table->string('numero_casa')->nullable();
            $table->string('password');
            $table->string('tokenesquecisenha')->nullable();
            $table->string('ultimoip')->nullable();
            $table->string('provider')->nullable();
            $table->string('provider_id')->nullable();
            $table->boolean('primeiroacesso')->default(true);
            $table->timestamps();
        });

        // Cria a sequência para useradminid
        DB::statement('CREATE SEQUENCE tb_users_useradminid_seq');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP SEQUENCE IF EXISTS tb_users_useradminid_seq');
        Schema::dropIfExists('tb_users');
    }
};
