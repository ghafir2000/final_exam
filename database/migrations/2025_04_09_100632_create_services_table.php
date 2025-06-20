<?php

use App\Models\Breed;
use App\Models\Partner;
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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->string('name');
            $table->decimal('price', 8, 2);
            $table->text('description');
            $table->integer('duration'); // Duration in minutes or other appropriate unit
            $table->json('available_times'); // JSON column to store an array of available times in this format {["time",bool],[...]}
            $table->nullableMorphs('servicable');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
