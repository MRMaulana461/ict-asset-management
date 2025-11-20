<?php

namespace App\Exports;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class EmployeesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    protected $filters;
    protected $rowNumber = 1;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = Employee::query();

        // Apply filters
        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('ghrs_id', 'like', "%{$search}%")
                  ->orWhere('badge_id', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%")
                  ->orWhere('company', 'like', "%{$search}%");
            });
        }

        if (isset($this->filters['is_active']) && $this->filters['is_active'] !== '') {
            $query->where('is_active', $this->filters['is_active']);
        }

        if (!empty($this->filters['department'])) {
            $query->where('department', $this->filters['department']);
        }

        if (!empty($this->filters['company'])) {
            $query->where('company', $this->filters['company']);
        }

        return $query->orderBy('ghrs_id')->get();
    }

    /**
     * Define column headings
     */
    public function headings(): array
    {
        return [
            'Company',
            'Org. Context',
            'DeptID',
            'Org Relation',
            'Agency',
            'BoC',
            'Cost Center',
            'Cost Center Descr',
            'GHRS ID',
            'Badge_id',
            'Empl Rcd #',
            'Role Company',
            'Lastname',
            'First Name',
            'Empl Class',
            'Tipo Terzi',
            'Contractual Position',
            'SamAccountName',
            'EmailAddress',
            'is_active',
            'department',
            'name'
        ];
    }

    /**
     * Map data to columns
     */
    public function map($employee): array
    {
        $this->rowNumber++;
        
        return [
            $employee->company ?? '',
            $employee->org_context ?? '',
            $employee->dept_id ?? '',
            $employee->org_relation ?? '',
            $employee->agency ?? '',
            $employee->boc ?? '',
            $employee->cost_center ?? '',
            $employee->cost_center_descr ?? '',
            $employee->ghrs_id ?? '',
            $employee->badge_id ?? '',
            $employee->empl_rcd ?? '',
            $employee->role_company ?? '',
            $employee->last_name ?? '',
            $employee->first_name ?? '',
            $employee->employee_class ?? '',
            $employee->tipo_terzi ?? '',
            $employee->contractual_position ?? '',
            $employee->user_id ?? '',
            $employee->email ?? '',
            $employee->is_active ? 'Active' : 'Inactive',
            $employee->department ?? '',
            $employee->name ?? ''
        ];
    }

    /**
     * Apply styles to the worksheet
     */
    public function styles(Worksheet $sheet)
    {
        // Header styling
        $sheet->getStyle('A1:V1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ]);

        $sheet->getRowDimension(1)->setRowHeight(25);

        $lastRow = $this->rowNumber;
        if ($lastRow > 1) {
            // Apply borders
            $sheet->getStyle("A1:V{$lastRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'D3D3D3']
                    ]
                ]
            ]);

            // Alternate row colors
            for ($i = 2; $i <= $lastRow; $i++) {
                if ($i % 2 == 0) {
                    $sheet->getStyle("A{$i}:V{$i}")->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'F2F2F2']
                        ]
                    ]);
                }
            }

            // Center align specific columns
            $sheet->getStyle("I2:K{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // GHRS, Badge, Empl Rcd
            $sheet->getStyle("T2:T{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // is_active

            // Text wrap for long text columns
            $sheet->getStyle("R2:R{$lastRow}")->getAlignment()->setWrapText(true); // SamAccountName
            $sheet->getStyle("S2:S{$lastRow}")->getAlignment()->setWrapText(true); // EmailAddress
            $sheet->getStyle("V2:V{$lastRow}")->getAlignment()->setWrapText(true); // name
        }

        $sheet->freezePane('A2');

        return [];
    }

    /**
     * Define column widths
     */
    public function columnWidths(): array
    {
        return [
            'A' => 18,  // Company
            'B' => 15,  // Org. Context
            'C' => 10,  // DeptID
            'D' => 15,  // Org Relation
            'E' => 15,  // Agency
            'F' => 10,  // BoC
            'G' => 12,  // Cost Center
            'H' => 20,  // Cost Center Descr
            'I' => 12,  // GHRS ID
            'J' => 12,  // Badge_id
            'K' => 12,  // Empl Rcd #
            'L' => 15,  // Role Company
            'M' => 18,  // Lastname
            'N' => 18,  // First Name
            'O' => 12,  // Empl Class
            'P' => 12,  // Tipo Terzi
            'Q' => 20,  // Contractual Position
            'R' => 20,  // SamAccountName
            'S' => 25,  // EmailAddress
            'T' => 10,  // is_active
            'U' => 20,  // department
            'V' => 25,  // name
        ];
    }

    /**
     * Sheet title
     */
    public function title(): string
    {
        return 'Employees';
    }
}