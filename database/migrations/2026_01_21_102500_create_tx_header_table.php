<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tx_header', function (Blueprint $table) {
            $table->unsignedInteger('header_id')->primary()->autoIncrement();
            // $table->string('brand_code', 50);
            $table->unsignedInteger('invoice_no');
            $table->unsignedInteger('wip_no');
            $table->string('account_code', 20)->nullable();
            $table->string('customer_name', 150)->nullable();
            $table->text('address_1')->nullable();
            $table->text('address_2')->nullable();
            $table->text('address_3')->nullable();
            $table->text('address_4')->nullable();
            $table->text('address_5')->nullable();
            $table->string('department', 50)->nullable();
            $table->date('invoice_date');
            $table->unsignedInteger('magic_id');
            $table->enum('document_type', ['I', 'C'])->comment('I = Invoice, C = Credit Note');
            $table->decimal('exchange_rate', 10, 2)->nullable();
            $table->string('registration_no', 20)->nullable();
            $table->string('chassis', 25)->nullable();
            $table->unsignedInteger('mileage');
            $table->char('currency_code', 3);
            $table->decimal('gross_value', 20, 2)->nullable();
            $table->decimal('net_value', 20, 2)->nullable();
            $table->string('customer_discount', 10);
            $table->char('service_code', 3)->nullable();
            $table->date('registration_date')->nullable();
            $table->string('description', 250)->nullable();
            $table->string('engine_no', 20)->nullable();
            $table->string('phone_number_1', 20)->nullable();
            $table->string('phone_number_2', 20)->nullable();
            $table->string('phone_number_3', 20)->nullable();
            $table->string('phone_number_4', 20)->nullable();
            $table->string('operator_code', 20)->nullable();
            $table->string('operator_name', 150)->nullable();
            $table->string('account_company', 20);
            $table->string('pos_code', 20);
            $table->string('created_by', 50)->comment('User yang Create Data');
            $table->dateTime('created_date')->nullable()->useCurrent()->comment('Kapan data nya di Create');
            $table->string('updated_by', 50)->nullable()->comment('User yang Update Data');
            $table->dateTime('updated_date')->nullable()->comment('Kapan data nya di Update');
            $table->char('unique_id', 36)->unique()->comment('UUIDV4, di gunakan untuk Get Data dari URL');
            $table->enum('is_active', ['0', '1'])->nullable()->default('1');
            
            // Indexes
            // $table->index('customer_name', 'idx_customer_name');
            $table->index('chassis', 'idx_chassis');
            $table->index('invoice_date', 'idx_invoice_date');
            $table->index('invoice_no', 'idx_invoice_no');
            $table->index('wip_no', 'idx_wip_no');
            // $table->index('registration_no', 'idx_registration_no');
            $table->index('pos_code', 'idx_pos_code');
            $table->index('created_by', 'idx_created_by');
            $table->index('updated_by', 'idx_updated_by');
            $table->index('is_active', 'idx_is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tx_header');
    }
};
