<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ParticipantAccountExporter
{
    private const HEADERS = ['No', 'Nama', 'Email', 'NIK', 'Institusi', 'WhatsApp', 'Jumlah Pendaftaran'];

    /**
     * @param  Collection<int, User>  $participants
     */
    public function generate(Collection $participants): Spreadsheet
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Akun Peserta');

        $sheet->fromArray(self::HEADERS, null, 'A1');
        $sheet->getStyle('A1:G1')->getFont()->setBold(true);
        $sheet->getStyle('A1:G1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D1FAE5');
        $sheet->getStyle('A1:G1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row = 2;
        foreach ($participants as $index => $participant) {
            $sheet->fromArray([
                $index + 1,
                $participant->name,
                $participant->email,
                $participant->nik,
                $participant->institution,
                $participant->whatsapp_number,
                $participant->event_registrations_count ?? $participant->eventRegistrations()->count(),
            ], null, "A{$row}");
            $row++;
        }

        foreach (range('A', 'G') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return $spreadsheet;
    }

    public function writeTo(Collection $participants): Xlsx
    {
        return new Xlsx($this->generate($participants));
    }
}
