<?php

namespace App\Services;

use App\Models\EventRegistration;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ParticipantExporter
{
    private const HEADERS = ['No', 'Nama', 'Email', 'Nomor HP', 'Jenis', 'Metode Kehadiran', 'Status Pembayaran'];

    /**
     * @param  Collection<int, EventRegistration>  $registrations
     */
    public function generate(Collection $registrations): Spreadsheet
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Daftar Peserta');

        $sheet->fromArray(self::HEADERS, null, 'A1');
        $sheet->getStyle('A1:G1')->getFont()->setBold(true);
        $sheet->getStyle('A1:G1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D1FAE5');
        $sheet->getStyle('A1:G1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row = 2;
        foreach ($registrations as $index => $registration) {
            $user = $registration->user;

            $sheet->fromArray([
                $index + 1,
                $user->name ?? '-',
                $user->email ?? '-',
                $user->whatsapp_number ?? '-',
                EventRegistration::PARTICIPANT_TYPE_LABELS[$registration->participant_type] ?? $registration->participant_type,
                EventRegistration::ATTENDANCE_METHOD_LABELS[$registration->attendance_method] ?? $registration->attendance_method,
                EventRegistration::STATUS_LABELS[$registration->status] ?? $registration->status,
            ], null, "A{$row}");
            $row++;
        }

        foreach (range('A', 'G') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return $spreadsheet;
    }

    public function writeTo(Collection $registrations): Xlsx
    {
        return new Xlsx($this->generate($registrations));
    }
}
