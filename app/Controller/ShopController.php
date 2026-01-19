<?php
namespace App\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;

class ShopController extends Model
{
    protected $blade;
    protected $basePath;
    protected $returnUrl;

    public function __construct($blade, $basePath)
    {
        $this->blade = $blade;
        $this->basePath = $basePath;
        $this->returnUrl = "";
    }

    public function itemUse(Request $request, Response $response, $args) {
        $invId = $args['inv_id'];
    
        $inventory = DB::table('character_items')
            ->join('items', 'character_items.item_id', '=', 'items.id')
            ->where('character_items.id', $invId)
            ->select('character_items.*', 'items.effect_type', 'items.effect_data', 'items.name')
            ->first();
    
        if (!$inventory || $inventory->quantity < 1) {
            return $response->withHeader('Location', $_SERVER['HTTP_REFERER'])->withStatus(302);
        }
        
        $char = DB::table('characters')->find($inventory->character_id);
        if ($char->user_id != $_SESSION['user_idx']) {
            $_SESSION['flash_message'] = "권한이 없습니다.";
            $_SESSION['flash_type'] = 'error';
            return $response->withHeader('Location', $_SERVER['HTTP_REFERER'])->withStatus(302);
        }
    
        if ($inventory->effect_type === 'none') {
            $_SESSION['flash_message'] = "사용할 수 없는 아이템입니다.";
            $_SESSION['flash_type'] = 'error';
            return $response->withHeader('Location', $_SERVER['HTTP_REFERER'])->withStatus(302);
        }
    
        $msg = "{$inventory->name}을(를) 사용했습니다.";

        $group = DB::table('groups')->where('id', $char->group_id)->first();
        $pointName = $group->point_name;

        switch($inventory->effect_type) {
            case 'lottery':{
                $data = json_decode($inventory->effect_data, true);
                $point = rand($data['min_point'], $data['max_point']);
                
                DB::table('users')->where('id', $_SESSION['user_idx'])->increment('user_point', $point);
                $msg = "{$point} {$pointName}를 획득했습니다.";
                break;
            }
            case 'create_item':{
                $data = $request->getParsedBody();
                $uploadedFiles = $request->getUploadedFiles();
        
                if (isset($uploadedFiles['icon']) && $uploadedFiles['icon']->getError() === UPLOAD_ERR_OK) {
                    $uploadDir = __DIR__ . '/../../public/data/uploads/items';
                    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                    
                    $filename = uniqid() . '_' . $uploadedFiles['icon']->getClientFilename();
                    $uploadedFiles['icon']->moveTo($uploadDir . '/' . $filename);
                    $iconPath = '/public/data/uploads/items/' . $filename;
                }else{
                    $_SESSION['flash_message'] = "아이콘은 필수 입력 사항입니다.";
                    $_SESSION['flash_type'] = 'error';
                    return $response->withHeader('Location', $_SERVER['HTTP_REFERER'])->withStatus(302);
                }

                $saveData = [
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'icon_path' => $iconPath,
                    'effect_type' => "none",
                    'effect_data' => null,
                    'is_sellable' => 0,
                    'sell_price' => 0,
                ];
                $newItemId = DB::table('items')->insertGetId($saveData);

                $saveCharData = [
                    'character_id' => $char->id,
                    'item_id' => $newItemId,
                ];

                DB::table('character_items')->insert($saveCharData);

                $msg = '아이템이 생성되었습니다.';
                break;
            }
            case 'random_box':{
                $data = json_decode($inventory->effect_data, true);
                $itemList = [];
                $getItem = [];
                $getItemId = "";

                foreach($data['pool'] as $list){
                    for($i=0; $i<$list['weight']; $i++){
                        $itemList[] = $list['item_id'];
                    }
                }

                $getItemId = $itemList[array_rand($itemList)];

                $getItem = DB::table('items')->where('id', $getItemId)->where('is_deleted', 0)->first();

                if(!$getItem){
                    $_SESSION['flash_message'] = "사용할 수 없는 아이템입니다. 관리자에게 문의하세요.";
                    $_SESSION['flash_type'] = 'error';
                    return $response->withHeader('Location', $_SERVER['HTTP_REFERER'])->withStatus(302);
                }

                $saveCharData = [
                    'character_id' => $char->id,
                    'item_id' => $getItemId,
                ];

                DB::table('character_items')->insert($saveCharData);

                $msg = "{$getItem->name} 아이템을 획득했습니다.";
                break;
            }
        }
    
        if ($inventory->quantity > 1) {
            DB::table('character_items')->where('id', $invId)->decrement('quantity');
        } else {
            DB::table('character_items')
            ->where('id', $invId)
            ->update([
                'is_deleted' => 1,
                'deleted_at' => date('Y-m-d H:i:s')
            ]);
        }

        $_SESSION['flash_message'] = $msg;
        $_SESSION['flash_type'] = 'success';
        return $response->withHeader('Location', $_SERVER['HTTP_REFERER'])->withStatus(302);
    }
    public function itemSell(Request $request, Response $response, $args) {
        $invId = $args['inv_id'];
    
        $inventory = DB::table('character_items')
            ->join('items', 'character_items.item_id', '=', 'items.id')
            ->where('character_items.id', $invId)
            ->select('character_items.*', 'items.is_sellable', 'items.sell_price', 'items.name')
            ->first();
    
        $char = DB::table('characters')->find($inventory->character_id);
        if ($char->user_id != $_SESSION['user_idx']) {
            $_SESSION['flash_message'] = "권한이 없습니다.";
            $_SESSION['flash_type'] = 'error';
            return $response->withHeader('Location', $_SERVER['HTTP_REFERER'])->withStatus(302);
        }
    
        if (!$inventory->is_sellable) {
            $_SESSION['flash_message'] = "판매할 수 없는 아이템입니다.";
            return $response->withHeader('Location', $_SERVER['HTTP_REFERER'])->withStatus(302);
        }
    
        if ($inventory->quantity > 1) {
            DB::table('character_items')->where('id', $invId)->decrement('quantity');
        } else {
            DB::table('character_items')
            ->where('id', $invId)
            ->update([
                'is_deleted' => 1,
                'deleted_at' => date('Y-m-d H:i:s')
            ]);
        }
    
        DB::table('users')->where('id', $_SESSION['user_idx'])->increment('user_point', $inventory->sell_price);
    
        $_SESSION['flash_message'] = "{$inventory->name}을(를) 판매하여 {$inventory->sell_price}P를 획득했습니다.";
        $_SESSION['flash_type'] = 'success';
        return $response->withHeader('Location', $_SERVER['HTTP_REFERER'])->withStatus(302);
    }
}
