<?php
use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app = AppFactory::create();

$basePath = str_replace('/public/index.php', '', $_SERVER['SCRIPT_NAME']);
$app->setBasePath($basePath);

function renderInstallView($response, $file, $data = []) {
    global $basePath;
    $data['basePath'] = $basePath;

    extract($data);
    ob_start();
    require __DIR__ . '/views/' . $file . '.php';
    $content = ob_get_clean();
    $response->getBody()->write($content);
    return $response;
}

require_once __DIR__ . '/InstallerController.php';
$controller = new InstallerController();

$app->get('/', [$controller, 'step1']);

$app->get('/step2', [$controller, 'step2']);

$app->get('/step3', [$controller, 'step3']);
$app->post('/step3', [$controller, 'checkDbConnection']);

$app->get('/step4', [$controller, 'step4']);
$app->post('/install', [$controller, 'runInstall']);

$app->map(['GET', 'POST'], '/{routes:.+}', function ($request, $response) {
    global $basePath;
    
    return $response
        ->withHeader('Location', $basePath . '/')
        ->withStatus(302);
});

$app->run();