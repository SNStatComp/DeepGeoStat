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
        Schema::create('label_evidence_labels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->references('id')->on('users')->nullOnDelete();
            $table->foreignId('label_evidence_id')->references('id')->on('label_evidence')->cascadeOnDelete();
            $table->foreignId('grid_id')->references('id')->on('dataset_grids')->cascadeOnDelete();
            $table->foreignId('label_class_id')->references('id')->on('label_classes')->cascadeOnDelete();
            $table->decimal('probability')->nullable();
            $table->timestamps();

            $table->unique(['label_evidence_id', 'grid_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('label_evidence_labels');
    }
};
