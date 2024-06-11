<?php

declare(strict_types=1);

use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;
use Ramsey\Uuid\Uuid;


return function (App $app) {
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
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

    $app->post('/comptes/{uuid}/ecritures', function ($request, $response, $args) {
        $compteUuid = $args['uuid'];
        $data = $request->getParsedBody();
    
        $errors = [];
    
        if (!isset($data['label']) || !isset($data['date']) || !isset($data['type']) || !isset($data['amount'])) {
            $errors[] = 'Champs obligatoires manquants';
        }
    
        if (isset($data['amount']) && $data['amount'] < 0) {
            $errors[] = 'Le montant ne peut pas être négatif';
        }
    
        if (isset($data['type']) && !in_array($data['type'], ['C', 'D'])) {
            $errors[] = 'Type invalide';
        }
    
        if (isset($data['date'])) {
            try {
                $date = new DateTime($data['date']);
            } catch (Exception $e) {
                $errors[] = 'Format de date invalide';
            }
        }
    
        if (!empty($errors)) {
            $response->getBody()->write(json_encode(['errors' => $errors]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    
        $uuid = Uuid::uuid4()->toString();
        $pdo = $this->get(PDO::class);
        $stmt = $pdo->prepare('INSERT INTO ecritures (uuid, compte_uuid, label, date, type, amount) VALUES (:uuid, :compte_uuid, :label, :date, :type, :amount)');
        $stmt->execute([
            'uuid' => $uuid,
            'compte_uuid' => $compteUuid,
            'label' => $data['label'],
            'date' => $date->format('Y-m-d'),
            'type' => $data['type'],
            'amount' => $data['amount']
        ]);
    
        $response->getBody()->write(json_encode(['uuid' => $uuid]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    });
    
    $app->put('/comptes/{compte_uuid}/ecritures/{uuid}', function ($request, $response, $args) {
        $compteUuid = $args['compte_uuid'];
        $ecritureUuid = $args['uuid'];
        $data = $request->getParsedBody();
    
        $errors = [];
    
        if (!isset($data['label']) || !isset($data['date']) || !isset($data['type']) || !isset($data['amount'])) {
            $errors[] = 'Champs obligatoires manquants';
        }
    
        if (isset($data['amount']) && $data['amount'] < 0) {
            $errors[] = 'Le montant ne peut pas être négatif';
        }
    
        if (isset($data['type']) && !in_array($data['type'], ['C', 'D'])) {
            $errors[] = 'Type invalide';
        }
    
        if (isset($data['date'])) {
            try {
                $date = new DateTime($data['date']);
            } catch (Exception $e) {
                $errors[] = 'Format de date invalide';
            }
        }
    
        if (!empty($errors)) {
            $response->getBody()->write(json_encode(['errors' => $errors]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    
        $pdo = $this->get(PDO::class);
    
        $stmt = $pdo->prepare('UPDATE ecritures SET label = :label, date = :date, type = :type, amount = :amount, updated_at = CURRENT_TIMESTAMP WHERE uuid = :uuid AND compte_uuid = :compte_uuid');
        $stmt->execute([
            'label' => $data['label'],
            'date' => $date->format('Y-m-d'),
            'type' => $data['type'],
            'amount' => $data['amount'],
            'uuid' => $ecritureUuid,
            'compte_uuid' => $compteUuid
        ]);
    
        $response->getBody()->write(json_encode(['uuid' => $ecritureUuid]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    });
    
    $app->delete('/comptes/{compte_uuid}/ecritures/{uuid}', function ($request, $response, $args) {
        $compteUuid = $args['compte_uuid'];
        $ecritureUuid = $args['uuid'];

        $pdo = $this->get(PDO::class);

        $stmt = $pdo->prepare('DELETE FROM ecritures WHERE uuid = :uuid AND compte_uuid = :compte_uuid');
        $stmt->execute([
            'uuid' => $ecritureUuid,
            'compte_uuid' => $compteUuid
        ]);

        return $response->withStatus(204);
    });
    
};