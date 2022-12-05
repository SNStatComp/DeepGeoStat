<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('source_dataset_grid_cell', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_dataset_id')->index()->references('id')->on('source_datasets')->cascadeOnDelete();
            $table->foreignId('grid_cell_id')->index()->references('id')->on('grid_cells')->cascadeOnDelete();

            $table->unique(['source_dataset_id', 'grid_cell_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('source_dataset_grid_cell');
    }
};
