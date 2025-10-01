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
        $query = Asset::query();

        // Apply filters
        if (!empty($this->filters['asset_type'])) {
            $query->where('asset_type', $this->filters['asset_type']);
        }

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['owner'])) {
            $query->where('current_owner', 'like', '%' . $this->filters['owner'] . '%');
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
            'Owner',
            'Status',
            'Tanggal Status',
            'Catatan',
            'Dibuat Pada',
        ];
    }

    public function map($asset): array
    {
        return [
            $asset->asset_tag,
            $asset->serial_number ?? '-',
            $asset->asset_type,
            $asset->current_owner ?? '-',
            $asset->status,
            $asset->last_status_date->format('d/m/Y'),
            $asset->notes ?? '-',
            $asset->created_at->format('d/m/Y H:i'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}