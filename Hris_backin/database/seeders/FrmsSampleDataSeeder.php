<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FRMS\Form;
use App\Models\FRMS\FinanceDisbursement;
use App\Models\FRMS\SummaryOfLiquidatedExpense;
use App\Models\Core\User;
use App\Models\Core\CoreBranch;
use Carbon\Carbon;

class FrmsSampleDataSeeder extends Seeder
{
    public function run()
    {
        // Get existing users and branches
        $users = User::all();
        $branches = CoreBranch::all();

        if ($users->isEmpty() || $branches->isEmpty()) {
            $this->command->error('No users or branches found. Please seed core data first.');
            return;
        }

        // Create sample FRMS forms
        $forms = [];
        for ($i = 1; $i <= 10; $i++) {
            $user = $users->random();
            $form = Form::create([
                'frm_no' => 'FRM-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'user_id' => $user->id,
                'branch_id' => $user->branch_id ?? $branches->random()->id,
                'request_date' => Carbon::now()->subDays(rand(1, 30)),
                'expectedliquidation_date' => Carbon::now()->addDays(rand(7, 30)),
                'status_request' => collect(['P', 'O', 'A', 'C'])->random(),
            ]);
            $forms[] = $form;
        }

        // Create sample Finance Disbursements
        foreach ($forms as $index => $form) {
            FinanceDisbursement::create([
                'ref_num' => 'FD-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'debit' => 'DEBIT-' . ($index + 1),
                'location' => $branches->random()->branch_name,
                'form_id' => $form->id,
                'transaction_type' => rand(1, 5),
                'description' => 'Sample disbursement for ' . $form->frm_no,
                'frequency' => rand(1, 3),
                'expected_liquidation' => $form->expectedliquidation_date,
                'actual_liquidation' => rand(0, 1) ? Carbon::now()->subDays(rand(1, 10)) : null,
                'liquidation_report_mj' => 'MJ-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'original_receipts' => rand(0, 1),
                'amount' => rand(5000, 50000),
                'difference' => rand(-1000, 1000),
                'remarks' => 'Sample remarks for disbursement ' . ($index + 1),
                'status' => collect(['P', 'O', 'A', 'C'])->random(),
                'created_by' => $form->user_id,
                'updated_by' => $form->user_id,
            ]);
        }

        // Create sample Liquidation Expenses
        foreach ($forms as $index => $form) {
            for ($j = 1; $j <= rand(1, 3); $j++) {
                SummaryOfLiquidatedExpense::create([
                    'ref_num' => $form->frm_no,
                    'date' => Carbon::now()->subDays(rand(1, 20)),
                    'or_no' => 'OR-' . str_pad(($index * 3) + $j, 6, '0', STR_PAD_LEFT),
                    'plate_no' => 'ABC-' . rand(1000, 9999),
                    'account_code_title' => 'Account Code ' . rand(1000, 9999),
                    'particulars' => 'Sample expense particulars for ' . $form->frm_no . ' item ' . $j,
                    'supplier_name' => 'Supplier ' . ($index + 1),
                    'tin' => rand(100000000, 999999999),
                    'address' => 'Sample Address ' . ($index + 1),
                    'location_client' => $branches->random()->branch_name,
                    'gross_amount' => rand(1000, 10000),
                    'vat_non_vat' => rand(1, 2),
                    'expense_amount' => rand(800, 9000),
                    'input_vat' => rand(100, 1000),
                    'accountable_person' => $form->user_id,
                    'validated_by_accounting' => rand(0, 1),
                    'manual_journal_no' => 'MJ-' . str_pad(($index * 3) + $j, 6, '0', STR_PAD_LEFT),
                    'code' => 'CODE-' . rand(100, 999),
                    'status' => collect(['P', 'O', 'A', 'C'])->random(),
                ]);
            }
        }

        $this->command->info('Sample FRMS data created successfully!');
        $this->command->info('Created: ' . count($forms) . ' forms, ' . count($forms) . ' disbursements, and multiple liquidation expenses');
    }
}