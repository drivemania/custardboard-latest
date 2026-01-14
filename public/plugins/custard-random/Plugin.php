<?php

namespace Plugins\CustardRandom;

use App\Support\Hook;
use App\Support\PluginHelper;

class RandomState {
    public static $key = "";
    public static $result = 0;
}

Hook::add('before_comment_save', function($data) {
    if ($data['custard_random_chk'] != "") {
        $settings = PluginHelper::getPluginSettings('custardRandom');
        $lists = $settings['lists'];
        foreach($lists as $value){
            if($value['trigger'] == $data['custard_random_chk']){
                $items = explode("\r\n", $value['items']);
                RandomState::$key = $value['trigger'];
                RandomState::$result = rand(1, count($items));
            }
        }
    }
    return $data;
});

Hook::add('after_comment_save', function($id) {
    $r = RandomState::$result;
    $k = RandomState::$key;
    if ($k != "") {
        $settings = PluginHelper::getPluginSettings('custardRandom');
        foreach($settings['lists'] as $value){
            if($value['trigger'] == $k){
                $items = explode("\r\n", $value['items']);

                $randomHtml = '
                <div class="custard-random-box">
                    '.$items[$r-1].'
                </div>';
        
                PluginHelper::saveCommentMeta('custardRandom', $id, 'result', $randomHtml);
            }
        }
        RandomState::$key = "";
        RandomState::$result = 0;
    }

});

Hook::add('plugin_menu', function() {
    $pluginName = 'custardRandom';
    echo '<li><a href="'.PluginHelper::getBasePath().'/admin/plugins/'.$pluginName.'" class="block px-6 py-1 text-gray-300 hover:bg-stone-700 hover:text-white transition">
            랜덤 출력 메세지 관리</a></li>';
});

Hook::add('comment_content_list', function(){
    $settings = PluginHelper::getPluginSettings('custardRandom');
    foreach($settings['lists'] as $value) {
        echo '<label class="custard-random-label">
        <input type="radio" name="custard_random_chk" value="'.($value['trigger']).'">
        '.$value['keyword'].'</label>';
    }

});

Hook::add('layout_head', function() {
    echo '<style>
        .custard-random-box {
            display: block;
            background: #f3f4f6;
            border: 1px solid #d1d5db;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 0.9em;
            color: #374151;
            margin-top: 5px;
        }
        .custard-random-label { font-size: 0.875rem; padding-left: 3px; padding-right:15px; }
    </style>';
});