<?php

use App\Models\Breed;
use App\Models\Service;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('breed_service', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Service::class)->constrained()->cascadeOnDelete()->cascadeOnUpdate()->nullable();
            $table->foreignIdFor(Breed::class)->constrained()->cascadeOnDelete()->cascadeOnUpdate()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('breed_service');
    }
};
