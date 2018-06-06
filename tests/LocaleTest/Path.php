<?php
namespace Rise\Test\LocaleTest;

use Rise\Path as BasePath;

class Path extends BasePath {
    public function getConfigurationsPath() {
        return __DIR__ . '/../config';
    }
}

