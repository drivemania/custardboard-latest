<?php
namespace App\Controller\Admin;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Illuminate\Database\Capsule\Manager as DB;
use App\Support\PluginHelper as Helper;

class AdminPluginController {

    protected $blade;
    protected $basePath;
    protected $returnUrl;

    public function __construct($blade, $basePath)
    {
        $this->blade = $blade;
        $this->basePath = $basePath;
        $this->returnUrl = "";
    }
    
    public function index(Request $request, Response $response, $view) {
        $pluginDir = __DIR__ . '/../../../public/plugins';
        $dirs = glob($pluginDir . '/*', GLOB_ONLYDIR);
        
        $dbPlugins = DB::table('plugins')->pluck('is_active', 'directory')->toArray();

        $plugins = [];

        foreach ($dirs as $dir) {
            $dirName = basename($dir);
            $jsonFile = $dir . '/plugin.json';

            if (!file_exists($jsonFile)) continue;

            $info = $jsonFile ? json_decode(file_get_contents($jsonFile), true) : [];
            
            if (!$info) continue;

            $isActive = $dbPlugins[$dirName] ?? 0;

            $plugins[] = [
                'directory' => $dirName,
                'id'        => $info['id'] ?? $dirName,
                'name'      => $info['name'] ?? $dirName,
                'version'   => $info['version'] ?? '0.0.1',
                'description'=> $info['description'] ?? '',
                'author'    => $info['author'] ?? 'Unknown',
                'is_active' => $isActive
            ];
        }

        $content = $this->blade->render('admin.plugins.index', [
            'title' => '플러그인 관리',
            'plugin' => $plugins
        ]);

        $response->getBody()->write($content);
        return $response;
        
    }

    public function toggle(Request $request, Response $response) {
        $data = $request->getParsedBody();
        $id = $data['id'];
        $dir = $data['directory'];

        $plugin = DB::table('plugins')->where('directory', $dir)->first();

        if ($plugin) {
            DB::table('plugins')->where('directory', $dir)->update([
                'is_active' => !$plugin->is_active
            ]);
            $msg = $plugin->is_active ? '플러그인을 비활성화했습니다.' : '플러그인을 활성화했습니다.';
        } else {
            DB::table('plugins')->insert([
                'name' => $id,
                'directory' => $dir,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            $msg = '플러그인을 활성화했습니다.';
        }

        $_SESSION['flash_message'] = $msg;
        $_SESSION['flash_type'] = 'success';
        return $response
            ->withHeader('Location', $this->basePath . '/admin/plugins')
            ->withStatus(302);
    }

    public function setting(Request $request, Response $response, $args) {
        $pluginName = $args['plugin_name'];
        
        if (preg_match('/[^a-zA-Z0-9-_]/', $pluginName)) {
            $_SESSION['flash_message'] = "유효하지 않은 이름입니다.";
            $_SESSION['flash_type'] = 'error';
            return $response
            ->withHeader('Location', $this->basePath . '/admin')
            ->withStatus(302);
        }

        $plugin = DB::table('plugins')
        ->where('name', $pluginName)
        ->first();

        if (!$plugin) {
            $_SESSION['flash_message'] = "존재하지 않는 플러그인입니다.";
            $_SESSION['flash_type'] = 'error';
            return $response
            ->withHeader('Location', $this->basePath . '/admin')
            ->withStatus(302);
        }

        $settingFile = __DIR__ . '/../../../public/plugins/' . $plugin->directory . '/Settings.php';
        
        if (!file_exists($settingFile)) {
            $_SESSION['flash_message'] = "설정 페이지가 없는 플러그인입니다.";
            $_SESSION['flash_type'] = 'error';
            return $response
            ->withHeader('Location', $this->basePath . '/admin')
            ->withStatus(302);
        }

        // 현재 설정값 가져오기
        $settings = Helper::getPluginSettings($pluginName);

        ob_start();
        include $settingFile; 
        $content = ob_get_clean();
        $return = $this->blade->render('admin.plugins.wrapper', [
            'pluginName' => $pluginName,
            'pluginContent' => $content,
            'settings' => $settings
        ]);

        $response->getBody()->write($return);
        return $response;

    }

    public function settingSave(Request $request, Response $response, $args) {
        $pluginName = $args['plugin_name'];
        $data = $request->getParsedBody();

        Helper::savePluginSettings($pluginName, $data);

        $_SESSION['flash_message'] = "설정이 저장되었습니다.";
        $_SESSION['flash_type'] = 'success';
        return $response
            ->withHeader('Location', $this->basePath . '/admin/plugins/' . $pluginName)
            ->withStatus(302);
    }
}