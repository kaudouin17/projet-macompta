<?php

declare(strict_types=1);

use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $app->get('/', function (Request $request, Response $response) {
        $response->getBody()->write('Hello world!');
        return $response;
    });

    $app->group('/users', function (Group $group) {
        $group->get('', ListUsersAction::class);
        $group->get('/{id}', ViewUserAction::class);
    });

    $app->get('/comptes/{uuid}/ecritures', function ($request, $response, $args) {
        $uuid = $args['uuid'];
    
        $pdo = $this->get(PDO::class);
        $stmt = $pdo->prepare('SELECT label, date, type, amount, created_at, updated_at FROM ecritures WHERE compte_uuid = :uuid');
        $stmt->execute(['uuid' => $uuid]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        $formattedResponse = ['items' => []];
    
        foreach ($results as $result) {
            $formattedResponse['items'][] = [
                'label' => $result['label'],
                'date' => $result['date'],
                'type' => $result['type'],
                'amount' => $result['amount'],
                'created_at' => $result['created_at'],
                'updated_at' => $result['updated_at']
            ];
        }
        $response->getBody()->write(json_encode($formattedResponse));
        return $response->withHeader('Content-Type', 'application/json');
    }); 

    
};
