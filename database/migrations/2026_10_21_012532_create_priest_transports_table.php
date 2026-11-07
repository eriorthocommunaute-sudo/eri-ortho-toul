<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('priest_transports', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique(); // TRP-YYYY-NNNN
            $table->date('transport_date');
            $table->string('origin');
            $table->string('destination');
            $table->string('occasion')->nullable(); // messe, conférence, visite, autre
            $table->decimal('distance_km', 8, 2)->nullable();
            $table->decimal('amount', 8, 2);
            $table->enum('transport_type', ['personal_vehicle', 'taxi', 'train', 'bus', 'flight', 'other'])->default('personal_vehicle');
            $table->string('driver_name')->nullable();
            $table->string('financial_reference'); // RF-YYYY-NNNN
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['transport_date']);
            $table->index('financial_reference');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('priest_transports');
    }
};