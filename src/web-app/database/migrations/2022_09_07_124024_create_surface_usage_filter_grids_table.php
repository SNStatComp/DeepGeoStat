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
        Schema::create('surface_usage_filter_grids', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surface_usage_filter_id')->references('id')->on('surface_usage_filters')->cascadeOnDelete();
            $table->foreignId('grid_cell_id')->references('id')->on('grid_cells')->cascadeOnDelete();
            $table->foreignId('mask_id')->references('id')->on('masks')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('surface_usage_filter_grids');
    }
};
