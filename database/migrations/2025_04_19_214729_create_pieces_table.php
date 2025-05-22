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
        Schema::create('pieces', function (Blueprint $table) {
            $table->integer('id_piece')->primary();
            $table->string('nom_piece');
            $table->integer('prix_piece');
            $table->string('marque_piece');
            $table->string('ref_piece');
            $table->string('id_centre');

            $table->foreign('id_centre')
                ->references('id_centre')
                ->on('centres')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pieces', function (Blueprint $table) {
            $table->dropForeign(['id_centre']);
        });
        Schema::dropIfExists('pieces');
    }
};
