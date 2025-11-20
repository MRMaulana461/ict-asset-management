<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

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
            $assetTypesCreated = $import->getAssetTypesCreatedCount();

            $message = "✅ Import completed! ";
            $details = [];
            
            if ($created > 0) $details[] = "{$created} assets created";
            if ($updated > 0) $details[] = "{$updated} assets updated";
            if ($assetTypesCreated > 0) $details[] = "{$assetTypesCreated} asset types auto-created";
            if ($skipped > 0) $details[] = "{$skipped} rows skipped";
            
            $message .= implode(', ', $details);

            return redirect()->route('assets.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            \Log::error('Import failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Import error: ' . $e->getMessage());
        }
    }

    /**
     * Generate Excel template - MATCH EXACT HEADERS
     */
    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Assets Import');

        // HEADER - EXACT MATCH dengan Excel Anda
        $headers = [
            'A1' => 'pr_ref',
            'B1' => 'po_ref',
            'C1' => 'item',
            'D1' => 'brand',
            'E1' => 'type',
            'F1' => 'serial_number',
            'G1' => 'ghrs_id',
            'H1' => 'badge_id',
            'I1' => 'assignment_date',
            'J1' => 'location',
            'K1' => 'remarks',
            'L1' => 'dept/project',
            'M1' => 'delivery_date',
            'N1' => 'status',
            'O1' => 'service_tag',
            'P1' => 'username',
            'Q1' => 'device_name',
            'R1' => 'serial_clean',
        ];

        // Apply headers with styling
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
            [
                'PR001',           // pr_ref
                'PO001',           // po_ref
                'Dell Latitude 5420', // item
                'Dell',            // brand
                'Laptop',          // type
                'SN123456',        // serial_number
                '118154',          // ghrs_id
                '',                // badge_id
                '2025-01-15',      // assignment_date
                'Main Office 2F',  // location
                'Good condition',  // remarks
                'IT Department',   // dept/project
                '2025-01-10',      // delivery_date
                'In Use',          // status
                '',                // service_tag
                'John Doe',        // username
                'LAPTOP-001',      // device_name
                'SN123456'         // serial_clean
            ],
            [
                'PR002',           // pr_ref
                'PO002',           // po_ref
                'Logitech MX Master 3', // item
                'Logitech',        // brand
                'Mouse',           // type
                'SN789012',        // serial_number
                '',                // ghrs_id
                '',                // badge_id
                '',                // assignment_date
                'Warehouse',       // location
                'New stock',       // remarks
                '',                // dept/project
                '2025-01-20',      // delivery_date
                'In Stock',        // status
                '',                // service_tag
                '',                // username
                '',                // device_name
                'SN789012'         // serial_clean
            ],
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
            ['🤖 SMART ASSET IMPORT - INSTRUCTIONS'],
            [''],
            ['EXACT COLUMN HEADERS (DO NOT CHANGE):'],
            ['pr_ref | po_ref | item | brand | type | serial_number | ghrs_id | badge_id'],
            ['assignment_date | location | remarks | dept/project | delivery_date | status'],
            ['service_tag | username | device_name | serial_clean'],
            [''],
            ['COLUMN DETAILS:'],
            ['• pr_ref: Purchase Request number'],
            ['• po_ref: Purchase Order number'],
            ['• item: Product name (e.g., "Dell Latitude 5420")'],
            ['• brand: Manufacturer (e.g., "Dell", "HP", "Logitech")'],
            ['• type: Item type (e.g., "Laptop", "Monitor", "Mouse")'],
            ['• serial_number: Asset serial number'],
            ['• ghrs_id: Employee GHRS ID (for assignment)'],
            ['• badge_id: Employee Badge ID (for assignment)'],
            ['• assignment_date: Format YYYY-MM-DD or DD/MM/YYYY'],
            ['• location: Physical location'],
            ['• remarks: Additional notes'],
            ['• dept/project: Department or Project name'],
            ['• delivery_date: Format YYYY-MM-DD or DD/MM/YYYY'],
            ['• status: In Stock / In Use / Broken / Retired / Taken'],
            ['• service_tag: Service tag (optional)'],
            ['• username: Employee name (for assignment)'],
            ['• device_name: Computer/device hostname'],
            ['• serial_clean: Cleaned serial (auto-generated if empty)'],
            [''],
            ['🎯 SMART FEATURES:'],
            ['✓ Auto-detect asset type from "item" and "type" columns'],
            ['✓ Auto-create asset types if not in database'],
            ['✓ Auto-generate asset_tag (LAP-0001, MON-0002, etc.)'],
            ['✓ Auto-assign to employee if ghrs_id/badge_id/username found'],
            ['✓ Auto-set status based on assignment'],
            ['✓ Skip duplicate asset tags'],
            ['✓ Skip empty rows automatically'],
            [''],
            ['💡 EMPLOYEE ASSIGNMENT:'],
            ['Priority: ghrs_id > badge_id > username'],
            ['If employee found → Status auto-set to "In Use"'],
            ['If employee NOT found → Status defaults to "In Stock"'],
            [''],
            ['📋 EXAMPLES:'],
            ['✅ GOOD: All main fields filled'],
            ['   item: "HP EliteBook 840 G8"'],
            ['   brand: "HP"'],
            ['   type: "Laptop"'],
            ['   ghrs_id: 118154'],
            [''],
            ['✅ GOOD: Minimal (item only)'],
            ['   item: "Dell Monitor 24 inch"'],
            ['   → Will auto-detect type "Monitor"'],
            [''],
            ['❌ BAD: Empty item'],
            ['   (Will be skipped)'],
        ];

        $row = 1;
        foreach ($instructions as $instruction) {
            $col = 'A';
            foreach ($instruction as $text) {
                $instructionSheet->setCellValue($col . $row, $text);
                $col++;
            }
            
            // Highlight headers
            if ($row == 1 || in_array($row, [3, 8, 28, 33, 38])) {
                $instructionSheet->getStyle('A' . $row)->getFont()->setBold(true);
                $instructionSheet->getStyle('A' . $row)->getFont()->setSize(12);
            }
            
            $row++;
        }

        // Auto-size columns
        $spreadsheet->setActiveSheetIndex(0);
        foreach (range('A', 'R') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        foreach (range('A', 'B') as $col) {
            $instructionSheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Download
        $fileName = 'asset_import_template_' . date('Ymd') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }
}