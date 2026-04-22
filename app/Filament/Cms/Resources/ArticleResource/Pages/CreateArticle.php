<?php

namespace App\Filament\Cms\Resources\ArticleResource\Pages;

use App\Filament\Cms\Resources\Base\Pages\CmsCreateRecord;
use App\Filament\Cms\Resources\ArticleResource;

class CreateArticle extends CmsCreateRecord
{
    protected static string $resource = ArticleResource::class;
}
