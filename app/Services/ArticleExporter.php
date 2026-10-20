<?php

namespace App\Services;

use App\Models\Article;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ArticleExporter
{
    private const HEADERS = ['No', 'Nama Penulis', 'Instansi', 'Nomor HP/WhatsApp', 'Judul Artikel', 'Status'];

    /**
     * @param  Collection<int, Article>  $articles
     */
    public function generate(Collection $articles): Spreadsheet
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Daftar Artikel');

        $sheet->fromArray(self::HEADERS, null, 'A1');
        $sheet->getStyle('A1:F1')->getFont()->setBold(true);
        $sheet->getStyle('A1:F1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('EDE9FE');
        $sheet->getStyle('A1:F1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row = 2;
        foreach ($articles as $index => $article) {
            $user = $article->eventRegistration->user;

            $sheet->fromArray([
                $index + 1,
                $user->name ?? '-',
                $user->institution ?? $article->eventRegistration->institution ?? '-',
                $user->whatsapp_number ?? '-',
                $article->title,
                Article::STATUS_LABELS[$article->status] ?? $article->status,
            ], null, "A{$row}");
            $row++;
        }

        foreach (range('A', 'F') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return $spreadsheet;
    }

    public function writeTo(Collection $articles): Xlsx
    {
        return new Xlsx($this->generate($articles));
    }
}
