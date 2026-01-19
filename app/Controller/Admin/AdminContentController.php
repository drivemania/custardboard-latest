<?php
namespace App\Controller\Admin;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Illuminate\Database\Capsule\Manager as DB;

class AdminContentController {

    protected $blade;
    protected $basePath;
    protected $returnUrl;

    public function __construct($blade, $basePath)
    {
        $this->blade = $blade;
        $this->basePath = $basePath;
        $this->returnUrl = "";
    }

    public function boardList(Request $request, Response $response) {
        $boards = DB::table('boards')
        ->where('is_deleted', 0)
        ->orderBy('created_at', 'desc')
        ->get();

        $content = $this->blade->render('admin.boards.index', [
            'title' => '게시판 원본 관리',
            'boards' => $boards
        ]);
        $response->getBody()->write($content);
        return $response;
    }

    public function boardStore(Request $request, Response $response) {
        $data = $request->getParsedBody();
            
        $title = trim($data['title']);

        DB::table('boards')->insert([
            'title' => $title,
            'board_skin' => 'basic',
            'type' => $data['type'] ?? 'document',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $_SESSION['flash_message'] = '게시판이 생성되었습니다. 이제 그룹 메뉴 설정에서 연결하세요.';
        $_SESSION['flash_type'] = 'success';
        return $response->withHeader('Location', $this->basePath . '/admin/boards')->withStatus(302);
    }

    public function boardEdit(Request $request, Response $response, $args) {
        $id = $args['id'];
        $board = DB::table('boards')->find($id);
    
        $boardSkins = self::getSkinList($this->basePath, 'board');
        $charSkins = self::getSkinList($this->basePath, 'character');
        $loadSkins = self::getSkinList($this->basePath, 'load');
        
        $content = $this->blade->render('admin.boards.edit', [
            'title' => '게시판 설정',
            'board' => $board,
            'boardSkins' => $boardSkins,
            'charSkins' => $charSkins,
            'loadSkins' => $loadSkins
        ]);
        $response->getBody()->write($content);
        return $response;
    }

    public function boardUpdate(Request $request, Response $response) {
        $data = $request->getParsedBody();
        $id = $data['id'];
        $customFields = $data['custom_fields'] ?? [];
        $notice = trim($data['notice'] ?? '');

        $cleanFields = [];
        if (is_array($customFields)) {
            foreach ($customFields as $field) {
                if (!empty($field['name'])) {
                    $cleanFields[] = [
                        'name' => trim($field['name']),
                        'type' => $field['type'],
                        'required' => isset($field['required']) ? 1 : 0,
                        'options' => trim($field['options'] ?? '')
                    ];
                }
            }
        }
        
        $jsonFields = !empty($cleanFields) ? json_encode($cleanFields, JSON_UNESCAPED_UNICODE) : null;

        $notice = cleanHtml($notice);

        DB::table('boards')
            ->where('id', $id)
            ->update([
                'title' => trim($data['title']),
                'notice' => $notice,
                'board_skin' => isset($data['board_skin']) ? $data['board_skin'] : "",
                'list_count' => isset($data['list_count']) ? (int)$data['list_count'] : 10,
                'read_level' => isset($data['read_level']) ? (int)$data['read_level'] : 1,
                'write_level' => isset($data['write_level']) ? (int)$data['write_level'] : 1,
                'comment_level' => isset($data['comment_level']) ? (int)$data['comment_level'] : 1,
                'use_secret' => isset($data['use_secret']) ? 1 : 0,
                'use_editor' => isset($data['use_editor']) ? 1 : 0,
                'custom_fields' => $jsonFields
            ]);

        $_SESSION['flash_message'] = '저장되었습니다.';
        $_SESSION['flash_type'] = 'success';
        return $response->withHeader('Location', $this->basePath . '/admin/boards/' . $id)->withStatus(302);

    }

    public function boardDelete(Request $request, Response $response) {
        $data = $request->getParsedBody();

        $menu = DB::table('menus')
        ->where('type', 'board')
        ->where('target_id', $data['id'])
        ->get();
    
        foreach ($menu as $menu) {
            $newSlug = $menu->slug . '_deleted_' . time() . '_' . $menu->id;
        
            DB::table('menus')
                ->where('id', $menu->id)
                ->update([
                    'is_deleted' => 1,
                    'deleted_at' => date('Y-m-d H:i:s'),
                    'slug'       => $newSlug
                ]);
        }

        $board = DB::table('boards')->find($data['id']);
        if ($board) {
            DB::table('boards')
                ->where('id', $data['id'])
                ->update([
                    'is_deleted' => 1,
                    'deleted_at' => date('Y-m-d H:i:s')
                ]);
        }
        
        $_SESSION['flash_message'] = '게시판이 삭제되었습니다.';
        $_SESSION['flash_type'] = 'success';
        return $response->withHeader('Location', $this->basePath . '/admin/boards')->withStatus(302);
    }

    public function boardCopy(Request $request, Response $response) {
        $data = $request->getParsedBody();
        $originId = $data['board_id'];

        $origin = DB::table('boards')->find($originId);
        if (!$origin) {
            $_SESSION['flash_message'] = "원본 게시판을 찾을 수 없습니다.";
            $_SESSION['flash_type'] = 'error';
            return $response->withHeader('Location', $_SERVER['HTTP_REFERER'] ?? '/admin')->withStatus(302);
        }

        $newData = (array)$origin;
        
        unset($newData['id']);
        $newData['title'] = $origin->title . ' (복사본)';
        $newData['created_at'] = date('Y-m-d H:i:s');
        
        DB::table('boards')->insert($newData);

        $_SESSION['flash_message'] = "게시판이 복제되었습니다.";
        $_SESSION['flash_type'] = 'success';
        return $response->withHeader('Location', $_SERVER['HTTP_REFERER'] ?? '/admin')->withStatus(302);
    }

    public function emoticonList(Request $request, Response $response) {
        $emoticon = DB::table('emoticons')
        ->get();

        $content = $this->blade->render('admin.emoticons.index', [
        'emoticons' => $emoticon
        ]);
        $response->getBody()->write($content);
        return $response;
    }
    
    public function emoticonStore(Request $request, Response $response) {
        $data = $request->getParsedBody();
        $files = $request->getUploadedFiles();

        $code = trim($data['code'] ?? '');
        $uploadedFile = $files['image'] ?? null;

        if (empty($code) || !$uploadedFile || $uploadedFile->getError() !== UPLOAD_ERR_OK) {
            $_SESSION['flash_message'] = '예약어와 이미지를 모두 입력해주세요.';
            $_SESSION['flash_type'] = 'error';
            return $response->withHeader('Location', $_SERVER['HTTP_REFERER'] ?? '/admin')->withStatus(302);
        }

        if (DB::table('emoticons')->where('code', $code)->exists()) {
            $_SESSION['flash_message'] = '이미 등록된 예약어입니다.';
            $_SESSION['flash_type'] = 'error';
            return $response->withHeader('Location', $_SERVER['HTTP_REFERER'] ?? '/admin')->withStatus(302);
        }

        $directory = __DIR__ . '/../../../public/data/uploads/emoticons';
        if (!is_dir($directory)) {
            @mkdir($directory, 0777, true);
        }


        $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
        $filename = 'emo_' . uniqid() . '.' . $extension;
        
        $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

        DB::table('emoticons')->insert([
            'code' => $code,
            'image_path' => '/public/data/uploads/emoticons/' . $filename,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $_SESSION['flash_message'] = '이모티콘이 등록되었습니다.';
        $_SESSION['flash_type'] = 'success';
        return $response->withHeader('Location', $_SERVER['HTTP_REFERER'] ?? '/admin')->withStatus(302);
    }

    public function emoticonDelete(Request $request, Response $response) {
        $data = $request->getParsedBody();
        $id = $data['id'];

        $emoticon = DB::table('emoticons')->find($id);

        if ($emoticon) {
            $filePath = __DIR__ . '/../../../public/data/uploads/emoticons/' . $emoticon->image_path;
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            DB::table('emoticons')->delete($id);
        }

        $_SESSION['flash_message'] = '삭제처리되었습니다.';
        $_SESSION['flash_type'] = 'success';
        return $response->withHeader('Location', $_SERVER['HTTP_REFERER'] ?? '/admin')->withStatus(302);
    }

    private function getSkinList($basePath, $type = 'document') {
        $skinDir = __DIR__ . '/../../../public/skins/'.$type;
        $skins = [];
    
        if (is_dir($skinDir)) {
            $folders = scandir($skinDir);
            foreach ($folders as $folder) {
                if ($folder === '.' || $folder === '..') continue;
                if (!is_dir($skinDir . '/' . $folder)) continue;
    
                $skinInfo = [
                    'id' => $folder,
                    'name' => $folder,
                    'description' => '설명 파일이 없습니다.'
                ];
    
                $configFile = $skinDir . '/' . $folder . '/skin.json';
                if (file_exists($configFile)) {
                    $config = json_decode(file_get_contents($configFile), true);
                    if ($config) {
                        $skinInfo['name'] = $config['name'] ?? $folder;
                        $skinInfo['description'] = $config['description'] ?? '';
                    }
                }
    
                $skins[] = $skinInfo;
            }
        }
        return $skins;
    }

}