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
            $table->primary(['id_centre', 'n_remb']); // Composite primary key
            $table->string('id_centre');
            $table->unsignedBigInteger('n_remb');
            $table->decimal('montant_enc', 20, 6);
            $table->date('date_enc');
            $table->timestamps();

            $table->foreign('id_centre')
                ->references('id_centre')
                ->on('centres')
                ->onDelete('cascade');

            $table->foreign('n_remb')
                ->references('n_remb')
                ->on('remboursements')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('encaissements');
    }

};
