<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recurring_expense_templates', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('name');
            $table->string('category')->unique();
            $table->decimal('amount', 12, 2);
            $table->unsignedTinyInteger('due_day')->default(1);
            $table->string('beneficiary')->nullable();
            $table->string('payment_method')->nullable();
            $table->unsignedSmallInteger('start_year');
            $table->unsignedTinyInteger('start_month');
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('recurring_expense_occurrences', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('template_id')->constrained('recurring_expense_templates')->restrictOnDelete();
            $table->unsignedSmallInteger('period_year');
            $table->unsignedTinyInteger('period_month');
            $table->decimal('amount_due', 12, 2);
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->date('due_date');
            $table->date('paid_at')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable();
            $table->string('proof_path')->nullable();
            $table->string('status')->default('pending');
            $table->foreignId('financial_entry_id')->nullable()->constrained('financial_entries')->nullOnDelete();
            $table->foreignId('paid_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['template_id', 'period_year', 'period_month'], 'recurring_expense_period_unique');
            $table->index(['period_year', 'period_month', 'status']);
        });

        // Initial fixed amounts requested by the association, effective November 2026.
        $now = now();
        DB::table('recurring_expense_templates')->insert([
            ['reference' => 'RFT-RENT', 'name' => 'Loyer', 'category' => 'rent', 'amount' => 150.00, 'due_day' => 1, 'beneficiary' => null, 'payment_method' => null, 'start_year' => 2026, 'start_month' => 11, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['reference' => 'RFT-INSURANCE', 'name' => 'Assurance', 'category' => 'insurance', 'amount' => 58.00, 'due_day' => 1, 'beneficiary' => null, 'payment_method' => null, 'start_year' => 2026, 'start_month' => 11, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['reference' => 'RFT-PRIEST', 'name' => 'Indemnité du prêtre', 'category' => 'indemnite', 'amount' => 100.00, 'due_day' => 1, 'beneficiary' => null, 'payment_method' => null, 'start_year' => 2026, 'start_month' => 11, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('recurring_expense_occurrences');
        Schema::dropIfExists('recurring_expense_templates');
    }
};
