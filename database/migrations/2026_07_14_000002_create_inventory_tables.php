<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('supplied_products')->nullable();
            $table->timestamps();
        });

        Schema::create('inventory_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->nullable()->constrained('inventory_suppliers')->nullOnDelete();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('category');
            $table->text('description')->nullable();
            $table->string('unit')->default('unité');
            $table->unsignedInteger('initial_quantity')->default(0);
            $table->unsignedInteger('available_quantity')->default(0);
            $table->unsignedInteger('minimum_threshold')->default(0);
            $table->decimal('unit_price', 14, 2)->default(0);
            $table->decimal('total_value', 16, 2)->default(0);
            $table->date('acquired_at')->nullable();
            $table->string('condition')->default('Bon');
            $table->text('observations')->nullable();
            $table->timestamps();

            $table->index(['category', 'available_quantity']);
        });

        Schema::create('inventory_stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id')->constrained('inventory_materials')->cascadeOnDelete();
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('movement_date');
            $table->string('movement_type')->default('sortie');
            $table->string('voucher_number')->nullable();
            $table->string('requester_name')->nullable();
            $table->string('requesting_service')->nullable();
            $table->string('function')->nullable();
            $table->unsignedInteger('requested_quantity')->default(0);
            $table->unsignedInteger('granted_quantity')->default(0);
            $table->unsignedInteger('stock_before')->default(0);
            $table->unsignedInteger('stock_after')->default(0);
            $table->text('reason')->nullable();
            $table->string('signature')->nullable();
            $table->text('observation')->nullable();
            $table->timestamps();

            $table->index(['movement_date', 'movement_type']);
        });

        Schema::create('inventory_supply_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id')->constrained('inventory_materials')->cascadeOnDelete();
            $table->date('order_date');
            $table->unsignedInteger('remaining_quantity')->default(0);
            $table->unsignedInteger('quantity_to_order');
            $table->string('status')->default('demandee');
            $table->text('observation')->nullable();
            $table->timestamps();

            $table->index(['order_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_supply_orders');
        Schema::dropIfExists('inventory_stock_movements');
        Schema::dropIfExists('inventory_materials');
        Schema::dropIfExists('inventory_suppliers');
    }
};
