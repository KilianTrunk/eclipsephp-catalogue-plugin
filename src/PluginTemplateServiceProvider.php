<?php

namespace Eclipse\PluginTemplate;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class PluginTemplateServiceProvider extends PackageServiceProvider
{
    public static string $name = 'plugin-template';

    public function configurePackage(Package $package): void
    {
        $package->name(static::$name)
            ->hasConfigFile()
            ->hasTranslations();
    }
}
