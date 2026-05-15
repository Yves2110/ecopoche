<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function createApplication(): \Illuminate\Foundation\Application
    {
        // Forcer la base de test avant que Laravel ne charge la config
        putenv('DB_DATABASE=ecopoche_test');
        $_ENV['DB_DATABASE']    = 'ecopoche_test';
        $_SERVER['DB_DATABASE'] = 'ecopoche_test';

        $app = parent::createApplication();

        // Double sécurité après le boot
        $app['config']->set('database.connections.mysql.database', 'ecopoche_test');

        return $app;
    }
}
