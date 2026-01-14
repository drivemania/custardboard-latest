<?php
use DI\Container;
use Slim\Exception\HttpException;
use Slim\Factory\AppFactory;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Pagination\Paginator;
use Jenssegers\Blade\Blade;
use Psr\Http\Message\ServerRequestInterface;

$sessionLifeTime = 86400; 
ini_set('session.gc_maxlifetime', $sessionLifeTime); 
ini_set('session.cookie_lifetime', $sessionLifeTime); 
$sessionPath = __DIR__ . '/../cache/sessions';
if (!file_exists($sessionPath)) {
    mkdir($sessionPath, 0777, true);
}
ini_set('session.save_path', $sessionPath);

session_start();

require __DIR__ . '/../vendor/autoload.php';

date_default_timezone_set('Asia/Seoul');

if (!file_exists(__DIR__ . '/../.env')) {
    require __DIR__ . '/../lib/Installer/installer_routes.php';
    exit;
}

if (!function_exists('env')) {
    function env($key, $default = null) {
        return $_ENV[$key] ?? $default;
    }
}

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$capsule = new Capsule;
$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => env('DB_HOST', '127.0.0.1') . ':'. env('DB_PORT', '3306'),
    'database'  => env('DB_DATABASE', ''),
    'username'  => env('DB_USERNAME', 'root'),
    'password'  => env('DB_PASSWORD', ''),
    'charset'   => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix'    => env('TABLE_PREFIX', 'cu_'),
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();

Paginator::currentPathResolver(function () {
    return isset($_SERVER['REQUEST_URI']) ? strtok($_SERVER['REQUEST_URI'], '?') : '/';
});

Paginator::currentPageResolver(function ($pageName = 'page') {
    $page = $_GET[$pageName] ?? 1;
    return filter_var($page, FILTER_VALIDATE_INT) !== false && (int) $page >= 1 ? (int) $page : 1;
});

$app = AppFactory::create();

$basePath = str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);

if (strpos($_SERVER['REQUEST_URI'], $basePath) === false) {
    $basePath = str_replace('/public', '', $basePath);
}

$app->setBasePath($basePath);

$views = [
    __DIR__ . '/../views',
    __DIR__ . '/themes',
    __DIR__ . '/skins',
];
$cache = __DIR__ . '/../cache';

$blade = new Blade($views, $cache);

require __DIR__ . '/../lib/Widget.php';
require __DIR__ . '/../lib/Helper.php';
require __DIR__ . '/../lib/ContentParser.php';
require __DIR__ . '/../lib/VersionService.php';

$pluginLoader = new \App\Services\PluginLoader($app);
$pluginLoader->boot();

$app->add(new \App\Middleware\AutoLoginMiddleware());

require __DIR__ . '/../routes/auth.php';
require __DIR__ . '/../routes/admin.php';
require __DIR__ . '/../routes/web.php';
require __DIR__ . '/../routes/api.php';

$app->add(function ($request, $handler) use ($blade, $basePath) {
    $blade->share('base_path', $basePath);
    return $handler->handle($request);
});

\App\Support\PluginHelper::setBasePath($basePath);

$blade->compiler()->directive('custard_menu', function ($expression) {
    return "<?php echo Widget::menu(\$base_path, $expression); ?>";
});

$blade->compiler()->directive('custard_login', function ($expression) {
    return "<?php echo Widget::login(\$base_path, $expression ?? null) ?>";
});

$blade->compiler()->directive('custard_latestPost', function ($page=10, $subLimit=20, $gSlug = null) {
    return "<?php echo Widget::latestPosts(\$base_path, $page, $subLimit, $gSlug) ?>";
});

$blade->compiler()->directive('hook', function ($expression, $handler = null) {
    return "<?php \App\Support\Hook::trigger($expression, $handler); ?>";
});

$errorMiddleware = $app->addErrorMiddleware(false, true, true);

$errorMiddleware->setDefaultErrorHandler(
    function (
        ServerRequestInterface $request, 
        \Throwable $exception, 
        bool $displayErrorDetails, 
        bool $logErrors, 
        bool $logErrorDetails
    ) use ($app, $blade) {
        $response = $app->getResponseFactory()->createResponse();
        
        $code = $exception->getCode();
        
        if (!is_numeric($code) || $code < 100 || $code > 599) {
            $code = 500;
        }

        $message = $displayErrorDetails ? $exception->getMessage() : '서버에 문제가 발생했습니다.';

        $titles = [
            401 => '로그인이 필요합니다',
            403 => '접근 권한이 없습니다',
            404 => '페이지를 찾을 수 없습니다',
            500 => '서버 내부 오류',
        ];
        $title = $titles[$code] ?? '오류가 발생했습니다';

        try {
            $content = $blade->render('errors.errors', [
                'title' => $title,
                'errorMessage' => $message,
                'code' => $code
            ]);
            $response->getBody()->write($content);
        } catch (\Throwable $renderError) {
            $response->getBody()->write("<div style='padding:2rem; font-family:sans-serif;'><h1>{$title}</h1><p>{$message}</p></div>");
        }
        
        return $response->withStatus((int)$code);
    }
);

$app->run();