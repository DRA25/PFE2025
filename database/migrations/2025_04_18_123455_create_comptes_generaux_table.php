<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateComptesGenerauxTable extends Migration
{
public function up(): void
{
Schema::create('comptes_generaux', function (Blueprint $table) {
$table->string('code')->primary(); // Primary key
$table->string('libelle');
});
}

public function down(): void
{
Schema::dropIfExists('comptes_generaux');
}
}
