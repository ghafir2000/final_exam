<?php

use App\Models\Customer;
use App\Models\Order;
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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->float('total');
            $table->integer('status');
            $table->string('stripe_session_id')->nullable(); 
            $table->string('stripe_payment_intent_id')->nullable();
            $table->softDeletes();
            $table->nullableMorphs('payable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
