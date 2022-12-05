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
        Schema::create('inspect_dataset_labels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspect_dataset_id')->references('id')->on('inspect_dataset')->cascadeOnDelete();
            $table->foreignId('grid_id')->references('id')->on('dataset_grids')->cascadeOnDelete();
            $table->foreignId('label_class_id')->references('id')->on('label_classes')->cascadeOnDelete();
            $table->json('information')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inspect_dataset_labels');
    }
};
