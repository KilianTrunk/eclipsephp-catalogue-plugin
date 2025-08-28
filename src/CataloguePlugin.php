<?php

namespace Eclipse\Catalogue;

use Eclipse\Common\Foundation\Plugins\Plugin;
use Filament\Panel;

class CataloguePlugin extends Plugin
{
    public function register(Panel $panel): void
    {
        if ($panel->getId() === 'frontend') {
            parent::register($panel);
        } else {
            $panel->discoverResources(__DIR__.'/Filament/Resources', 'Eclipse\\Catalogue\\Filament\\Resources');
        }
    }
}
