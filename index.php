<?php

require __DIR__ . '/vendor/autoload.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

$app = AppFactory::create();
$app->addBodyParsingMiddleware();

$storageFile = __DIR__ . '/data/missoes.json';

function carregarMissoes(string $storageFile): array
{
    $missoesPadrao = [
        [
            'id' => 1,
            'nome' => 'Apollo 11',
            'ano' => 1969,
            'agencia' => 'NASA',
            'status' => 'Concluída',
        ],
        [
            'id' => 2,
            'nome' => 'Voyager 1',
            'ano' => 1977,
            'agencia' => 'NASA',
            'status' => 'Em operação',
        ],
        [
            'id' => 3,
            'nome' => 'Artemis II',
            'ano' => 2026,
            'agencia' => 'NASA',
            'status' => 'Planejada',
        ],
        [
            'id' => 4,
            'nome' => 'Mars Rover Perseverance',
            'ano' => 2021,
            'agencia' => 'NASA',
            'status' => 'Em operação',
        ],
        [
            'id' => 5,
            'nome' => 'James Webb',
            'ano' => 2021,
            'agencia' => 'NASA / ESA / CSA',
            'status' => 'Em operação',
        ],
    ];

    if (!is_dir(dirname($storageFile))) {
        mkdir(dirname($storageFile), 0777, true);
    }

    if (!file_exists($storageFile)) {
        file_put_contents($storageFile, json_encode($missoesPadrao, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return $missoesPadrao;
    }

    $conteudo = file_get_contents($storageFile);
    $dados = json_decode($conteudo, true);

    if (!is_array($dados) || $dados === []) {
        file_put_contents($storageFile, json_encode($missoesPadrao, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return $missoesPadrao;
    }

    return $dados;
}

function salvarMissoes(array $missoes, string $storageFile): void
{
    $dir = dirname($storageFile);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    file_put_contents($storageFile, json_encode($missoes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

$app->get('/status', function (Request $request, Response $response) {
    $payload = ['status' => 'ok'];
    $response = $response->withHeader('Content-Type', 'application/json');
    $response->getBody()->write(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    return $response->withStatus(200);
});

$app->get('/missoes', function (Request $request, Response $response) use ($storageFile) {
    $missoes = carregarMissoes($storageFile);
    $response = $response->withHeader('Content-Type', 'application/json');
    $response->getBody()->write(json_encode($missoes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    return $response->withStatus(200);
});

$app->get('/missoes/{id}', function (Request $request, Response $response, array $args) use ($storageFile) {
    $id = (int) $args['id'];
    $missoes = carregarMissoes($storageFile);

    foreach ($missoes as $missao) {
        if ((int) $missao['id'] === $id) {
            $response = $response->withHeader('Content-Type', 'application/json');
            $response->getBody()->write(json_encode($missao, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            return $response->withStatus(200);
        }
    }

    $payload = ['error' => 'Missão não encontrada'];
    $response = $response->withHeader('Content-Type', 'application/json');
    $response->getBody()->write(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    return $response->withStatus(404);
});

$app->post('/missoes', function (Request $request, Response $response) use ($storageFile) {
    $data = $request->getParsedBody();
    $missoes = carregarMissoes($storageFile);

    $nome = trim((string) ($data['nome'] ?? ''));
    $ano = (int) ($data['ano'] ?? 0);
    $agencia = trim((string) ($data['agencia'] ?? ''));
    $status = trim((string) ($data['status'] ?? ''));

    if ($nome === '' || $agencia === '' || $status === '' || $ano <= 0) {
        $payload = ['error' => 'Dados inválidos. Informe nome, ano, agencia e status.'];
        $response = $response->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return $response->withStatus(400);
    }

    $novoId = 1;
    foreach ($missoes as $item) {
        $novoId = max($novoId, (int) $item['id'] + 1);
    }

    $novaMissao = [
        'id' => $novoId,
        'nome' => $nome,
        'ano' => $ano,
        'agencia' => $agencia,
        'status' => $status,
    ];

    $missoes[] = $novaMissao;
    salvarMissoes($missoes, $storageFile);

    $response = $response->withHeader('Content-Type', 'application/json');
    $response->getBody()->write(json_encode($novaMissao, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    return $response->withStatus(201);
});

$app->put('/missoes/{id}', function (Request $request, Response $response, array $args) use ($storageFile) {
    $id = (int) $args['id'];
    $data = $request->getParsedBody();
    $missoes = carregarMissoes($storageFile);

    foreach ($missoes as $index => $item) {
        if ((int) $item['id'] === $id) {
            $missoes[$index] = [
                'id' => $id,
                'nome' => trim((string) ($data['nome'] ?? $item['nome'])),
                'ano' => (int) ($data['ano'] ?? $item['ano']),
                'agencia' => trim((string) ($data['agencia'] ?? $item['agencia'])),
                'status' => trim((string) ($data['status'] ?? $item['status'])),
            ];

            salvarMissoes($missoes, $storageFile);

            $response = $response->withHeader('Content-Type', 'application/json');
            $response->getBody()->write(json_encode($missoes[$index], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            return $response->withStatus(200);
        }
    }

    $payload = ['error' => 'Missão não encontrada'];
    $response = $response->withHeader('Content-Type', 'application/json');
    $response->getBody()->write(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    return $response->withStatus(404);
});

$app->delete('/missoes/{id}', function (Request $request, Response $response, array $args) use ($storageFile) {
    $id = (int) $args['id'];
    $missoes = carregarMissoes($storageFile);

    foreach ($missoes as $index => $item) {
        if ((int) $item['id'] === $id) {
            array_splice($missoes, $index, 1);
            salvarMissoes($missoes, $storageFile);

            return $response->withStatus(204);
        }
    }

    $payload = ['error' => 'Missão não encontrada'];
    $response = $response->withHeader('Content-Type', 'application/json');
    $response->getBody()->write(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    return $response->withStatus(404);
});

$app->run();
