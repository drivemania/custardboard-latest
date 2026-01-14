<?php
namespace App\Services;

class VersionService {
    const CURRENT_VERSION = '0.4.0';
    const CURRENT_CODENAME = 'Cinnamon';
    
    const UPDATE_URL = 'https://drivemania.github.io/custardboard-doc/version.json';
    
    private $cacheFile;

    public function __construct() {
        $this->cacheFile = __DIR__ . '/../cache/version_check.json';
    }

    public function checkUpdate() {
        if ($this->hasValidCache()) {
            return $this->getFromCache();
        }

        return $this->fetchFromRemote();
    }

    private function hasValidCache() {
        if (!file_exists($this->cacheFile)) return false;
        
        $data = json_decode(file_get_contents($this->cacheFile), true);
        if (!$data || !isset($data['checked_at'])) return false;

        return (time() - $data['checked_at']) < 86400;
    }

    private function getFromCache() {
        $data = json_decode(file_get_contents($this->cacheFile), true);
        return $this->processVersionData($data);
    }

    private function fetchFromRemote() {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::UPDATE_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

        $json = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($json === false || $httpCode !== 200) {
            return ['has_update' => false, 'error' => '서버 연결 실패'];
        }

        $data = json_decode($json, true);
        
        $data['checked_at'] = time();
        
        if (!is_dir(dirname($this->cacheFile))) {
            mkdir(dirname($this->cacheFile), 0777, true);
        }
        file_put_contents($this->cacheFile, json_encode($data));

        return $this->processVersionData($data);
    }

    private function processVersionData($remoteData) {
        if (!isset($remoteData['latest_version'])) {
            return ['has_update' => false];
        }

        $hasUpdate = version_compare(self::CURRENT_VERSION, $remoteData['latest_version'], '<');

        return [
            'has_update'     => $hasUpdate,
            'current_version'=> self::CURRENT_VERSION,
            'current_codename'=> self::CURRENT_CODENAME,
            'latest_version' => $remoteData['latest_version'],
            'latest_codename'=> $remoteData['latest_codename'],
            'message'        => $remoteData['message'] ?? '',
            'link'           => $remoteData['download_url'] ?? '#',
            'importance'     => $remoteData['importance'] ?? 'normal'
        ];
    }
}