<?php

namespace App\Exports;

use App\Models\LoanLog;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LoanLogExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = LoanLog::query();

        // Apply filters
        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['start_date'])) {
            $query->whereDate('loan_date', '>=', $this->filters['start_date']);
        }

        if (!empty($this->filters['end_date'])) {
            $query->whereDate('loan_date', '<=', $this->filters['end_date']);
        }

        return $query->orderBy('loan_date', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'Tanggal Pinjam',
            'Jam',
            'PIC / User',
            'Item',
            'Jumlah',
            'Tanggal Kembali',
            'Status',
        ];
    }

    public function map($loan): array
    {
        return [
            $loan->loan_date->format('d/m/Y'),
            $loan->loan_time ? \Carbon\Carbon::parse($loan->loan_time)->format('H:i') : '-',
            $loan->pic_user,
            $loan->item_description,
            $loan->quantity,
            $loan->return_date ? $loan->return_date->format('d/m/Y') : '-',
            $loan->status == 'On Loan' ? 'Dipinjam' : 'Kembali',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}