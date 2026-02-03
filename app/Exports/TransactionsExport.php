<?php

namespace App\Exports;

use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TransactionsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $date;

    public function __construct($date)
    {
        $this->date = $date;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Transaction::with(['insurance', 'user', 'details.medicalService'])
            ->whereDate('paid_at', $this->date)
            ->where('payment_status', 'paid')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Kode Transaksi',
            'Tanggal Bayar',
            'Nama Pasien',
            'Asuransi',
            'Kasir',
            'Subtotal',
            'Diskon',
            'Total',
            'Layanan'
        ];
    }

    public function map($transaction): array
    {
        $services = $transaction->details->map(function($detail) {
            return $detail->medicalService->name . ' (Qty: ' . $detail->quantity . ')';
        })->implode(', ');

        return [
            $transaction->transaction_code,
            $transaction->paid_at ? $transaction->paid_at->format('d/m/Y H:i') : '-',
            $transaction->patient_name,
            $transaction->insurance->name ?? '-',
            $transaction->user->name,
            $transaction->subtotal,
            $transaction->discount_amount,
            $transaction->total,
            $services
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
