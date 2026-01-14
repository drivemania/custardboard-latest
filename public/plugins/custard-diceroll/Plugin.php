<?php
namespace Plugins\CustardDiceroll;

use App\Support\Hook;
use App\Support\PluginHelper;

class DiceState {
    public static $result = null;
}

Hook::add('before_comment_save', function($data) {
    if ($data['custard_diceroll_chk'] == 1) {
        DiceState::$result = [rand(1, 6), rand(1, 6)];
    }

    return $data;
});

Hook::add('after_comment_save', function($id) {

    if (DiceState::$result !== null) {
        list($r1, $r2) = DiceState::$result;

        $diceHtml = '
        <div class="custard-dice-box">
            <span class="custard-dice-icon">🎲</span>
            <span class="custard-dice-text">주사위를 굴려 <strong>'.$r1.', '.$r2.'</strong>이(가) 나왔습니다!</span>
        </div>';
        PluginHelper::saveCommentMeta('custardDiceroll', $id, 'result', $diceHtml);
        
        DiceState::$result = null;
    }

});

Hook::add('comment_content_list', function(){
    echo '<label class="custard-dice-label">
          <input type="checkbox" name="custard_diceroll_chk" value="1">
          주사위</label>';
});

Hook::add('layout_head', function() {
    echo '<style>
        .custard-dice-box {
            display: block;
            background: #f3f4f6;
            border: 1px solid #d1d5db;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 0.9em;
            color: #374151;
            margin-top: 5px;
        }
        .custard-dice-label { font-size: 0.875rem; padding-left: 3px; padding-right:15px; }
        .custard-dice-icon { font-size: 1.2em; margin-right: 5px; }
        .custard-dice-text strong { color: #4f46e5; font-size: 1.1em; }
    </style>';
});