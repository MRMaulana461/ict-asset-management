<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class AssetImportController extends Controller
{
    public function showForm()
    {
        return view('assets.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240'
        ]);

        try {
            $import = new \App\Imports\AssetsImport;
            \Maatwebsite\Excel\Facades\Excel::import($import, $request->file('file'));

            $created = $import->getCreatedCount();
            $updated = $import->getUpdatedCount();
            $skipped = $import->getSkippedCount();
            $employeesCreated = $import->getEmployeesCreatedCount();

            $message = "Import completed! ";
            $details = [];
            
            if ($created > 0) $details[] = "{$created} assets created";
            if ($updated > 0) $details[] = "{$updated} assets updated";
            if ($employeesCreated > 0) $details[] = "{$employeesCreated} employees auto-created";
            if ($skipped > 0) $details[] = "{$skipped} rows skipped";
            
            $message .= implode(', ', $details);

            return redirect()->route('assets.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            return back()->with('error', 'Import error: ' . $e->getMessage());
        }
    }

    /**
     * Generate and download Excel import template with all required columns
     */
    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Assets Import');

        // HEADER - Row 1
        $headers = [
            'A1' => 'asset_tag',           // REQUIRED
            'B1' => 'serial_number',       // Optional
            'C1' => 'asset_type',          // REQUIRED (type name, e.g.: "Laptop")
            'D1' => 'employee_id',         // Optional (ID of assigned employee)
            'E1' => 'employee_name',       // Optional (auto-created if not found)
            'F1' => 'employee_email',      // Optional
            'G1' => 'sam_account_name',    // Optional
            'H1' => 'department',          // Optional
            'I1' => 'cost_center',         // Optional
            'J1' => 'status',              // Optional (In Stock/In Use/Broken/Retired/Taken)
            'K1' => 'assignment_date',     // Optional (format: YYYY-MM-DD)
            'L1' => 'last_status_date',    // Optional (format: YYYY-MM-DD)
            'M1' => 'notes',               // Optional
        ];

        // Apply headers and styling
        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
            
            $sheet->getStyle($cell)->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 11
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '0066CC']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ]);
        }

        // SAMPLE DATA - Row 2 & 3
        $exampleData = [
            ['LAP001', 'SN123456', 'Laptop', 'EMP001', 'John Doe', 'john.doe@company.com', 'johndoe', 'IT', 'CC001', 'In Use', '2025-01-15', '2025-01-15', 'Dell Latitude 5420'],
            ['MOU001', 'SN789012', 'Mouse', 'EMP002', 'Jane Smith', 'jane.smith@company.com', 'janesmith', 'Finance', 'CC002', 'In Stock', '', '2025-01-10', 'Logitech MX Master'],
        ];

        $row = 2;
        foreach ($exampleData as $data) {
            $col = 'A';
            foreach ($data as $value) {
                $sheet->setCellValue($col . $row, $value);
                $col++;
            }
            $row++;
        }

        // INSTRUCTION SHEET
        $instructionSheet = $spreadsheet->createSheet();
        $instructionSheet->setTitle('Instructions');
        
        $instructions = [
            ['ASSET IMPORT TEMPLATE - INSTRUCTIONS'],
            [''],
            ['REQUIRED:'],
            ['1. asset_tag', '=> Unique identifier for the asset (e.g.: LAP001, MOU001)'],
            ['2. asset_type', '=> Asset type name already existing in the database (e.g.: Laptop, Mouse, Monitor)'],
            [''],
            ['OPTIONAL:'],
            ['3. serial_number', '=> Serial number (if available)'],
            ['4. employee_id', '=> ID of the employee assigned to this asset'],
            ['5. employee_name', '=> Employee name (auto-created if ID not found)'],
            ['6. employee_email', '=> Employee email address'],
            ['7. sam_account_name', '=> SAM Account Name (for Active Directory integration)'],
            ['8. department', '=> Employee’s department'],
            ['9. cost_center', '=> Employee’s cost center'],
            ['10. status', '=> Asset status: In Stock, In Use, Broken, Retired, Taken (default: In Stock)'],
            ['11. assignment_date', '=> Date asset was assigned (format: YYYY-MM-DD or DD/MM/YYYY)'],
            ['12. last_status_date', '=> Last status update date (format: YYYY-MM-DD)'],
            ['13. notes', '=> Any additional notes or comments'],
            [''],
            ['IMPORTANT NOTES:'],
            ['• asset_tag MUST be unique'],
            ['• If asset_tag already exists, the record will be UPDATED'],
            ['• If employee_id does not exist and employee_name is provided, a new employee record will be auto-created'],
            ['• Valid status values: In Stock, In Use, Broken, Retired, Taken'],
            ['• Empty columns will be ignored (defaults will be used)'],
            ['• Invalid rows will be skipped automatically'],
        ];

        $row = 1;
        foreach ($instructions as $instruction) {
            $col = 'A';
            foreach ($instruction as $text) {
                $instructionSheet->setCellValue($col . $row, $text);
                $col++;
            }
            
            // Highlight section headers
            if ($row == 1 || in_array($row, [3, 7, 19])) {
                $instructionSheet->getStyle('A' . $row)->getFont()->setBold(true);
                $instructionSheet->getStyle('A' . $row)->getFont()->setSize(12);
            }
            
            $row++;
        }

        // Auto-size columns
        $spreadsheet->setActiveSheetIndex(0);
        foreach (range('A', 'M') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        foreach (range('A', 'B') as $col) {
            $instructionSheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Output download
        $fileName = 'asset_import_template_' . date('Y-m-d') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }
}
