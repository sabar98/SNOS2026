<?php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PaymentExporter
{
    private const HEADERS = ['No', 'Referensi', 'Nama', 'Email', 'Tipe', 'Kategori', 'Nominal', 'Status Pembayaran'];

    /**
     * @param  Collection<int, Payment>  $payments
     */
    public function generate(Collection $payments): Spreadsheet
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Daftar Pembayaran');

        $sheet->fromArray(self::HEADERS, null, 'A1');
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);
        $sheet->getStyle('A1:H1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FEF3C7');
        $sheet->getStyle('A1:H1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row = 2;
        foreach ($payments as $index => $payment) {
            $sheet->fromArray([
                $index + 1,
                $payment->reference ?? '-',
                $payment->participant_name ?? '-',
                $payment->participant_email ?? '-',
                Payment::TYPE_LABELS[$payment->type] ?? $payment->type,
                $payment->participant_category ?? '-',
                (float) $payment->amount,
                Payment::STATUS_LABELS[$payment->status] ?? $payment->status,
            ], null, "A{$row}");
            $row++;
        }

        $lastRow = max($row - 1, 2);
        $sheet->getStyle("G2:G{$lastRow}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        foreach (range('A', 'H') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return $spreadsheet;
    }

    public function writeTo(Collection $payments): Xlsx
    {
        return new Xlsx($this->generate($payments));
    }
}
