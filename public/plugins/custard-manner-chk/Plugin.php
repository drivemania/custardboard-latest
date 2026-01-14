<?php

namespace Plugins\CustardMannerChk;

use App\Support\Hook;
use App\Support\PluginHelper;

Hook::add('plugin_menu', function() {
    $pluginName = 'custardMannerChk';
    echo '<li><a href="'.PluginHelper::getBasePath().'/admin/plugins/'.$pluginName.'" class="block px-6 py-1 text-gray-300 hover:bg-stone-700 hover:text-white transition">
            댓글 매너 체크 관리</a></li>';
});