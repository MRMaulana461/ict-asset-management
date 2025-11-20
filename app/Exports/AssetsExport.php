<?php

namespace App\Exports;

use App\Models\Asset;
use App\Models\AssetType;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class AssetsExport implements WithMultipleSheets
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * Return multiple sheets berdasarkan asset type
     */
    public function sheets(): array
    {
        $sheets = [];

        // Jika filter asset_type_id dipilih (bukan "All")
        if (!empty($this->filters['asset_type_id'])) {
            $assetType = AssetType::find($this->filters['asset_type_id']);
            $sheets[] = new AssetTypeSheet($this->filters, $assetType);
        } else {
            // Jika "All" - pisahkan per asset type
            $assetTypes = AssetType::whereHas('assets')->orderBy('name')->get();
            
            foreach ($assetTypes as $assetType) {
                $sheets[] = new AssetTypeSheet($this->filters, $assetType);
            }
        }

        return $sheets;
    }
}

/**
 * Class untuk single sheet per asset type
 */
class AssetTypeSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    protected $filters;
    protected $assetType;
    protected $rowNumber = 1;

    public function __construct($filters, $assetType)
    {
        $this->filters = $filters;
        $this->assetType = $assetType;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = Asset::with(['assetType', 'assignedEmployee'])
            ->where('asset_type_id', $this->assetType->id);

        // Apply other filters
        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['assigned_to'])) {
            $query->where('assigned_to', $this->filters['assigned_to']);
        }

        if (!empty($this->filters['start_date']) && !empty($this->filters['end_date'])) {
            $query->whereBetween('delivery_date', [
                $this->filters['start_date'],
                $this->filters['end_date']
            ]);
        }

        return $query->latest('delivery_date')
                    ->latest('created_at')
                    ->get();
    }

    /**
     * Define column headings
     */
    public function headings(): array
    {
        return [
            'pr_ref',
            'po_ref',
            'item',
            'brand',
            'type',
            'serial_number',
            'ghrs_id',
            'badge_id',
            'assignment_date',
            'location',
            'remarks',
            'dept/project',
            'delivery_date',
            'status',
            'service_tag',
            'username',
            'device_name',
            'specifications'
        ];
    }

    /**
     * Map data to columns
     */
    public function map($asset): array
    {
        $this->rowNumber++;
        
        return [
            $asset->pr_ref ?? '',
            $asset->po_ref ?? '',
            $asset->item_name ?? '',
            $asset->brand ?? '',
            $asset->type ?? '',
            $asset->serial_number ?? '',
            $asset->ghrs_id ?? '',
            $asset->badge_id ?? '',
            $asset->assignment_date ? date('Y-m-d', strtotime($asset->assignment_date)) : '',
            $asset->location ?? '',
            $asset->remarks ?? '',
            $asset->dept_project ?? '',
            $asset->delivery_date ? date('Y-m-d', strtotime($asset->delivery_date)) : '',
            $asset->status ?? 'In Stock',
            $asset->service_tag ?? '',
            $asset->username ?? '',
            $asset->device_name ?? '',
            $asset->specifications ?? ''
        ];
    }

    /**
     * Apply styles to the worksheet
     */
    public function styles(Worksheet $sheet)
    {
        // Header styling
        $sheet->getStyle('A1:R1')->applyFromArray([
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
            $sheet->getStyle("A1:R{$lastRow}")->applyFromArray([
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
                    $sheet->getStyle("A{$i}:R{$i}")->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'F2F2F2']
                        ]
                    ]);
                }
            }

            // Center align date and status columns
            $sheet->getStyle("I2:I{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("M2:M{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("N2:N{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Text wrap for long text columns
            $sheet->getStyle("C2:C{$lastRow}")->getAlignment()->setWrapText(true);
            $sheet->getStyle("K2:K{$lastRow}")->getAlignment()->setWrapText(true);
            $sheet->getStyle("R2:R{$lastRow}")->getAlignment()->setWrapText(true);
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
            'A' => 15,  // pr_ref
            'B' => 15,  // po_ref
            'C' => 30,  // item
            'D' => 15,  // brand
            'E' => 20,  // type
            'F' => 20,  // serial_number
            'G' => 12,  // ghrs_id
            'H' => 12,  // badge_id
            'I' => 15,  // assignment_date
            'J' => 20,  // location
            'K' => 30,  // remarks
            'L' => 20,  // dept/project
            'M' => 15,  // delivery_date
            'N' => 12,  // status
            'O' => 18,  // service_tag
            'P' => 15,  // username
            'Q' => 18,  // device_name
            'R' => 35,  // specifications
        ];
    }

    /**
     * Sheet title - nama asset type
     */
    public function title(): string
    {
        // Sanitize sheet name (max 31 chars, no special chars)
        $title = $this->assetType->name;
        $title = str_replace(['/', '\\', '?', '*', '[', ']', ':'], '-', $title);
        $title = substr($title, 0, 31);
        
        return $title;
    }
}