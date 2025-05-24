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
        Schema::create('encaissements', function (Blueprint $table) {
            $table->id();
            $table->string('id_centre');
            $table->unsignedBigInteger('n_remb');
            $table->integer('montant_enc');
            $table->date('date_enc');
            $table->timestamps();

            $table->foreign('id_centre')->references('id_centre')->on('centres')->onDelete('cascade');
            $table->foreign('n_remb')->references('n_remb')->on('remboursements')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('encaissements');
    }

};
