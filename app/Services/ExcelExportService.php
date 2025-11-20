<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetType;
use App\Models\LoanLog;
use App\Models\Employee;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ExcelExportService
{
    /**
     * Generate Complete ICT HoD Dashboard Report
     */
    public function generateICTDashboardReport()
    {
        $spreadsheet = new Spreadsheet();
        
        // Remove default sheet
        $spreadsheet->removeSheetByIndex(0);
        
        // Create all sheets
        $this->createExecutiveSummary($spreadsheet);
        $this->createAssetInventory($spreadsheet);
        $this->createAssetByDepartment($spreadsheet);
        $this->createAssetByLocation($spreadsheet);
        $this->createLoanHistory($spreadsheet);
        $this->createActiveLoans($spreadsheet);
        $this->createOverdueLoans($spreadsheet);
        $this->createDamageReports($spreadsheet);
        $this->createAssetUtilization($spreadsheet);
        $this->createCostCenterAnalysis($spreadsheet);
        $this->createBrandAnalysis($spreadsheet);
        $this->createAssetAging($spreadsheet);
        
        // Set active sheet to first
        $spreadsheet->setActiveSheetIndex(0);
        
        // Generate filename
        $filename = 'ICT_Dashboard_Report_' . now()->format('Y-m-d_His') . '.xlsx';
        
        // Save to storage
        $writer = new Xlsx($spreadsheet);
        $path = storage_path('app/public/reports/' . $filename);
        
        // Create directory if not exists
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }
        
        $writer->save($path);
        
        return $filename;
    }

    /**
     * Sheet 1: Executive Summary
     */
    private function createExecutiveSummary($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Executive Summary');
        
        // Header
        $sheet->setCellValue('A1', 'ICT ASSET MANAGEMENT - EXECUTIVE SUMMARY');
        $sheet->mergeCells('A1:F1');
        $this->styleHeader($sheet, 'A1:F1');
        
        $sheet->setCellValue('A2', 'Report Generated: ' . now()->format('d M Y H:i'));
        $sheet->mergeCells('A2:F2');
        
        // Key Metrics
        $row = 4;
        $sheet->setCellValue('A' . $row, 'KEY METRICS');
        $sheet->mergeCells('A' . $row . ':B' . $row);
        $this->styleSectionHeader($sheet, 'A' . $row . ':B' . $row);
        
        $metrics = [
            ['Total Assets', Asset::count()],
            ['In Use', Asset::where('status', 'In Use')->count()],
            ['In Stock', Asset::where('status', 'In Stock')->count()],
            ['Broken', Asset::where('status', 'Broken')->count()],
            ['Retired', Asset::where('status', 'Retired')->count()],
            ['Active Loans', LoanLog::where('status', 'On Loan')->count()],
            ['Total Employees with Assets', Asset::whereNotNull('assigned_to')->distinct('assigned_to')->count()],
            ['Asset Types', AssetType::count()],
        ];
        
        $row++;
        foreach ($metrics as $metric) {
            $sheet->setCellValue('A' . $row, $metric[0]);
            $sheet->setCellValue('B' . $row, $metric[1]);
            $row++;
        }
        
        // Utilization Rate
        $totalAssets = Asset::count();
        $inUse = Asset::where('status', 'In Use')->count();
        $utilization = $totalAssets > 0 ? round(($inUse / $totalAssets) * 100, 2) : 0;
        
        $row++;
        $sheet->setCellValue('A' . $row, 'Utilization Rate');
        $sheet->setCellValue('B' . $row, $utilization . '%');
        $this->styleHighlight($sheet, 'A' . $row . ':B' . $row);
        
        // Top 10 Asset Types
        $row += 2;
        $sheet->setCellValue('A' . $row, 'TOP 10 ASSET TYPES BY COUNT');
        $sheet->mergeCells('A' . $row . ':D' . $row);
        $this->styleSectionHeader($sheet, 'A' . $row . ':D' . $row);
        
        $row++;
        $sheet->setCellValue('A' . $row, 'Asset Type');
        $sheet->setCellValue('B' . $row, 'Category');
        $sheet->setCellValue('C' . $row, 'Total');
        $sheet->setCellValue('D' . $row, 'In Use');
        $this->styleTableHeader($sheet, 'A' . $row . ':D' . $row);
        
        $topAssets = Asset::select('asset_type_id', DB::raw('count(*) as total'), DB::raw('SUM(CASE WHEN status = "In Use" THEN 1 ELSE 0 END) as in_use'))
            ->with('assetType')
            ->groupBy('asset_type_id')
            ->orderByDesc('total')
            ->take(10)
            ->get();
        
        $row++;
        foreach ($topAssets as $asset) {
            $sheet->setCellValue('A' . $row, $asset->assetType->name ?? 'Unknown');
            $sheet->setCellValue('B' . $row, $asset->assetType->category ?? 'N/A');
            $sheet->setCellValue('C' . $row, $asset->total);
            $sheet->setCellValue('D' . $row, $asset->in_use);
            $row++;
        }
        
        $this->autoSizeColumns($sheet, 'A', 'F');
    }

    /**
     * Sheet 2: Asset Inventory (Complete List)
     */
    private function createAssetInventory($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Asset Inventory');
        
        // Header
        $headers = [
            'A1' => 'Code',
            'B1' => 'PO Ref',
            'C1' => 'PR Ref',
            'D1' => 'Item Name',
            'E1' => 'Asset Type',
            'F1' => 'Category',
            'G1' => 'Brand',
            'H1' => 'Type',
            'I1' => 'Serial Number',
            'J1' => 'Service Tag',
            'K1' => 'Status',
            'L1' => 'Assigned To',
            'M1' => 'Employee ID',
            'N1' => 'Department',
            'O1' => 'Location',
            'P1' => 'Location Site',
            'Q1' => 'Cost Center',
            'R1' => 'Delivery Date',
            'S1' => 'SOC Compliant',
            'T1' => 'Memory',
            'U1' => 'Specifications'
        ];
        
        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }
        $this->styleTableHeader($sheet, 'A1:U1');
        
        // Data
        $assets = Asset::with(['assetType', 'assignedEmployee'])->get();
        $row = 2;
        
        foreach ($assets as $asset) {
            $sheet->setCellValue('A' . $row, $asset->code);
            $sheet->setCellValue('B' . $row, $asset->po_ref);
            $sheet->setCellValue('C' . $row, $asset->pr_ref);
            $sheet->setCellValue('D' . $row, $asset->item_name);
            $sheet->setCellValue('E' . $row, $asset->assetType->name ?? 'N/A');
            $sheet->setCellValue('F' . $row, $asset->assetType->category ?? 'N/A');
            $sheet->setCellValue('G' . $row, $asset->brand);
            $sheet->setCellValue('H' . $row, $asset->type);
            $sheet->setCellValue('I' . $row, $asset->serial_number);
            $sheet->setCellValue('J' . $row, $asset->service_tag);
            $sheet->setCellValue('K' . $row, $asset->status);
            $sheet->setCellValue('L' . $row, $asset->assignedEmployee->name ?? '');
            $sheet->setCellValue('M' . $row, $asset->assignedEmployee->ghrs_id ?? $asset->ghrs_id);
            $sheet->setCellValue('N' . $row, $asset->assignedEmployee->department ?? $asset->dept_project);
            $sheet->setCellValue('O' . $row, $asset->location);
            $sheet->setCellValue('P' . $row, $asset->location_site);
            $sheet->setCellValue('Q' . $row, $asset->cost_center);
            $sheet->setCellValue('R' . $row, $asset->delivery_date ? Carbon::parse($asset->delivery_date)->format('d/m/Y') : '');
            $sheet->setCellValue('S' . $row, $asset->soc_compliant);
            $sheet->setCellValue('T' . $row, $asset->memory);
            $sheet->setCellValue('U' . $row, $asset->specifications);
            
            // Color code by status
            $this->colorCodeStatus($sheet, 'K' . $row, $asset->status);
            
            $row++;
        }
        
        // Auto filter
        $sheet->setAutoFilter('A1:U1');
        $this->autoSizeColumns($sheet, 'A', 'U');
    }

    /**
     * Sheet 3: Assets by Department
     */
    private function createAssetByDepartment($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('By Department');
        
        // Header
        $sheet->setCellValue('A1', 'Department');
        $sheet->setCellValue('B1', 'Total Assets');
        $sheet->setCellValue('C1', 'In Use');
        $sheet->setCellValue('D1', 'In Stock');
        $sheet->setCellValue('E1', 'Broken');
        $sheet->setCellValue('F1', 'Utilization %');
        $this->styleTableHeader($sheet, 'A1:F1');
        
        // Data from employees table
        $deptData = Asset::whereNotNull('dept_project')
            ->where('dept_project', '!=', '')
            ->select(
                'dept_project',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN status = "In Use" THEN 1 ELSE 0 END) as in_use'),
                DB::raw('SUM(CASE WHEN status = "In Stock" THEN 1 ELSE 0 END) as in_stock'),
                DB::raw('SUM(CASE WHEN status = "Broken" THEN 1 ELSE 0 END) as broken')
            )
            ->groupBy('dept_project')
            ->orderByDesc('total')
            ->get();
        
        $row = 2;
        foreach ($deptData as $dept) {
            $utilization = $dept->total > 0 ? round(($dept->in_use / $dept->total) * 100, 2) : 0;
            
            $sheet->setCellValue('A' . $row, $dept->dept_project);
            $sheet->setCellValue('B' . $row, $dept->total);
            $sheet->setCellValue('C' . $row, $dept->in_use);
            $sheet->setCellValue('D' . $row, $dept->in_stock);
            $sheet->setCellValue('E' . $row, $dept->broken);
            $sheet->setCellValue('F' . $row, $utilization . '%');
            $row++;
        }
        
        $this->autoSizeColumns($sheet, 'A', 'F');
    }

    /**
     * Sheet 4: Assets by Location
     */
    private function createAssetByLocation($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('By Location');
        
        $sheet->setCellValue('A1', 'Location');
        $sheet->setCellValue('B1', 'Location Site');
        $sheet->setCellValue('C1', 'Total Assets');
        $sheet->setCellValue('D1', 'In Use');
        $sheet->setCellValue('E1', 'In Stock');
        $this->styleTableHeader($sheet, 'A1:E1');
        
        $locationData = Asset::whereNotNull('location')
            ->select(
                'location',
                'location_site',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN status = "In Use" THEN 1 ELSE 0 END) as in_use'),
                DB::raw('SUM(CASE WHEN status = "In Stock" THEN 1 ELSE 0 END) as in_stock')
            )
            ->groupBy('location', 'location_site')
            ->orderBy('location')
            ->orderBy('location_site')
            ->get();
        
        $row = 2;
        foreach ($locationData as $loc) {
            $sheet->setCellValue('A' . $row, $loc->location);
            $sheet->setCellValue('B' . $row, $loc->location_site);
            $sheet->setCellValue('C' . $row, $loc->total);
            $sheet->setCellValue('D' . $row, $loc->in_use);
            $sheet->setCellValue('E' . $row, $loc->in_stock);
            $row++;
        }
        
        $this->autoSizeColumns($sheet, 'A', 'E');
    }

    /**
     * Sheet 5: Loan History (All Loans)
     */
    private function createLoanHistory($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Loan History');
        
        $sheet->setCellValue('A1', 'Loan Date');
        $sheet->setCellValue('B1', 'Return Date');
        $sheet->setCellValue('C1', 'Asset Type');
        $sheet->setCellValue('D1', 'Borrower Name');
        $sheet->setCellValue('E1', 'Employee ID');
        $sheet->setCellValue('F1', 'Department');
        $sheet->setCellValue('G1', 'Duration (Days)');
        $sheet->setCellValue('H1', 'Quantity');
        $sheet->setCellValue('I1', 'Status');
        $sheet->setCellValue('J1', 'Reason');
        $this->styleTableHeader($sheet, 'A1:J1');
        
        $loans = LoanLog::with(['borrower', 'assetType'])
            ->orderBy('loan_date', 'desc')
            ->get();
        
        $row = 2;
        foreach ($loans as $loan) {
            $sheet->setCellValue('A' . $row, Carbon::parse($loan->loan_date)->format('d/m/Y'));
            $sheet->setCellValue('B' . $row, $loan->return_date ? Carbon::parse($loan->return_date)->format('d/m/Y') : '');
            $sheet->setCellValue('C' . $row, $loan->assetType->name ?? $loan->asset_type_name);
            $sheet->setCellValue('D' . $row, $loan->borrower->name ?? 'N/A');
            $sheet->setCellValue('E' . $row, $loan->borrower->ghrs_id ?? 'N/A');
            $sheet->setCellValue('F' . $row, $loan->borrower->department ?? 'N/A');
            $sheet->setCellValue('G' . $row, $loan->duration_days);
            $sheet->setCellValue('H' . $row, $loan->quantity);
            $sheet->setCellValue('I' . $row, $loan->status);
            $sheet->setCellValue('J' . $row, $loan->reason);
            
            // Highlight overdue
            if ($loan->status === 'On Loan') {
                $expectedReturn = Carbon::parse($loan->loan_date)->addDays($loan->duration_days);
                if ($expectedReturn->isPast()) {
                    $this->styleHighlight($sheet, 'A' . $row . ':J' . $row, 'FFFF0000');
                }
            }
            
            $row++;
        }
        
        $this->autoSizeColumns($sheet, 'A', 'J');
    }

    /**
     * Sheet 6: Active Loans
     */
    private function createActiveLoans($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Active Loans');
        
        $sheet->setCellValue('A1', 'Loan Date');
        $sheet->setCellValue('B1', 'Expected Return');
        $sheet->setCellValue('C1', 'Days Remaining');
        $sheet->setCellValue('D1', 'Asset Type');
        $sheet->setCellValue('E1', 'Borrower');
        $sheet->setCellValue('F1', 'Employee ID');
        $sheet->setCellValue('G1', 'Department');
        $sheet->setCellValue('H1', 'Quantity');
        $this->styleTableHeader($sheet, 'A1:H1');
        
        $activeLoans = LoanLog::where('status', 'On Loan')
            ->with(['borrower', 'assetType'])
            ->orderBy('loan_date')
            ->get();
        
        $row = 2;
        foreach ($activeLoans as $loan) {
            $loanDate = Carbon::parse($loan->loan_date);
            $expectedReturn = $loanDate->copy()->addDays($loan->duration_days);
            $daysRemaining = now()->diffInDays($expectedReturn, false);
            
            $sheet->setCellValue('A' . $row, $loanDate->format('d/m/Y'));
            $sheet->setCellValue('B' . $row, $expectedReturn->format('d/m/Y'));
            $sheet->setCellValue('C' . $row, $daysRemaining);
            $sheet->setCellValue('D' . $row, $loan->assetType->name ?? $loan->asset_type_name);
            $sheet->setCellValue('E' . $row, $loan->borrower->name ?? 'N/A');
            $sheet->setCellValue('F' . $row, $loan->borrower->ghrs_id ?? 'N/A');
            $sheet->setCellValue('G' . $row, $loan->borrower->department ?? 'N/A');
            $sheet->setCellValue('H' . $row, $loan->quantity);
            
            // Color code by days remaining
            if ($daysRemaining < 0) {
                $this->styleHighlight($sheet, 'C' . $row, 'FFFF0000'); // Red for overdue
            } elseif ($daysRemaining <= 3) {
                $this->styleHighlight($sheet, 'C' . $row, 'FFFFA500'); // Orange for soon
            }
            
            $row++;
        }
        
        $this->autoSizeColumns($sheet, 'A', 'H');
    }

    /**
     * Sheet 7: Overdue Loans
     */
    private function createOverdueLoans($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Overdue Loans');
        
        $sheet->setCellValue('A1', 'Loan Date');
        $sheet->setCellValue('B1', 'Expected Return');
        $sheet->setCellValue('C1', 'Days Overdue');
        $sheet->setCellValue('D1', 'Asset Type');
        $sheet->setCellValue('E1', 'Borrower');
        $sheet->setCellValue('F1', 'Employee ID');
        $sheet->setCellValue('G1', 'Department');
        $sheet->setCellValue('H1', 'Contact');
        $this->styleTableHeader($sheet, 'A1:H1');
        $this->styleHighlight($sheet, 'A1:H1', 'FFFF0000');
        
        $overdueLoans = LoanLog::where('status', 'On Loan')
            ->with(['borrower', 'assetType'])
            ->get()
            ->filter(function($loan) {
                $expectedReturn = Carbon::parse($loan->loan_date)->addDays($loan->duration_days);
                return $expectedReturn->isPast();
            })
            ->sortBy(function($loan) {
                return Carbon::parse($loan->loan_date)->addDays($loan->duration_days);
            });
        
        $row = 2;
        foreach ($overdueLoans as $loan) {
            $loanDate = Carbon::parse($loan->loan_date);
            $expectedReturn = $loanDate->copy()->addDays($loan->duration_days);
            $daysOverdue = now()->diffInDays($expectedReturn);
            
            $sheet->setCellValue('A' . $row, $loanDate->format('d/m/Y'));
            $sheet->setCellValue('B' . $row, $expectedReturn->format('d/m/Y'));
            $sheet->setCellValue('C' . $row, $daysOverdue);
            $sheet->setCellValue('D' . $row, $loan->assetType->name ?? $loan->asset_type_name);
            $sheet->setCellValue('E' . $row, $loan->borrower->name ?? 'N/A');
            $sheet->setCellValue('F' . $row, $loan->borrower->ghrs_id ?? 'N/A');
            $sheet->setCellValue('G' . $row, $loan->borrower->department ?? 'N/A');
            $sheet->setCellValue('H' . $row, $loan->borrower->email ?? 'N/A');
            
            $this->styleHighlight($sheet, 'A' . $row . ':H' . $row, 'FFFFC0C0');
            
            $row++;
        }
        
        $this->autoSizeColumns($sheet, 'A', 'H');
    }

    /**
     * Sheet 8: Damage Reports
     */
    private function createDamageReports($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Damage Reports');
        
        $sheet->setCellValue('A1', 'Date');
        $sheet->setCellValue('B1', 'Asset Type');
        $sheet->setCellValue('C1', 'Employee');
        $sheet->setCellValue('D1', 'Employee ID');
        $sheet->setCellValue('E1', 'Department');
        $sheet->setCellValue('F1', 'Quantity');
        $sheet->setCellValue('G1', 'Reason');
        $this->styleTableHeader($sheet, 'A1:G1');
        
        $withdrawals = Withdrawal::with(['employee', 'assetType'])
            ->orderBy('date', 'desc')
            ->get();
        
        $row = 2;
        foreach ($withdrawals as $withdrawal) {
            $sheet->setCellValue('A' . $row, Carbon::parse($withdrawal->date)->format('d/m/Y'));
            $sheet->setCellValue('B' . $row, $withdrawal->assetType->name ?? 'N/A');
            $sheet->setCellValue('C' . $row, $withdrawal->employee->name ?? 'N/A');
            $sheet->setCellValue('D' . $row, $withdrawal->employee->ghrs_id ?? 'N/A');
            $sheet->setCellValue('E' . $row, $withdrawal->employee->department ?? 'N/A');
            $sheet->setCellValue('F' . $row, $withdrawal->quantity);
            $sheet->setCellValue('G' . $row, $withdrawal->reason);
            $row++;
        }
        
        $this->autoSizeColumns($sheet, 'A', 'G');
    }

    /**
     * Sheet 9: Asset Utilization Analysis
     */
    private function createAssetUtilization($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Utilization Analysis');
        
        $sheet->setCellValue('A1', 'Asset Type');
        $sheet->setCellValue('B1', 'Category');
        $sheet->setCellValue('C1', 'Total');
        $sheet->setCellValue('D1', 'In Use');
        $sheet->setCellValue('E1', 'In Stock');
        $sheet->setCellValue('F1', 'Utilization %');
        $sheet->setCellValue('G1', 'Status');
        $this->styleTableHeader($sheet, 'A1:G1');
        
        $utilization = Asset::select(
                'asset_type_id',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN status = "In Use" THEN 1 ELSE 0 END) as in_use'),
                DB::raw('SUM(CASE WHEN status = "In Stock" THEN 1 ELSE 0 END) as in_stock')
            )
            ->with('assetType')
            ->groupBy('asset_type_id')
            ->get();
        
        $row = 2;
        foreach ($utilization as $item) {
            $utilizationRate = $item->total > 0 ? round(($item->in_use / $item->total) * 100, 2) : 0;
            
            // Determine status
            $status = 'Good';
            if ($utilizationRate < 30) $status = 'Low';
            elseif ($utilizationRate > 80) $status = 'High';
            
            $sheet->setCellValue('A' . $row, $item->assetType->name ?? 'Unknown');
            $sheet->setCellValue('B' . $row, $item->assetType->category ?? 'N/A');
            $sheet->setCellValue('C' . $row, $item->total);
            $sheet->setCellValue('D' . $row, $item->in_use);
            $sheet->setCellValue('E' . $row, $item->in_stock);
            $sheet->setCellValue('F' . $row, $utilizationRate . '%');
            $sheet->setCellValue('G' . $row, $status);
            
            // Color code
            if ($status === 'Low') {
                $this->styleHighlight($sheet, 'F' . $row . ':G' . $row, 'FFFFA500');
            } elseif ($status === 'High') {
                $this->styleHighlight($sheet, 'F' . $row . ':G' . $row, 'FF90EE90');
            }
            
            $row++;
        }
        
        $this->autoSizeColumns($sheet, 'A', 'G');
    }

    /**
     * Sheet 10: Cost Center Analysis
     */
    private function createCostCenterAnalysis($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Cost Center Analysis');
        
        $sheet->setCellValue('A1', 'Cost Center');
        $sheet->setCellValue('B1', 'Total Assets');
        $sheet->setCellValue('C1', 'In Use');
        $sheet->setCellValue('D1', 'Damaged');
        $this->styleTableHeader($sheet, 'A1:D1');
        
        $costCenterData = Asset::whereNotNull('cost_center')
            ->select(
                'cost_center',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN status = "In Use" THEN 1 ELSE 0 END) as in_use'),
                DB::raw('SUM(CASE WHEN status = "Broken" THEN 1 ELSE 0 END) as damaged')
            )
            ->groupBy('cost_center')
            ->orderByDesc('total')
            ->get();
        
        $row = 2;
        foreach ($costCenterData as $cc) {
            $sheet->setCellValue('A' . $row, $cc->cost_center);
            $sheet->setCellValue('B' . $row, $cc->total);
            $sheet->setCellValue('C' . $row, $cc->in_use);
            $sheet->setCellValue('D' . $row, $cc->damaged);
            $row++;
        }
        
        $this->autoSizeColumns($sheet, 'A', 'D');
    }

    /**
     * Sheet 11: Brand Analysis
     */
    private function createBrandAnalysis($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Brand Analysis');
        
        $sheet->setCellValue('A1', 'Brand');
        $sheet->setCellValue('B1', 'Total Assets');
        $sheet->setCellValue('C1', 'Broken');
        $sheet->setCellValue('D1', 'Failure Rate %');
        $sheet->setCellValue('E1', 'Reliability Score');
        $this->styleTableHeader($sheet, 'A1:E1');
        
        $brandData = Asset::whereNotNull('brand')
            ->where('brand', '!=', '')
            ->select(
                'brand',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN status = "Broken" THEN 1 ELSE 0 END) as broken')
            )
            ->groupBy('brand')
            ->having('total', '>=', 3) // Only brands with 3+ assets
            ->orderByDesc('total')
            ->get();
        
        $row = 2;
        foreach ($brandData as $brand) {
            $failureRate = $brand->total > 0 ? round(($brand->broken / $brand->total) * 100, 2) : 0;
            $reliabilityScore = max(0, 100 - $failureRate);
            
            $sheet->setCellValue('A' . $row, $brand->brand);
            $sheet->setCellValue('B' . $row, $brand->total);
            $sheet->setCellValue('C' . $row, $brand->broken);
            $sheet->setCellValue('D' . $row, $failureRate . '%');
            $sheet->setCellValue('E' . $row, $reliabilityScore);
            
            // Color code reliability
            if ($reliabilityScore >= 90) {
                $this->styleHighlight($sheet, 'E' . $row, 'FF90EE90'); // Green
            } elseif ($reliabilityScore >= 70) {
                $this->styleHighlight($sheet, 'E' . $row, 'FFFFA500'); // Orange
            } else {
                $this->styleHighlight($sheet, 'E' . $row, 'FFFF0000'); // Red
            }
            
            $row++;
        }
        
        $this->autoSizeColumns($sheet, 'A', 'E');
    }

    /**
     * Sheet 12: Asset Aging Report
     */
    private function createAssetAging($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Asset Aging');
        
        $sheet->setCellValue('A1', 'Asset Type');
        $sheet->setCellValue('B1', '< 1 Year');
        $sheet->setCellValue('C1', '1-2 Years');
        $sheet->setCellValue('D1', '2-3 Years');
        $sheet->setCellValue('E1', '3+ Years');
        $sheet->setCellValue('F1', 'Avg Age (Months)');
        $sheet->setCellValue('G1', 'Replacement Priority');
        $this->styleTableHeader($sheet, 'A1:G1');
        
        $assetTypes = AssetType::all();
        
        $row = 2;
        foreach ($assetTypes as $type) {
            $assets = Asset::where('asset_type_id', $type->id)
                ->whereNotNull('delivery_date')
                ->get();
            
            if ($assets->count() === 0) continue;
            
            $lessThan1Year = $assets->filter(function($asset) {
                return Carbon::parse($asset->delivery_date)->diffInMonths(now()) < 12;
            })->count();
            
            $oneToTwoYears = $assets->filter(function($asset) {
                $months = Carbon::parse($asset->delivery_date)->diffInMonths(now());
                return $months >= 12 && $months < 24;
            })->count();
            
            $twoToThreeYears = $assets->filter(function($asset) {
                $months = Carbon::parse($asset->delivery_date)->diffInMonths(now());
                return $months >= 24 && $months < 36;
            })->count();
            
            $moreThan3Years = $assets->filter(function($asset) {
                return Carbon::parse($asset->delivery_date)->diffInMonths(now()) >= 36;
            })->count();
            
            $avgAge = round($assets->avg(function($asset) {
                return Carbon::parse($asset->delivery_date)->diffInMonths(now());
            }), 1);
            
            // Determine replacement priority
            $priority = 'Low';
            if ($avgAge >= 48) $priority = 'Critical';
            elseif ($avgAge >= 36) $priority = 'High';
            elseif ($avgAge >= 24) $priority = 'Medium';
            
            $sheet->setCellValue('A' . $row, $type->name);
            $sheet->setCellValue('B' . $row, $lessThan1Year);
            $sheet->setCellValue('C' . $row, $oneToTwoYears);
            $sheet->setCellValue('D' . $row, $twoToThreeYears);
            $sheet->setCellValue('E' . $row, $moreThan3Years);
            $sheet->setCellValue('F' . $row, $avgAge);
            $sheet->setCellValue('G' . $row, $priority);
            
            // Color code priority
            if ($priority === 'Critical') {
                $this->styleHighlight($sheet, 'G' . $row, 'FFFF0000');
            } elseif ($priority === 'High') {
                $this->styleHighlight($sheet, 'G' . $row, 'FFFFA500');
            } elseif ($priority === 'Medium') {
                $this->styleHighlight($sheet, 'G' . $row, 'FFFFFF00');
            }
            
            $row++;
        }
        
        $this->autoSizeColumns($sheet, 'A', 'G');
    }

    /**
     * Styling Helper Methods
     */
    private function styleHeader($sheet, $range)
    {
        $sheet->getStyle($range)->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0E4C92']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);
    }

    private function styleSectionHeader($sheet, $range)
    {
        $sheet->getStyle($range)->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1a5fa8']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);
    }

    private function styleTableHeader($sheet, $range)
    {
        $sheet->getStyle($range)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0E4C92']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ]);
    }

    private function styleHighlight($sheet, $range, $color = 'FFFFFF00')
    {
        $sheet->getStyle($range)->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => $color]
            ]
        ]);
    }

    private function colorCodeStatus($sheet, $cell, $status)
    {
        $colors = [
            'In Stock' => 'FFB3D9FF',
            'In Use' => 'FF90EE90',
            'Broken' => 'FFFF6B6B',
            'Retired' => 'FFD3D3D3',
            'Taken' => 'FFFFA500'
        ];
        
        if (isset($colors[$status])) {
            $this->styleHighlight($sheet, $cell, $colors[$status]);
        }
    }

    private function autoSizeColumns($sheet, $startCol, $endCol)
    {
        foreach (range($startCol, $endCol) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
}