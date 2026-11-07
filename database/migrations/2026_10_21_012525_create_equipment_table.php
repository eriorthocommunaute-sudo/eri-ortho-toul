<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique(); // EQP-YYYY-NNNN
            $table->string('name');
            $table->string('category')->nullable(); // audio, video, furniture, liturgical, IT, other
            $table->text('description')->nullable();
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_price', 10, 2)->nullable();
            $table->string('purchase_reference')->nullable(); // DA-YYYY-NNNN or EXC-YYYY-NNNN
            $table->string('financial_reference')->nullable(); // RF-YYYY-NNNN
            $table->string('supplier')->nullable();
            $table->string('location')->nullable(); // where stored
            $table->enum('condition', ['new', 'good', 'fair', 'poor', 'disposed'])->default('good');
            $table->string('serial_number')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['category', 'condition']);
            $table->index('location');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};