<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use App\Application\Settings\SettingsInterface; // S'il y a d'autres classes utilisées, gardez ces déclarations use
// Pas besoin de la déclaration use PDO ici

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        PDO::class => function (ContainerInterface $c) {
            $settings = $c->get(SettingsInterface::class)->get('db');

            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                $settings['host'],
                $settings['database'],
                $settings['charset']
            );

            $pdo = new PDO($dsn, $settings['username'], $settings['password']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return $pdo;
        },
    ]);
};
