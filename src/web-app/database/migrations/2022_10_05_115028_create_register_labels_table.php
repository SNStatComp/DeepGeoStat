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
        Schema::create('register_labels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('register_id')->references('id')->on('registers')->cascadeOnDelete();
            $table->foreignId('grid_cell_id')->references('id')->on('grid_cells')->cascadeOnDelete();
            $table->foreignId('mask_id')->references('id')->on('masks')->nullable();
            $table->foreignId('label_class_id')->references('id')->on('register_label_classes')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('register_labels');
    }
};
