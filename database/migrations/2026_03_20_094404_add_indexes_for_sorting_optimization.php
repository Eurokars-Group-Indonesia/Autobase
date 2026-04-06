<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Add indexes for columns used in sorting and filtering to optimize query performance
     */
    public function up(): void
    {
        // Transaction Header (tx_header) indexes
        Schema::table('tx_header', function (Blueprint $table) {
            // Index for account_code (sortable column)
            if (!$this->hasIndex('tx_header', 'idx_account_code')) {
                $table->index('account_code', 'idx_account_code');
            }

            // Index for customer_name (sortable column) - regular B-TREE index for ORDER BY
            if (!$this->hasIndex('tx_header', 'idx_customer_name')) {
                $table->index('customer_name', 'idx_customer_name');
            }

            // Index for registration_no (sortable column) - regular B-TREE index for ORDER BY
            if (!$this->hasIndex('tx_header', 'idx_registration_no')) {
                $table->index('registration_no', 'idx_registration_no');
            }

            // Index for document_type (sortable column)
            // if (!$this->hasIndex('tx_header', 'idx_document_type')) {
            //     $table->index('document_type', 'idx_document_type');
            // }

            // Index for gross_value (sortable column)
            if (!$this->hasIndex('tx_header', 'idx_gross_value')) {
                $table->index('gross_value', 'idx_gross_value');
            }

            // Index for net_value (sortable column)
            if (!$this->hasIndex('tx_header', 'idx_net_value')) {
                $table->index('net_value', 'idx_net_value');
            }

            // Indexes for phone_number columns (filter/search columns)
            // Optimized for LIKE '%phone%' queries - each column gets its own index
            // if (!$this->hasIndex('tx_header', 'idx_phone_number_1')) {
            //     $table->index('phone_number_1', 'idx_phone_number_1');
            // }
            // if (!$this->hasIndex('tx_header', 'idx_phone_number_2')) {
            //     $table->index('phone_number_2', 'idx_phone_number_2');
            // }
            // if (!$this->hasIndex('tx_header', 'idx_phone_number_3')) {
            //     $table->index('phone_number_3', 'idx_phone_number_3');
            // }
            // if (!$this->hasIndex('tx_header', 'idx_phone_number_4')) {
            //     $table->index('phone_number_4', 'idx_phone_number_4');
            // }
        });

        // Transaction Body (tx_body) indexes
        Schema::table('tx_body', function (Blueprint $table) {
            // Index for description (sortable column)
            if (!$this->hasIndex('tx_body', 'idx_description')) {
                $table->index('description', 'idx_description');
            }

            // Index for date_decard (sortable and filter column)
            if (!$this->hasIndex('tx_body', 'idx_date_decard')) {
                $table->index('date_decard', 'idx_date_decard');
            }

            // Index for qty (sortable column)
            if (!$this->hasIndex('tx_body', 'idx_qty')) {
                $table->index('qty', 'idx_qty');
            }

            // Index for unit (sortable column)
            if (!$this->hasIndex('tx_body', 'idx_unit')) {
                $table->index('unit', 'idx_unit');
            }

            // Index for cost_price (sortable column)
            if (!$this->hasIndex('tx_body', 'idx_cost_price')) {
                $table->index('cost_price', 'idx_cost_price');
            }

            // Index for selling_price (sortable column)
            if (!$this->hasIndex('tx_body', 'idx_selling_price')) {
                $table->index('selling_price', 'idx_selling_price');
            }

            // Index for discount (sortable column)
            if (!$this->hasIndex('tx_body', 'idx_discount')) {
                $table->index('discount', 'idx_discount');
            }

            // Index for extended_price (sortable column)
            if (!$this->hasIndex('tx_body', 'idx_extended_price')) {
                $table->index('extended_price', 'idx_extended_price');
            }

            // Index for part_or_labour (sortable column)
            // if (!$this->hasIndex('tx_body', 'idx_part_or_labour')) {
            //     $table->index('part_or_labour', 'idx_part_or_labour');
            // }

            // Index for invoice_status (sortable and filter column)
            if (!$this->hasIndex('tx_body', 'idx_invoice_status')) {
                $table->index('invoice_status', 'idx_invoice_status');
            }

            // Index for analysis_code (commonly used column)
            if (!$this->hasIndex('tx_body', 'idx_analysis_code')) {
                $table->index('analysis_code', 'idx_analysis_code');
            }

            // Index for account_code (commonly used column)
            if (!$this->hasIndex('tx_body', 'idx_account_code')) {
                $table->index('account_code', 'idx_account_code');
            }

            // Index for department (commonly used column)
            if (!$this->hasIndex('tx_body', 'idx_department')) {
                $table->index('department', 'idx_department');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop Transaction Header indexes
        Schema::table('tx_header', function (Blueprint $table) {
            if ($this->hasIndex('tx_header', 'idx_account_code')) {
                $table->dropIndex('idx_account_code');
            }
            if ($this->hasIndex('tx_header', 'idx_customer_name')) {
                $table->dropIndex('idx_customer_name');
            }
            if ($this->hasIndex('tx_header', 'idx_registration_no')) {
                $table->dropIndex('idx_registration_no');
            }
            if ($this->hasIndex('tx_header', 'idx_document_type')) {
                $table->dropIndex('idx_document_type');
            }
            if ($this->hasIndex('tx_header', 'idx_gross_value')) {
                $table->dropIndex('idx_gross_value');
            }
            if ($this->hasIndex('tx_header', 'idx_net_value')) {
                $table->dropIndex('idx_net_value');
            }
            // Drop phone number indexes
            // if ($this->hasIndex('tx_header', 'idx_phone_number_1')) {
            //     $table->dropIndex('idx_phone_number_1');
            // }
            // if ($this->hasIndex('tx_header', 'idx_phone_number_2')) {
            //     $table->dropIndex('idx_phone_number_2');
            // }
            // if ($this->hasIndex('tx_header', 'idx_phone_number_3')) {
            //     $table->dropIndex('idx_phone_number_3');
            // }
            // if ($this->hasIndex('tx_header', 'idx_phone_number_4')) {
            //     $table->dropIndex('idx_phone_number_4');
            // }
        });

        // Drop Transaction Body indexes
        Schema::table('tx_body', function (Blueprint $table) {
            if ($this->hasIndex('tx_body', 'idx_description')) {
                $table->dropIndex('idx_description');
            }
            if ($this->hasIndex('tx_body', 'idx_date_decard')) {
                $table->dropIndex('idx_date_decard');
            }
            if ($this->hasIndex('tx_body', 'idx_qty')) {
                $table->dropIndex('idx_qty');
            }
            if ($this->hasIndex('tx_body', 'idx_unit')) {
                $table->dropIndex('idx_unit');
            }
            if ($this->hasIndex('tx_body', 'idx_cost_price')) {
                $table->dropIndex('idx_cost_price');
            }
            if ($this->hasIndex('tx_body', 'idx_selling_price')) {
                $table->dropIndex('idx_selling_price');
            }
            if ($this->hasIndex('tx_body', 'idx_discount')) {
                $table->dropIndex('idx_discount');
            }
            if ($this->hasIndex('tx_body', 'idx_extended_price')) {
                $table->dropIndex('idx_extended_price');
            }
            if ($this->hasIndex('tx_body', 'idx_part_or_labour')) {
                $table->dropIndex('idx_part_or_labour');
            }
            if ($this->hasIndex('tx_body', 'idx_invoice_status')) {
                $table->dropIndex('idx_invoice_status');
            }
            if ($this->hasIndex('tx_body', 'idx_analysis_code')) {
                $table->dropIndex('idx_analysis_code');
            }
            if ($this->hasIndex('tx_body', 'idx_account_code')) {
                $table->dropIndex('idx_account_code');
            }
            if ($this->hasIndex('tx_body', 'idx_department')) {
                $table->dropIndex('idx_department');
            }
        });
    }

    /**
     * Helper method to check if an index exists on a table
     */
    private function hasIndex(string $table, string $indexName): bool
    {
        $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
        return !empty($indexes);
    }
};
