<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tx_body', function (Blueprint $table) {
            $table->unsignedInteger('body_id')->primary()->autoIncrement();
            $table->string('part_no', 100);
            $table->unsignedInteger('invoice_no');
            $table->string('description', 250)->nullable();
            $table->decimal('qty', 10, 2)->default(0);
            $table->decimal('selling_price', 20, 2);
            $table->decimal('discount', 5, 2)->default(0);
            $table->decimal('extended_price', 20, 2)->default(0);
            $table->decimal('menu_price', 20, 2)->default(0);
            $table->char('vat', 1)->nullable();
            $table->char('menu_vat', 1)->nullable();
            $table->decimal('cost_price', 20, 2)->default(0);
            $table->char('analysis_code', 1);
            $table->char('invoice_status', 1)->comment('X = Closed, C = Completed');
            $table->string('unit', 10)->comment('Example : Litre, Each, Pieces');
            $table->unsignedInteger('mins_per_unit')->nullable();
            $table->unsignedInteger('wip_no');
            $table->unsignedMediumInteger('line');
            $table->string('account_code', 20)->nullable();
            $table->string('department', 50)->nullable();
            $table->string('franchise_code', 3)->nullable();
            $table->char('sales_type', 1);
            $table->string('warranty_code', 3)->nullable();
            $table->char('menu_flag', 1)->nullable();
            $table->decimal('contribution', 5, 2)->default(0);
            $table->date('date_decard')->nullable();
            $table->unsignedInteger('magic_1');
            $table->unsignedInteger('magic_2');
            $table->unsignedInteger('po_no')->nullable();
            $table->unsignedInteger('grn_no')->nullable();
            $table->unsignedTinyInteger('menu_code')->nullable();
            $table->char('labour_rates', 1)->nullable();
            $table->string('supplier_code', 20)->nullable();
            $table->unsignedTinyInteger('menu_link')->default(0);
            $table->decimal('currency_price', 20, 2)->nullable();
            $table->enum('part_or_labour', ['P', 'L']);
            $table->string('operator_code', 20)->nullable();
            $table->string('operator_name', 150)->nullable();
            $table->string('pos_code', 20);
            $table->string('created_by', 50)->comment('User yang Create Data');
            $table->dateTime('created_date')->nullable()->useCurrent()->comment('Kapan data nya di Create');
            $table->string('updated_by', 50)->nullable()->comment('User yang Update Data');
            $table->dateTime('updated_date')->nullable()->comment('Kapan data nya di Update');
            $table->char('unique_id', 36)->unique()->comment('UUIDV4, di gunakan untuk Get Data dari URL');
            $table->enum('is_active', ['0', '1'])->nullable()->default('1');
            
            // Indexes
            $table->index('part_no', 'idx_part_no');
            $table->index('invoice_no', 'idx_invoice_no');
            $table->index('wip_no', 'idx_wip_no');
            $table->index('created_by', 'idx_created_by');
            $table->index('updated_by', 'idx_updated_by');
            // $table->index('unique_id', 'idx_unique_id');
            $table->index('is_active', 'idx_is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tx_body');
    }
};
