<?php

namespace App\Providers\Filament;

use Illuminate\Support\Facades\Blade;

final class PanelLocaleSwitcher
{
    public static function render(): string
    {
        return Blade::render('
            <div class="flex items-center gap-x-3 mr-3">
                <a href="{{ route(\'language.switch\', [\'locale\' => \'vi\']) }}" class="text-sm font-medium {{ app()->getLocale() === \'vi\' ? \'text-primary-600 underline dark:text-primary-400\' : \'text-gray-500 dark:text-gray-400\' }}">
                    VI
                </a>
                <span class="text-gray-300 dark:text-gray-600">|</span>
                <a href="{{ route(\'language.switch\', [\'locale\' => \'en\']) }}" class="text-sm font-medium {{ app()->getLocale() === \'en\' ? \'text-primary-600 underline dark:text-primary-400\' : \'text-gray-500 dark:text-gray-400\' }}">
                    EN
                </a>
            </div>
        ');
    }
}
