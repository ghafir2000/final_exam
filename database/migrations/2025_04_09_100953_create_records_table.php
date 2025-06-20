<?php

use App\Models\Pet;
use App\Models\Animal;
use App\Models\Booking;
use App\Models\Veterinarian;
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
        Schema::create('records', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->json('stats');
            $table->softDeletes();
            $table->foreignIdFor(Booking::class)->constrained()->cascadeOnDelete()->cascadeOnUpdate()->nullable();
           
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('records');
    }
};
