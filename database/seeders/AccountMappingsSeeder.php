<?php

namespace Database\Seeders;

use App\Models\AccountMapping;
use App\Models\ChartOfAccounts;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class AccountMappingsSeeder extends Seeder
{
    public function run(): void
    {
        // Get default tenant
        $tenant = Tenant::where('code', 'default')->first();
        if (!$tenant) return;

        // Get all accounts by code for easy lookup
        $accounts = ChartOfAccounts::all()->keyBy('account_code');

        $mappings = [
            // Purchase Order Mappings
            [
                'transaction_type' => 'purchase_order',
                'mapping_key' => 'po_inventory',
                'account_code' => '1-1230',
                'description' => 'Inventory received from PO'
            ],
            [
                'transaction_type' => 'purchase_order',
                'mapping_key' => 'po_supplier',
                'account_code' => '2-1010',
                'description' => 'Accounts Payable - Supplier'
            ],

            // Goods Receipt Mappings
            [
                'transaction_type' => 'goods_receipt',
                'mapping_key' => 'gr_inventory',
                'account_code' => '1-1230',
                'description' => 'Inventory posting from GR'
            ],
            [
                'transaction_type' => 'goods_receipt',
                'mapping_key' => 'gr_supplier',
                'account_code' => '2-1010',
                'description' => 'Accounts Payable on goods receipt'
            ],

            // Sales Transaction Mappings
            [
                'transaction_type' => 'sales_transaction',
                'mapping_key' => 'sales_revenue',
                'account_code' => '4-1010',
                'description' => 'Sales Revenue'
            ],
            [
                'transaction_type' => 'sales_transaction',
                'mapping_key' => 'sales_cogs',
                'account_code' => '5-1010',
                'description' => 'Cost of Sales'
            ],
            [
                'transaction_type' => 'sales_transaction',
                'mapping_key' => 'sales_ar',
                'account_code' => '1-1110',
                'description' => 'Accounts Receivable'
            ],
            [
                'transaction_type' => 'sales_transaction',
                'mapping_key' => 'sales_cash',
                'account_code' => '1-1010',
                'description' => 'Cash from Sales'
            ],
            [
                'transaction_type' => 'sales_transaction',
                'mapping_key' => 'sales_inventory',
                'account_code' => '1-1230',
                'description' => 'Inventory reduction on sales'
            ],

            // Sales Return Mappings
            [
                'transaction_type' => 'sales_return',
                'mapping_key' => 'return_inventory',
                'account_code' => '1-1230',
                'description' => 'Inventory returned by customer'
            ],
            [
                'transaction_type' => 'sales_return',
                'mapping_key' => 'return_cogs',
                'account_code' => '5-1010',
                'description' => 'COGS reversal on return'
            ],
            [
                'transaction_type' => 'sales_return',
                'mapping_key' => 'return_revenue',
                'account_code' => '4-1040',
                'description' => 'Sales return/revenue reduction'
            ],
            [
                'transaction_type' => 'sales_return',
                'mapping_key' => 'return_cash',
                'account_code' => '1-1010',
                'description' => 'Cash refund for returned goods'
            ],

            // Purchase Return Mappings
            [
                'transaction_type' => 'purchase_return',
                'mapping_key' => 'pr_inventory',
                'account_code' => '1-1230',
                'description' => 'Inventory returned to supplier'
            ],
            [
                'transaction_type' => 'purchase_return',
                'mapping_key' => 'pr_supplier',
                'account_code' => '2-1010',
                'description' => 'AP reduction on PR'
            ],

            // Payroll Mappings
            [
                'transaction_type' => 'payroll',
                'mapping_key' => 'payroll_expense',
                'account_code' => '5-4010',
                'description' => 'Wages & Salaries Expense'
            ],
            [
                'transaction_type' => 'payroll',
                'mapping_key' => 'payroll_cash',
                'account_code' => '1-1010',
                'description' => 'Cash paid for payroll'
            ],
            [
                'transaction_type' => 'payroll',
                'mapping_key' => 'payroll_payable',
                'account_code' => '2-1050',
                'description' => 'Salaries Payable'
            ],

            // Cash Reconciliation Mappings
            [
                'transaction_type' => 'cash_reconciliation',
                'mapping_key' => 'recon_variance',
                'account_code' => '5-3090',
                'description' => 'Variance on cash reconciliation'
            ],

            // Stock Transfer Mappings
            [
                'transaction_type' => 'stock_transfer',
                'mapping_key' => 'transfer_source',
                'account_code' => '1-1230',
                'description' => 'Inventory reduction at source'
            ],
            [
                'transaction_type' => 'stock_transfer',
                'mapping_key' => 'transfer_destination',
                'account_code' => '1-1230',
                'description' => 'Inventory increase at destination'
            ],

            // Stock Opname Mappings
            [
                'transaction_type' => 'stock_opname',
                'mapping_key' => 'opname_variance',
                'account_code' => '5-1010',
                'description' => 'Inventory variance on opname'
            ],
        ];

        foreach ($mappings as $mapping) {
            $accountCode = $mapping['account_code'];
            unset($mapping['account_code']);

            if (isset($accounts[$accountCode])) {
                AccountMapping::firstOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'transaction_type' => $mapping['transaction_type'],
                        'mapping_key' => $mapping['mapping_key'],
                    ],
                    [
                        'gl_account_id' => $accounts[$accountCode]->id,
                        'description' => $mapping['description'],
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
