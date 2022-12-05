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
        Schema::create('prediction_labels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prediction_id')->references('id')->on('predictions')->cascadeOnDelete();
            $table->foreignId('source_dataset_grid_cell_id')->references('id')->on('source_dataset_grid_cell')->cascadeOnDelete();
            $table->foreignId('mask_id')->nullable()->references('id')->on('masks')->cascadeOnDelete();
            $table->foreignId('change_source_dataset_grid_cell_id')->nullable()->references('id')->on('source_dataset_grid_cell')->cascadeOnDelete();
            $table->foreignId('change_mask_id')->nullable()->nullable()->references('id')->on('masks')->cascadeOnDelete();
            $table->foreignId('label_class_id')->references('id')->on('label_classes')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('prediction_labels');
    }
};
