<?php
namespace App\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Illuminate\Database\Capsule\Manager as DB;

class PluginDispatcherController {

    private static $blackList = [
        'Illuminate\Database',
        'DB::',
        'exec(',
        'system(',
        'shell_exec',
        'eval(',
        'DROP TABLE',
        'DELETE FROM',
    ];

    public function dispatch(Request $request, Response $response, $args) {
        $pluginName = $args['plugin_name'];
        $action = $args['action'];

        $plugin = DB::table('plugins')->where('is_active', 1)->where('name', $pluginName)->first();

        if(!$plugin) {
            $response->getBody()->write(json_encode(['error' => 'Plugin not found']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $directory = $plugin->directory;

        $pluginPath = __DIR__ . '/../../public/plugins/' . $directory . '/ApiController.php';
        
        if (file_exists($pluginPath)) {
            $content = file_get_contents($pluginPath);
            
            foreach (self::$blackList as $keyword) {
                if (stripos($content, $keyword) !== false) {
                    $response->getBody()->write(json_encode([
                        'error' => 'Security Violation',
                        'message' => "플러그인에서 허용되지 않은 코드('$keyword')가 발견되어 실행이 차단되었습니다."
                    ]));
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
                }
            }
        } else {
             $response->getBody()->write(json_encode(['error' => 'Plugin file not found']));
             return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        require_once $pluginPath;
        $className = "\\Plugins\\" . ucfirst($pluginName) . "\\ApiController";

        if (class_exists($className)) {
            $controller = new $className();
            if (method_exists($controller, $action)) {
                return $controller->$action($request, $response, $args);
            }
        }

        $response->getBody()->write(json_encode(['error' => 'API not found']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }
}