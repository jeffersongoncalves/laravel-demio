<?php

namespace JeffersonGoncalves\Demio;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class DemioServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('demio')
            ->hasConfigFile();
    }
}
