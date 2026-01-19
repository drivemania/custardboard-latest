<?php
namespace App\Controller\Admin;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Illuminate\Database\Capsule\Manager as DB;

class AdminCharacterController {

    protected $blade;
    protected $basePath;
    protected $returnUrl;

    public function __construct($blade, $basePath)
    {
        $this->blade = $blade;
        $this->basePath = $basePath;
        $this->returnUrl = "";
    }

    public function characterList(Request $request, Response $response) {
        $page = $_GET['page'] ?? 1;
        $search = $_GET['search'] ?? '';

        $query = DB::table('characters')
            ->join('users', 'characters.user_id', '=', 'users.id')
            ->join('groups', 'characters.group_id', '=', 'groups.id')
            ->leftJoin('boards', 'characters.board_id', '=', 'boards.id')
            ->select(
                'characters.*', 
                'users.nickname as owner_name', 
                'groups.name as group_name',
                'boards.title as board_title'
            )
            ->where('characters.is_deleted', 0);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('characters.name', 'LIKE', "%$search%")
                  ->orWhere('users.nickname', 'LIKE', "%$search%");
            });
        }

        $characters = $query->orderBy('characters.id', 'desc')->paginate(15, ['*'], 'page', $page);
        foreach($characters as $cha){
            if( mb_strlen($cha->name) > 15 ){
                $cha->name = mb_substr($cha->name, 0, 12) . '...';
            }
        }

        $content = $this->blade->render('admin.characters.index', [
            'characters' => $characters,
            'search' => $search
        ]);
        $response->getBody()->write($content);
        return $response;
    }

    public function characterDetail(Request $request, Response $response, $args) {
        $groupId = $args['group_id'];
            
        $boards = DB::table('boards')
            ->join('menus', 'boards.id', '=', 'menus.target_id')
            ->where('menus.group_id', $groupId)
            ->where('boards.type', 'character')
            ->where('boards.is_deleted', 0)
            ->select('boards.id', 'boards.title')
            ->distinct()
            ->get();
            
        $payload = json_encode($boards);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function characterMove(Request $request, Response $response) {
        $data = $request->getParsedBody();
            
        $idsParam = $data['char_ids'] ?? '';
        $targetBoardId = $data['target_board_id'];

        if (empty($idsParam) || empty($targetBoardId)) {
            $_SESSION['flash_message'] = "잘못된 요청입니다.";
            $_SESSION['flash_type'] = 'error';
            return $response->withHeader('Location', $_SERVER['HTTP_REFERER'] ?? '/admin')->withStatus(302);
        }

        $idArray = explode(',', $idsParam);

        DB::table('characters')
            ->whereIn('id', $idArray)
            ->update([
                'board_id' => $targetBoardId,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

        $count = count($idArray);
        $_SESSION['flash_message'] = "{$count}명의 캐릭터가 이동되었습니다.";
        $_SESSION['flash_type'] = 'success';
        return $response->withHeader('Location', $_SERVER['HTTP_REFERER'] ?? '/admin')->withStatus(302);
    }

    public function profileList(Request $request, Response $response) {
        $page = $_GET['page'] ?? 1;

        $groups = DB::table('groups')
            ->where('is_deleted', 0)
            ->orderBy('created_at', 'desc')
            ->paginate(15, ['*'], 'page', $page);

        $content = $this->blade->render('admin.profiles.index', [
            'title' => '프로필 양식 설정',
            'group' => $groups
        ]);
        $response->getBody()->write($content);
        return $response;
    }

    public function profileEdit(Request $request, Response $response, $args) {
        $id = $args['id'];
        $groupData = DB::table('groups')->where('id', $id)->where('is_deleted', 0)->first();

        if (!$groupData) {
            return $response->withHeader('Location', $this->basePath . '/admin/profiles')->withStatus(302);
        }

        $content = $this->blade->render('admin.profiles.edit', [
            'title' => '프로필 양식 설정 - ' . $groupData->name,
            'group' => $groupData
        ]);
        $response->getBody()->write($content);
        return $response;
    }

    public function profileUpdate(Request $request, Response $response) {
        $data = $request->getParsedBody();
        $id = $data['id'];

        $charFields = $data['char_fields'] ?? [];
        $cleanFields = [];
        if (is_array($charFields)) {
            foreach ($charFields as $field) {
                if (!empty($field['name'])) {
                    $cleanFields[] = [
                        'name' => trim($field['name']),
                        'type' => $field['type'],
                        'required' => isset($field['required']) ? 1 : 0,
                        'options' => isset($field['options']) ? $field['options'] : ""
                    ];
                }
            }
        }
        $jsonCharFields = !empty($cleanFields) ? json_encode($cleanFields, JSON_UNESCAPED_UNICODE) : null;

        DB::table('groups')
            ->where('id', $id)
            ->update([
                'use_fixed_char_fields' => isset($data['use_fixed_char_fields']) ? (int)$data['use_fixed_char_fields'] : 0,
                'char_fixed_fields' => $jsonCharFields
            ]);

        $_SESSION['flash_message'] = '프로필 양식 설정이 저장되었습니다.';
        $_SESSION['flash_type'] = 'success';
        
        return $response->withHeader('Location', $this->basePath . '/admin/profiles/' . $id)->withStatus(302);
    }
}