<?php

namespace App\Exports;

use App\Models\Asset;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AssetsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Asset::with(['assetType', 'assignedEmployee']);

        // Apply filters
        if (!empty($this->filters['asset_type_id'])) {
            $query->where('asset_type_id', $this->filters['asset_type_id']);
        }

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['assigned_to'])) {
            $query->where('assigned_to', $this->filters['assigned_to']);
        }

        if (!empty($this->filters['start_date'])) {
            $query->whereDate('last_status_date', '>=', $this->filters['start_date']);
        }

        if (!empty($this->filters['end_date'])) {
            $query->whereDate('last_status_date', '<=', $this->filters['end_date']);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Asset Tag',
            'Serial Number',
            'Tipe Perangkat',
            'Employee ID',
            'Owner Name',
            'Email',
            'Department',
            'Cost Center',
            'Status',
            'Assignment Date',
            'Last Status Date',
            'Notes',
        ];
    }

    public function map($asset): array
    {
        return [
            $asset->asset_tag,
            $asset->serial_number ?? '-',
            $asset->assetType->name,
            $asset->assignedEmployee->employee_id ?? '-',
            $asset->assignedEmployee->name ?? '-',
            $asset->assignedEmployee->email ?? '-',
            $asset->assignedEmployee->department ?? '-',
            $asset->assignedEmployee->cost_center ?? '-',
            $asset->status,
            $asset->assignment_date ? $asset->assignment_date->format('d/m/Y') : '-',
            $asset->last_status_date->format('d/m/Y'),
            $asset->notes ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}