<?php

namespace App\Services;

use App\Models\Article;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdf;
use Illuminate\Support\Collection;

class ArticlePdfExporter
{
    /**
     * @param  Collection<int, Article>  $articles
     */
    public function generate(Collection $articles, ?string $search = null): DomPdf
    {
        return Pdf::loadView('admin.articles.pdf', [
            'articles' => $articles,
            'search' => $search,
            'generatedAt' => now(),
            'seminarName' => config('seminar.name'),
        ])->setPaper('a4', 'landscape');
    }
}
