<?php

// Carrega as classes instaladas pelo Composer, incluindo o Slim Framework.
require __DIR__ . '/vendor/autoload.php';

// Interfaces PSR usadas para tipar as requisições e respostas HTTP.
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
// Fábrica do Slim responsável por criar a aplicação HTTP.
use Slim\Factory\AppFactory;

// Cria a aplicação e habilita a leitura automática de corpos JSON e formulários.
$app = AppFactory::create();
$app->addBodyParsingMiddleware();

// Define o arquivo usado para guardar os registros entre diferentes requisições.
$storageFile = __DIR__ . '/data/missoes.json';

// Lê as missões do arquivo; se ele estiver ausente ou inválido, inicializa os dados padrão.
function carregarMissoes(string $storageFile): array
{
    // Conjunto inicial exigido pelo projeto, com pelo menos cinco missões.
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

    // Cria a pasta de dados caso ela ainda não exista.
    if (!is_dir(dirname($storageFile))) {
        mkdir(dirname($storageFile), 0777, true);
    }

    // Na primeira execução, cria o arquivo com as missões iniciais.
    if (!file_exists($storageFile)) {
        file_put_contents($storageFile, json_encode($missoesPadrao, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return $missoesPadrao;
    }

    // Converte o conteúdo JSON do arquivo em um array PHP.
    $conteudo = file_get_contents($storageFile);
    $dados = json_decode($conteudo, true);

    // Restaura os registros iniciais se o conteúdo estiver vazio ou não for um array válido.
    if (!is_array($dados) || $dados === []) {
        file_put_contents($storageFile, json_encode($missoesPadrao, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return $missoesPadrao;
    }

    // Retorna os registros carregados do armazenamento.
    return $dados;
}

// Salva a lista atualizada de missões em formato JSON legível.
function salvarMissoes(array $missoes, string $storageFile): void
{
    $dir = dirname($storageFile);

    // Garante que a pasta de destino exista antes da gravação.
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    // Codifica o array PHP como JSON e grava no arquivo.
    file_put_contents($storageFile, json_encode($missoes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// GET /status: informa que a API está disponível.
$app->get('/status', function (Request $request, Response $response) {
    // Monta a resposta e declara que o conteúdo retornado é JSON.
    $payload = ['status' => 'ok'];
    $response = $response->withHeader('Content-Type', 'application/json');
    $response->getBody()->write(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    // Retorna o código HTTP 200 (OK).
    return $response->withStatus(200);
});

// GET /missoes: retorna todas as missões cadastradas.
$app->get('/missoes', function (Request $request, Response $response) use ($storageFile) {
    // Lê os registros persistidos e prepara o corpo JSON da resposta.
    $missoes = carregarMissoes($storageFile);
    $response = $response->withHeader('Content-Type', 'application/json');
    $response->getBody()->write(json_encode($missoes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    // Retorna o código HTTP 200 (OK).
    return $response->withStatus(200);
});

// GET /missoes/{id}: procura e retorna uma missão pelo ID informado na rota.
$app->get('/missoes/{id}', function (Request $request, Response $response, array $args) use ($storageFile) {
    // Lê o parâmetro de rota e carrega a lista atual.
    $id = (int) $args['id'];
    $missoes = carregarMissoes($storageFile);

    // Percorre os registros até localizar o ID solicitado.
    foreach ($missoes as $missao) {
        if ((int) $missao['id'] === $id) {
            $response = $response->withHeader('Content-Type', 'application/json');
            $response->getBody()->write(json_encode($missao, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            // Retorna a missão encontrada com o código HTTP 200 (OK).
            return $response->withStatus(200);
        }
    }

    // Se não houver correspondência, responde com erro JSON e HTTP 404.
    $payload = ['error' => 'Missão não encontrada'];
    $response = $response->withHeader('Content-Type', 'application/json');
    $response->getBody()->write(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    return $response->withStatus(404);
});

// POST /missoes: valida os dados recebidos e cadastra uma nova missão.
$app->post('/missoes', function (Request $request, Response $response) use ($storageFile) {
    // Obtém os campos enviados no corpo da requisição e carrega os registros atuais.
    $data = $request->getParsedBody();
    $missoes = carregarMissoes($storageFile);

    // Lê e normaliza os campos obrigatórios enviados pelo cliente.
    $nome = trim((string) ($data['nome'] ?? ''));
    $ano = (int) ($data['ano'] ?? 0);
    $agencia = trim((string) ($data['agencia'] ?? ''));
    $status = trim((string) ($data['status'] ?? ''));

    // Rejeita o cadastro se faltar texto obrigatório ou se o ano não for positivo.
    if ($nome === '' || $agencia === '' || $status === '' || $ano <= 0) {
        $payload = ['error' => 'Dados inválidos. Informe nome, ano, agencia e status.'];
        $response = $response->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        // HTTP 400 indica que os dados enviados são inválidos.
        return $response->withStatus(400);
    }

    // Calcula o próximo ID usando o maior ID atual mais um.
    $novoId = 1;
    foreach ($missoes as $item) {
        $novoId = max($novoId, (int) $item['id'] + 1);
    }

    // Monta o novo registro com o ID gerado e os dados validados.
    $novaMissao = [
        'id' => $novoId,
        'nome' => $nome,
        'ano' => $ano,
        'agencia' => $agencia,
        'status' => $status,
    ];

    // Adiciona e persiste o registro antes de responder ao cliente.
    $missoes[] = $novaMissao;
    salvarMissoes($missoes, $storageFile);

    // Retorna a missão criada como JSON e informa HTTP 201 (Created).
    $response = $response->withHeader('Content-Type', 'application/json');
    $response->getBody()->write(json_encode($novaMissao, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    return $response->withStatus(201);
});

// PUT /missoes/{id}: atualiza os dados da missão identificada pelo parâmetro de rota.
$app->put('/missoes/{id}', function (Request $request, Response $response, array $args) use ($storageFile) {
    // Obtém o ID, os dados enviados no corpo e a lista atual de missões.
    $id = (int) $args['id'];
    $data = $request->getParsedBody();
    $missoes = carregarMissoes($storageFile);

    // Localiza o registro e substitui os campos enviados; campos ausentes mantêm o valor atual.
    foreach ($missoes as $index => $item) {
        if ((int) $item['id'] === $id) {
            $missoes[$index] = [
                'id' => $id,
                'nome' => trim((string) ($data['nome'] ?? $item['nome'])),
                'ano' => (int) ($data['ano'] ?? $item['ano']),
                'agencia' => trim((string) ($data['agencia'] ?? $item['agencia'])),
                'status' => trim((string) ($data['status'] ?? $item['status'])),
            ];

            // Persiste a alteração e devolve a missão atualizada em JSON.
            salvarMissoes($missoes, $storageFile);

            $response = $response->withHeader('Content-Type', 'application/json');
            $response->getBody()->write(json_encode($missoes[$index], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            // HTTP 200 indica que a atualização foi concluída.
            return $response->withStatus(200);
        }
    }

    // Se o ID não existir, retorna erro JSON com HTTP 404 (Not Found).
    $payload = ['error' => 'Missão não encontrada'];
    $response = $response->withHeader('Content-Type', 'application/json');
    $response->getBody()->write(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    return $response->withStatus(404);
});

// DELETE /missoes/{id}: remove do arquivo a missão identificada pelo parâmetro de rota.
$app->delete('/missoes/{id}', function (Request $request, Response $response, array $args) use ($storageFile) {
    // Lê o ID solicitado e carrega os registros persistidos.
    $id = (int) $args['id'];
    $missoes = carregarMissoes($storageFile);

    // Procura o registro, remove-o da lista e salva o resultado.
    foreach ($missoes as $index => $item) {
        if ((int) $item['id'] === $id) {
            array_splice($missoes, $index, 1);
            salvarMissoes($missoes, $storageFile);

            // HTTP 204 indica sucesso sem conteúdo no corpo da resposta.
            return $response->withStatus(204);
        }
    }

    // Se o ID não existir, retorna uma mensagem de erro em JSON com HTTP 404.
    $payload = ['error' => 'Missão não encontrada'];
    $response = $response->withHeader('Content-Type', 'application/json');
    $response->getBody()->write(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    return $response->withStatus(404);
});

// Inicia o Slim para receber e despachar as requisições HTTP.
$app->run();
