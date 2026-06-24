<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('routes', function (Blueprint $table) {
            $table->id();
            $table->string('from_location');
            $table->string('to_location');
            $table->decimal('fare_kes', 10, 2);
            $table->string('departure_time');
            $table->string('arrival_time');
            $table->string('duration');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['from_location', 'to_location']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('routes');
    }
};
