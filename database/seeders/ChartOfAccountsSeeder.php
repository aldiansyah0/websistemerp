<?php

namespace Database\Seeders;

use App\Models\ChartOfAccounts;
use Illuminate\Database\Seeder;

class ChartOfAccountsSeeder extends Seeder
{
    public function run(): void
    {
        // Standard Indonesia Chart of Accounts (SKA)
        $accounts = [
            // === ASSETS (1000-1999) ===
            ['code' => '1', 'name' => 'ASSETS', 'type' => 'asset', 'class' => 'other_asset', 'is_header' => true],

            // Current Assets (1100-1200)
            ['code' => '1-1', 'name' => 'CURRENT ASSETS', 'type' => 'asset', 'class' => 'current_asset', 'parent' => '1', 'is_header' => true],
            ['code' => '1-1010', 'name' => 'Cash in Bank', 'type' => 'asset', 'class' => 'current_asset', 'parent' => '1-1'],
            ['code' => '1-1020', 'name' => 'Cash on Hand', 'type' => 'asset', 'class' => 'current_asset', 'parent' => '1-1'],
            ['code' => '1-1030', 'name' => 'Cash Equivalents', 'type' => 'asset', 'class' => 'current_asset', 'parent' => '1-1'],
            ['code' => '1-1110', 'name' => 'Accounts Receivable', 'type' => 'asset', 'class' => 'current_asset', 'parent' => '1-1'],
            ['code' => '1-1120', 'name' => 'Allowance for Doubtful Receivables', 'type' => 'asset', 'class' => 'current_asset', 'parent' => '1-1'],
            ['code' => '1-1210', 'name' => 'Inventory - Raw Materials', 'type' => 'asset', 'class' => 'current_asset', 'parent' => '1-1'],
            ['code' => '1-1220', 'name' => 'Inventory - Work in Process', 'type' => 'asset', 'class' => 'current_asset', 'parent' => '1-1'],
            ['code' => '1-1230', 'name' => 'Inventory - Finished Goods', 'type' => 'asset', 'class' => 'current_asset', 'parent' => '1-1'],
            ['code' => '1-1240', 'name' => 'Inventory - Supplies', 'type' => 'asset', 'class' => 'current_asset', 'parent' => '1-1'],
            ['code' => '1-1310', 'name' => 'Prepaid Expenses', 'type' => 'asset', 'class' => 'current_asset', 'parent' => '1-1'],
            ['code' => '1-1320', 'name' => 'Prepaid Insurance', 'type' => 'asset', 'class' => 'current_asset', 'parent' => '1-1'],

            // Fixed Assets (1300-1400)
            ['code' => '1-2', 'name' => 'FIXED ASSETS', 'type' => 'asset', 'class' => 'fixed_asset', 'parent' => '1', 'is_header' => true],
            ['code' => '1-2010', 'name' => 'Land', 'type' => 'asset', 'class' => 'fixed_asset', 'parent' => '1-2'],
            ['code' => '1-2020', 'name' => 'Building & Improvements', 'type' => 'asset', 'class' => 'fixed_asset', 'parent' => '1-2'],
            ['code' => '1-2030', 'name' => 'Machinery & Equipment', 'type' => 'asset', 'class' => 'fixed_asset', 'parent' => '1-2'],
            ['code' => '1-2040', 'name' => 'Furniture & Fixtures', 'type' => 'asset', 'class' => 'fixed_asset', 'parent' => '1-2'],
            ['code' => '1-2050', 'name' => 'Vehicles', 'type' => 'asset', 'class' => 'fixed_asset', 'parent' => '1-2'],
            ['code' => '1-2060', 'name' => 'Computer Equipment', 'type' => 'asset', 'class' => 'fixed_asset', 'parent' => '1-2'],
            ['code' => '1-2110', 'name' => 'Accumulated Depreciation - Building', 'type' => 'asset', 'class' => 'fixed_asset', 'parent' => '1-2'],
            ['code' => '1-2120', 'name' => 'Accumulated Depreciation - Machinery', 'type' => 'asset', 'class' => 'fixed_asset', 'parent' => '1-2'],
            ['code' => '1-2130', 'name' => 'Accumulated Depreciation - Furniture', 'type' => 'asset', 'class' => 'fixed_asset', 'parent' => '1-2'],
            ['code' => '1-2140', 'name' => 'Accumulated Depreciation - Vehicles', 'type' => 'asset', 'class' => 'fixed_asset', 'parent' => '1-2'],
            ['code' => '1-2150', 'name' => 'Accumulated Depreciation - Computer', 'type' => 'asset', 'class' => 'fixed_asset', 'parent' => '1-2'],

            // Other Assets (1500+)
            ['code' => '1-3', 'name' => 'OTHER ASSETS', 'type' => 'asset', 'class' => 'other_asset', 'parent' => '1', 'is_header' => true],
            ['code' => '1-3010', 'name' => 'Goodwill', 'type' => 'asset', 'class' => 'other_asset', 'parent' => '1-3'],
            ['code' => '1-3020', 'name' => 'Intangible Assets', 'type' => 'asset', 'class' => 'other_asset', 'parent' => '1-3'],
            ['code' => '1-3030', 'name' => 'Deferred Tax Assets', 'type' => 'asset', 'class' => 'other_asset', 'parent' => '1-3'],

            // === LIABILITIES (2000-2999) ===
            ['code' => '2', 'name' => 'LIABILITIES', 'type' => 'liability', 'class' => 'current_liability', 'is_header' => true],

            // Current Liabilities (2100-2200)
            ['code' => '2-1', 'name' => 'CURRENT LIABILITIES', 'type' => 'liability', 'class' => 'current_liability', 'parent' => '2', 'is_header' => true],
            ['code' => '2-1010', 'name' => 'Accounts Payable', 'type' => 'liability', 'class' => 'current_liability', 'parent' => '2-1'],
            ['code' => '2-1020', 'name' => 'Short-term Debt', 'type' => 'liability', 'class' => 'current_liability', 'parent' => '2-1'],
            ['code' => '2-1030', 'name' => 'Income Tax Payable', 'type' => 'liability', 'class' => 'current_liability', 'parent' => '2-1'],
            ['code' => '2-1040', 'name' => 'Sales Tax Payable', 'type' => 'liability', 'class' => 'current_liability', 'parent' => '2-1'],
            ['code' => '2-1050', 'name' => 'Salaries Payable', 'type' => 'liability', 'class' => 'current_liability', 'parent' => '2-1'],
            ['code' => '2-1060', 'name' => 'Wages Payable', 'type' => 'liability', 'class' => 'current_liability', 'parent' => '2-1'],
            ['code' => '2-1070', 'name' => 'Employee Benefits Payable', 'type' => 'liability', 'class' => 'current_liability', 'parent' => '2-1'],
            ['code' => '2-1080', 'name' => 'Accrued Expenses', 'type' => 'liability', 'class' => 'current_liability', 'parent' => '2-1'],

            // Long-term Liabilities (2200-2300)
            ['code' => '2-2', 'name' => 'LONG-TERM LIABILITIES', 'type' => 'liability', 'class' => 'long_term_liability', 'parent' => '2', 'is_header' => true],
            ['code' => '2-2010', 'name' => 'Long-term Debt', 'type' => 'liability', 'class' => 'long_term_liability', 'parent' => '2-2'],
            ['code' => '2-2020', 'name' => 'Deferred Tax Liabilities', 'type' => 'liability', 'class' => 'long_term_liability', 'parent' => '2-2'],
            ['code' => '2-2030', 'name' => 'Pension Obligations', 'type' => 'liability', 'class' => 'long_term_liability', 'parent' => '2-2'],

            // === EQUITY (3000-3999) ===
            ['code' => '3', 'name' => 'EQUITY', 'type' => 'equity', 'class' => 'equity', 'is_header' => true],
            ['code' => '3-1010', 'name' => 'Common Stock', 'type' => 'equity', 'class' => 'equity', 'parent' => '3'],
            ['code' => '3-1020', 'name' => 'Additional Paid-in Capital', 'type' => 'equity', 'class' => 'equity', 'parent' => '3'],
            ['code' => '3-1030', 'name' => 'Retained Earnings', 'type' => 'equity', 'class' => 'equity', 'parent' => '3'],
            ['code' => '3-1040', 'name' => 'Accumulated Other Comprehensive Income', 'type' => 'equity', 'class' => 'equity', 'parent' => '3'],

            // === REVENUE (4000-4999) ===
            ['code' => '4', 'name' => 'REVENUE', 'type' => 'revenue', 'class' => 'operating_revenue', 'is_header' => true],

            // Operating Revenue (4100-4200)
            ['code' => '4-1', 'name' => 'OPERATING REVENUE', 'type' => 'revenue', 'class' => 'operating_revenue', 'parent' => '4', 'is_header' => true],
            ['code' => '4-1010', 'name' => 'Sales Revenue', 'type' => 'revenue', 'class' => 'operating_revenue', 'parent' => '4-1'],
            ['code' => '4-1020', 'name' => 'Service Revenue', 'type' => 'revenue', 'class' => 'operating_revenue', 'parent' => '4-1'],
            ['code' => '4-1030', 'name' => 'Sales Discount', 'type' => 'revenue', 'class' => 'operating_revenue', 'parent' => '4-1'],
            ['code' => '4-1040', 'name' => 'Sales Return', 'type' => 'revenue', 'class' => 'operating_revenue', 'parent' => '4-1'],

            // Non-Operating Revenue (4300+)
            ['code' => '4-2', 'name' => 'NON-OPERATING REVENUE', 'type' => 'revenue', 'class' => 'non_operating_revenue', 'parent' => '4', 'is_header' => true],
            ['code' => '4-2010', 'name' => 'Interest Income', 'type' => 'revenue', 'class' => 'non_operating_revenue', 'parent' => '4-2'],
            ['code' => '4-2020', 'name' => 'Rental Income', 'type' => 'revenue', 'class' => 'non_operating_revenue', 'parent' => '4-2'],
            ['code' => '4-2030', 'name' => 'Gain on Sale of Assets', 'type' => 'revenue', 'class' => 'non_operating_revenue', 'parent' => '4-2'],
            ['code' => '4-2040', 'name' => 'Other Income', 'type' => 'revenue', 'class' => 'non_operating_revenue', 'parent' => '4-2'],

            // === EXPENSES (5000-6999) ===
            ['code' => '5', 'name' => 'OPERATING EXPENSES', 'type' => 'expense', 'class' => 'operating_expense', 'is_header' => true],

            // Cost of Goods Sold (5100-5200)
            ['code' => '5-1', 'name' => 'COST OF GOODS SOLD', 'type' => 'expense', 'class' => 'operating_expense', 'parent' => '5', 'is_header' => true],
            ['code' => '5-1010', 'name' => 'Cost of Sales', 'type' => 'expense', 'class' => 'operating_expense', 'parent' => '5-1'],
            ['code' => '5-1020', 'name' => 'Beginning Inventory', 'type' => 'expense', 'class' => 'operating_expense', 'parent' => '5-1'],
            ['code' => '5-1030', 'name' => 'Purchases', 'type' => 'expense', 'class' => 'operating_expense', 'parent' => '5-1'],
            ['code' => '5-1040', 'name' => 'Ending Inventory', 'type' => 'expense', 'class' => 'operating_expense', 'parent' => '5-1'],
            ['code' => '5-1050', 'name' => 'Freight-in', 'type' => 'expense', 'class' => 'operating_expense', 'parent' => '5-1'],

            // Operating Expenses (5300-5500)
            ['code' => '5-2', 'name' => 'SELLING & DISTRIBUTION EXPENSES', 'type' => 'expense', 'class' => 'operating_expense', 'parent' => '5', 'is_header' => true],
            ['code' => '5-2010', 'name' => 'Sales Salaries', 'type' => 'expense', 'class' => 'operating_expense', 'parent' => '5-2'],
            ['code' => '5-2020', 'name' => 'Sales Commission', 'type' => 'expense', 'class' => 'operating_expense', 'parent' => '5-2'],
            ['code' => '5-2030', 'name' => 'Sales Bonus', 'type' => 'expense', 'class' => 'operating_expense', 'parent' => '5-2'],
            ['code' => '5-2040', 'name' => 'Delivery Expense', 'type' => 'expense', 'class' => 'operating_expense', 'parent' => '5-2'],
            ['code' => '5-2050', 'name' => 'Shipping and Handling', 'type' => 'expense', 'class' => 'operating_expense', 'parent' => '5-2'],
            ['code' => '5-2060', 'name' => 'Advertising Expense', 'type' => 'expense', 'class' => 'operating_expense', 'parent' => '5-2'],

            ['code' => '5-3', 'name' => 'ADMINISTRATIVE EXPENSES', 'type' => 'expense', 'class' => 'operating_expense', 'parent' => '5', 'is_header' => true],
            ['code' => '5-3010', 'name' => 'Administrative Salaries', 'type' => 'expense', 'class' => 'operating_expense', 'parent' => '5-3'],
            ['code' => '5-3020', 'name' => 'Office Supplies', 'type' => 'expense', 'class' => 'operating_expense', 'parent' => '5-3'],
            ['code' => '5-3030', 'name' => 'Telephone Expense', 'type' => 'expense', 'class' => 'operating_expense', 'parent' => '5-3'],
            ['code' => '5-3040', 'name' => 'Utilities', 'type' => 'expense', 'class' => 'operating_expense', 'parent' => '5-3'],
            ['code' => '5-3050', 'name' => 'Rent Expense', 'type' => 'expense', 'class' => 'operating_expense', 'parent' => '5-3'],
            ['code' => '5-3060', 'name' => 'Insurance Expense', 'type' => 'expense', 'class' => 'operating_expense', 'parent' => '5-3'],
            ['code' => '5-3070', 'name' => 'Depreciation Expense', 'type' => 'expense', 'class' => 'operating_expense', 'parent' => '5-3'],
            ['code' => '5-3080', 'name' => 'Amortization Expense', 'type' => 'expense', 'class' => 'operating_expense', 'parent' => '5-3'],
            ['code' => '5-3090', 'name' => 'Bad Debt Expense', 'type' => 'expense', 'class' => 'operating_expense', 'parent' => '5-3'],
            ['code' => '5-3100', 'name' => 'Office Equipment Rental', 'type' => 'expense', 'class' => 'operating_expense', 'parent' => '5-3'],

            // Personnel Expenses (5400-5500)
            ['code' => '5-4', 'name' => 'PERSONNEL EXPENSES', 'type' => 'expense', 'class' => 'operating_expense', 'parent' => '5', 'is_header' => true],
            ['code' => '5-4010', 'name' => 'Wages & Salaries', 'type' => 'expense', 'class' => 'operating_expense', 'parent' => '5-4'],
            ['code' => '5-4020', 'name' => 'Employee Benefits', 'type' => 'expense', 'class' => 'operating_expense', 'parent' => '5-4'],
            ['code' => '5-4030', 'name' => 'Payroll Taxes', 'type' => 'expense', 'class' => 'operating_expense', 'parent' => '5-4'],
            ['code' => '5-4040', 'name' => 'Health Insurance', 'type' => 'expense', 'class' => 'operating_expense', 'parent' => '5-4'],
            ['code' => '5-4050', 'name' => 'Training & Development', 'type' => 'expense', 'class' => 'operating_expense', 'parent' => '5-4'],
            ['code' => '5-4060', 'name' => 'Staff Entertainment', 'type' => 'expense', 'class' => 'operating_expense', 'parent' => '5-4'],

            // Non-Operating Expenses (6000+)
            ['code' => '6', 'name' => 'NON-OPERATING EXPENSES', 'type' => 'expense', 'class' => 'non_operating_expense', 'is_header' => true],
            ['code' => '6-1010', 'name' => 'Interest Expense', 'type' => 'expense', 'class' => 'non_operating_expense', 'parent' => '6'],
            ['code' => '6-1020', 'name' => 'Bank Charges', 'type' => 'expense', 'class' => 'non_operating_expense', 'parent' => '6'],
            ['code' => '6-1030', 'name' => 'Loss on Sale of Assets', 'type' => 'expense', 'class' => 'non_operating_expense', 'parent' => '6'],
            ['code' => '6-1040', 'name' => 'Other Expense', 'type' => 'expense', 'class' => 'non_operating_expense', 'parent' => '6'],

            // Tax Expenses (7000+)
            ['code' => '7', 'name' => 'TAX EXPENSE', 'type' => 'expense', 'class' => 'tax_expense', 'is_header' => true],
            ['code' => '7-1010', 'name' => 'Income Tax Expense', 'type' => 'expense', 'class' => 'tax_expense', 'parent' => '7'],
        ];

        // Map parent codes to IDs
        $codeToId = [];

        foreach ($accounts as &$account) {
            unset($account['parent']); // Remove parent key before insert
        }

        // First pass: Insert header accounts
        foreach ($accounts as $account) {
            $isHeader = $account['is_header'] ?? false;
            if ($isHeader && !str_contains($account['code'], '-')) {
                // Root level headers only
                $created = ChartOfAccounts::create([
                    'account_code' => $account['code'],
                    'account_name' => $account['name'],
                    'account_type' => $account['type'],
                    'account_class' => $account['class'],
                    'parent_id' => null,
                    'is_header' => true,
                    'is_active' => true,
                ]);
                $codeToId[$account['code']] = $created->id;
            }
        }

        // Second pass: Insert all accounts with parent IDs
        foreach ($accounts as $account) {
            $parentId = null;
            if (str_contains($account['code'], '-')) {
                $parentCode = explode('-', $account['code'])[0];
                $parentId = $codeToId[$parentCode] ?? null;
            }

            if (!isset($codeToId[$account['code']])) {
                $created = ChartOfAccounts::create([
                    'account_code' => $account['code'],
                    'account_name' => $account['name'],
                    'account_type' => $account['type'],
                    'account_class' => $account['class'],
                    'parent_id' => $parentId,
                    'is_header' => $account['is_header'] ?? false,
                    'is_active' => true,
                ]);
                $codeToId[$account['code']] = $created->id;
            }
        }
    }
}
