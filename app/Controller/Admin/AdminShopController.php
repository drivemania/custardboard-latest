<?php
namespace App\Controller\Admin;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Illuminate\Database\Capsule\Manager as DB;

class AdminShopController {

    protected $blade;
    protected $basePath;
    protected $returnUrl;

    public function __construct($blade, $basePath)
    {
        $this->blade = $blade;
        $this->basePath = $basePath;
        $this->returnUrl = "";
    }

    public function itemList(Request $request, Response $response) {
        $items = DB::table('items')->where('is_deleted', 0)->orderBy('id', 'desc')->get();
        $content = $this->blade->render('admin.items.index', [
            'items' => $items
        ]);
        $response->getBody()->write($content);
        return $response;
    }

    public function itemStore(Request $request, Response $response) {
        $data = $request->getParsedBody();
        $files = $request->getUploadedFiles();

        $effectType = $data['effect_type'];
        $effectData = null;

        if ($effectType === 'lottery') {
            $effectData = json_encode([
                'min_point' => (int)$data['lottery_min'],
                'max_point' => (int)$data['lottery_max']
            ]);
        } elseif ($effectType === 'random_box') {
            $effectData = $data['random_box_json'];
        }
        
        $iconPath = $data['existing_icon_path'] ?? null;
        if (isset($files['icon']) && $files['icon']->getError() === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../../public/data/uploads/items';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            
            $filename = uniqid() . '_' . $files['icon']->getClientFilename();
            $files['icon']->moveTo($uploadDir . '/' . $filename);
            $iconPath = '/public/data/uploads/items/' . $filename;
        }

        $saveData = [
            'name' => $data['name'],
            'description' => $data['description'],
            'icon_path' => $iconPath,
            'effect_type' => $effectType,
            'effect_data' => $effectData,
            'is_sellable' => isset($data['is_sellable']) ? 1 : 0,
            'sell_price' => (int)$data['sell_price'],
        ];

        if (!empty($data['id'])) {
            DB::table('items')->where('id', $data['id'])->update($saveData);
            $msg = '아이템이 수정되었습니다.';
        } else {
            DB::table('items')->insert($saveData);
            $msg = '아이템이 생성되었습니다.';
        }

        $_SESSION['flash_message'] = $msg;
        $_SESSION['flash_type'] = 'success';
        return $response->withHeader('Location', $_SERVER['HTTP_REFERER'] ?? '/admin')->withStatus(302);
    }

    public function itemDelete(Request $request, Response $response) {
        $data = $request->getParsedBody();
        DB::table('character_items')->where('item_id', $data['id'])->update([
            'is_deleted' => 1,
            'deleted_at' => date('Y-m-d H:i:s')
        ]);

        DB::table('items')->where('id', $data['id'])->update([
            'is_deleted' => 1,
            'deleted_at' => date('Y-m-d H:i:s')
        ]);
        $_SESSION['flash_message'] = '삭제처리되었습니다.';
        $_SESSION['flash_type'] = 'success';
        return $response->withHeader('Location', $_SERVER['HTTP_REFERER'] ?? '/admin')->withStatus(302);
    }

    public function settlementList(Request $request, Response $response) {
        $groups = DB::table('groups')->where('is_deleted', 0)->orderBy('created_at', 'desc')->get();
        
        $content = $this->blade->render('admin.settlements.index', [
            'groups' => $groups
        ]);
        $response->getBody()->write($content);
        return $response;
    }

    public function settlementManage(Request $request, Response $response, $args) {
        $groupId = $args['group_id'];
        $group = DB::table('groups')->find($groupId);

        if (!$group) {
            return $response->withHeader('Location', $this->basePath . '/admin/settlements')->withStatus(302);
        }

        $items = DB::table('items')->where('is_deleted', 0)->get();

        $customFields = [];
        if ($group->use_fixed_char_fields && !empty($group->char_fixed_fields)) {
            $customFields = json_decode($group->char_fixed_fields, true);
        }

        $characters = DB::table('characters')
            ->join('users', 'characters.user_id', '=', 'users.id')
            ->where('characters.group_id', $groupId)
            ->where('characters.is_deleted', 0)
            ->select('characters.id', 'characters.name', 'users.nickname as owner_name')
            ->get();

        $content = $this->blade->render('admin.settlements.manage', [
            'group' => $group,
            'items' => $items,
            'characters' => $characters,
            'customFields' => $customFields
        ]);
        $response->getBody()->write($content);
        return $response;
    }

    public function settlementDist(Request $request, Response $response) {
        $data = $request->getParsedBody();
        
        $groupId = $data['group_id'];
        $targetType = $data['target_type'];
        $pointAmount = (int)($data['point_amount'] ?? 0);
        $itemIds = $data['item_ids'] ?? [];
        $reason = trim($data['reason']);

        if (empty($reason)) {
            $_SESSION['flash_message'] = '지급 사유를 입력해주세요.';
            $_SESSION['flash_type'] = 'error';
            return $response->withHeader('Location', $_SERVER['HTTP_REFERER'])->withStatus(302);
        }

        $query = DB::table('characters')
            ->where('group_id', $groupId)
            ->where('is_deleted', 0);

        if ($targetType === 'selection') {
            $selectedIds = $data['selected_chars'] ?? [];
            if (empty($selectedIds)) {
                $_SESSION['flash_message'] = '캐릭터를 선택해주세요.';
                $_SESSION['flash_type'] = 'error';
                return $response->withHeader('Location', $_SERVER['HTTP_REFERER'])->withStatus(302);
            }
            $query->whereIn('id', $selectedIds);

        } elseif ($targetType === 'filter') {
            $filters = $data['filters'] ?? [];
            foreach ($filters as $key => $val) {
                if (!empty($val)) {
                    $query->whereJsonContains('info->' . $key, $val);
                }
            }
        }

        $targets = $query->select('id', 'user_id', 'name')->get();

        if ($targets->isEmpty()) {
            $_SESSION['flash_message'] = '지급 대상이 없습니다.';
            $_SESSION['flash_type'] = 'error';
            return $response->withHeader('Location', $_SERVER['HTTP_REFERER'])->withStatus(302);
        }

        if ($pointAmount != 0) {
            $userIds = $targets->pluck('user_id')->unique();
            
            DB::table('users')->whereIn('id', $userIds)->increment('user_point', $pointAmount);
        }

        $gaveItems = [];
        if (!empty($itemIds)) {
            $insertData = [];
            
            $gaveItems = DB::table('items')->whereIn('id', $itemIds)->select('id', 'name')->get()->toArray();

            $targetIds = $targets->pluck('id')->toArray();

            $existingItems = DB::table('character_items')
                ->whereIn('character_id', $targetIds)
                ->whereIn('item_id', $itemIds)
                ->get();

            $existingMap = [];
            foreach ($existingItems as $row) {
                $existingMap[$row->character_id][$row->item_id] = $row->id;
            }

            $incrementIds = [];
            $insertData = [];

            foreach ($targets as $char) {
                foreach ($itemIds as $itemId) {
                    if (isset($existingMap[$char->id][$itemId])) {
                        $incrementIds[] = $existingMap[$char->id][$itemId];
                    } else {
                        $insertData[] = [
                            'character_id' => $char->id,
                            'item_id' => $itemId,
                            'quantity' => 1
                        ];
                    }
                }
            }

            if (!empty($incrementIds)) {
                DB::table('character_items')
                    ->whereIn('id', $incrementIds)
                    ->increment('quantity');
            }

            if (!empty($insertData)) {
                foreach (array_chunk($insertData, 1000) as $chunk) {
                    DB::table('character_items')->insert($chunk);
                }
            }
        }

        DB::table('settlement_logs')->insert([
            'group_id' => $groupId,
            'admin_id' => $_SESSION['user_idx'],
            'target_count' => $targets->count(),
            'target_list' => !empty($targets) ? json_encode($targets, JSON_UNESCAPED_UNICODE) : null,
            'point_amount' => $pointAmount,
            'items_json' => !empty($gaveItems) ? json_encode($gaveItems, JSON_UNESCAPED_UNICODE) : null,
            'reason' => $reason,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $_SESSION['flash_message'] = "총 {$targets->count()}명(캐릭터 기준)에게 지급이 완료되었습니다.";
        $_SESSION['flash_type'] = 'success';
        return $response->withHeader('Location', $_SERVER['HTTP_REFERER'])->withStatus(302);
    
    }

}