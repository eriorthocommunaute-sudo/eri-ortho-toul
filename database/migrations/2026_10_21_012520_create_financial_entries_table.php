<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_entries', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique(); // RF-YYYY-NNNN
            $table->date('entry_date');
            $table->enum('type', ['income', 'expense']);
            $table->string('category'); // cotisation, offrande, don, achat, transport, indemnite, autre
            $table->string('source_type'); // receipt, offrande, donation, purchase_request, priest_transport, priest_indemnity, exceptional_demand, manual
            $table->unsignedBigInteger('source_id'); // polymorphic relation to source record
            $table->string('description');
            $table->decimal('amount', 12, 2);
            $table->enum('payment_method', ['cash', 'transfer', 'check', 'other'])->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['entry_date', 'type']);
            $table->index(['source_type', 'source_id']);
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_entries');
    }
};