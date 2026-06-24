<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_reference')->unique();
            $table->string('from_location');
            $table->string('to_location');
            $table->string('trip_type')->default('One Way');
            $table->date('travel_date');
            $table->string('departure_time');
            $table->string('arrival_time');
            $table->string('duration');
            $table->integer('passengers')->default(1);
            $table->json('seats');
            $table->string('passenger_name');
            $table->string('passenger_email');
            $table->string('passenger_phone');
            $table->decimal('fare_kes', 10, 2);
            $table->decimal('total_kes', 10, 2);
            $table->string('currency')->default('KES');
            $table->decimal('total_paid', 10, 2);
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('bookings');
    }
};
