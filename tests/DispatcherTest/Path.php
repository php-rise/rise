<?php
namespace Rise\Test\DispatcherTest;

use Rise\Path as BasePath;

class Path extends BasePath {
    public function getConfigurationsPath() {
        return __DIR__ . '/../config';
    }
}

