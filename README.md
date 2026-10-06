# API de Missões Espaciais

## 1. Identificação
- Nome completo do aluno: Flavyo Ferreira de oliveira
- Curso: Desenvolvimento de Sistemas / Tecnologia da Informação
- Unidade Curricular: Desenvolver Serviços Web

## 2. Descrição do Projeto
Esta API REST foi desenvolvida em PHP com Slim Framework para gerenciar missões espaciais históricas e futuras. A aplicação permite cadastrar, listar, consultar por ID, atualizar e remover registros em formato JSON, seguindo os princípios de REST e HTTP.

## 3. Tecnologias Utilizadas
- PHP 8+
- Slim Framework
- Composer
- JSON
- Servidor embutido do PHP

## 4. Como Clonar o Projeto
```bash
git clone <URL_DO_REPOSITORIO>
```

## 5. Como Instalar as Dependências
```bash
composer install
```

## 6. Como Executar o Projeto
```bash
php -S localhost:8090 index.php
```

## 7. Documentação dos Endpoints

### GET /status
- Método: GET
- URL: http://localhost:8090/status
- Objetivo: Verificar se a API está funcionando.
- Exemplo de requisição:
```http
GET /status
```
- Exemplo de resposta:
```json
{
  "status": "ok"
}
```

### GET /missoes
- Método: GET
- URL: http://localhost:8090/missoes
- Objetivo: Listar todas as missões cadastradas.
- Exemplo de requisição:
```http
GET /missoes
```
- Exemplo de resposta:
```json
[
  {
    "id": 1,
    "nome": "Apollo 11",
    "ano": 1969,
    "agencia": "NASA",
    "status": "Concluída"
  }
]
```

### GET /missoes/{id}
- Método: GET
- URL: http://localhost:8080/missoes/1
- Objetivo: Consultar uma missão específica pelo ID.
- Exemplo de requisição:
```http
GET /missoes/1
```
- Exemplo de resposta:
```json
{
  "id": 1,
  "nome": "Apollo 11",
  "ano": 1969,
  "agencia": "NASA",
  "status": "Concluída"
}
```

### POST /missoes
- Método: POST
- URL: http://localhost:8090/missoes
- Objetivo: Cadastrar uma nova missão.
- Exemplo de requisição:
```json
{
  "nome": "Artemis III",
  "ano": 2027,
  "agencia": "NASA",
  "status": "Planejada"
}
```
- Exemplo de resposta:
```json
{
  "id": 6,
  "nome": "Artemis III",
  "ano": 2027,
  "agencia": "NASA",
  "status": "Planejada"
}
```

### PUT /missoes/{id}
- Método: PUT
- URL: http://localhost:8090/missoes/6
- Objetivo: Atualizar uma missão existente.
- Exemplo de requisição:
```json
{
  "nome": "Artemis III",
  "ano": 2027,
  "agencia": "NASA",
  "status": "Em preparação"
}
```
- Exemplo de resposta:
```json
{
  "id": 6,
  "nome": "Artemis III",
  "ano": 2027,
  "agencia": "NASA",
  "status": "Em preparação"
}
```

### DELETE /missoes/{id}
- Método: DELETE
- URL: http://localhost:8090/missoes/6
- Objetivo: Remover uma missão pelo ID.
- Exemplo de requisição:
```http
DELETE /missoes/6
```
- Exemplo de resposta:
```http
204 No Content
```

## 8. Evidências dos Testes
Os testes foram validados por requisições HTTP em ambiente local usando o servidor embutido do PHP e retornos em JSON. A API foi verificada para as rotas:
- GET /status
- GET /missoes
- GET /missoes/{id}
- POST /missoes
- PUT /missoes/{id}
- DELETE /missoes/{id}

> Abaixo estão exemplos de evidência de execução:

![Teste de status da API](https://via.placeholder.com/800x400?text=GET+%2Fstatus)
![Listagem de missões](https://via.placeholder.com/800x400?text=GET+%2Fmissoes)
![Cadastro de missão](https://via.placeholder.com/800x400?text=POST+%2Fmissoes)

## Observações
- As respostas da API utilizam JSON.
- Os status HTTP foram aplicados de acordo com a operação executada.
- A aplicação pode ser executada localmente com o comando informado acima.
