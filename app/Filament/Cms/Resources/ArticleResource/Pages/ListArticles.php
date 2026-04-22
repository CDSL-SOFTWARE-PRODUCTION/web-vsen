<?php

namespace App\Filament\Cms\Resources\ArticleResource\Pages;

use App\Filament\Cms\Resources\Base\Pages\CmsListRecords;
use App\Filament\Cms\Resources\ArticleResource;

class ListArticles extends CmsListRecords
{
    protected static string $resource = ArticleResource::class;
}
