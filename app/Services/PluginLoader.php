<?php
namespace App\Services;

use Illuminate\Database\Capsule\Manager as DB;
use Slim\App;

class PluginLoader {
    protected $app;

    public function __construct(App $app) {
        $this->app = $app;
    }

    public function boot() {
        try {
            $actives = DB::table('plugins')->where('is_active', 1)->pluck('directory')->toArray();
        } catch (\Exception $e) {
            return;
        }

        foreach ($actives as $dir) {
            $file = __DIR__ . '/../../public/plugins/' . $dir . '/Plugin.php';
            
            if (file_exists($file)) {
                require_once $file;
            }
        }
    }
}