<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('priest_indemnities', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique(); // PRE-YYYY-NNNN
            $table->date('service_date');
            $table->string('occasion'); // messe, baptême, mariage, enterrement, confession, autre
            $table->string('location')->nullable();
            $table->decimal('amount', 8, 2);
            $table->string('financial_reference'); // RF-YYYY-NNNN
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['service_date']);
            $table->index('financial_reference');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('priest_indemnities');
    }
};