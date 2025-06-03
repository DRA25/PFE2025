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
        Schema::create('dras', function (Blueprint $table) {
            $table->string('n_dra')->primary();
            $table->string('id_centre');
            $table->foreign('id_centre')->references('id_centre')->on('centres')->onDelete('cascade');
            $table->date('date_creation');
            $table->enum('etat', ['actif', 'cloture','refuse','accepte','rembourse'])->default('actif');
            $table->decimal('total_dra');
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('dras', function (Blueprint $table) {
            $table->dropTimestamps();
        });
    }
};
