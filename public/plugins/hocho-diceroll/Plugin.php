<?php
use App\Support\Hook;

// 1. 댓글 저장 전 검문소 (로직 처리)
Hook::add('before_comment_save', function($data) {
    if (strpos($data['content'], '/주사위') !== false) {
        
        $rand = rand(1, 6);
        $rand2 = rand(1, 6);
        $diceHtml = '
        <div class="hc-dice-box">
            <span class="hc-dice-icon">🎲</span>
            <span class="hc-dice-text">주사위를 굴려 <strong>'.$rand.', '.$rand2.'</strong>이(가) 나왔습니다!</span>
        </div>';

        $data['content'] = str_replace('/주사위', $diceHtml, $data['content']);
    }

    return $data;
});

Hook::add('layout_head', function() {
    echo '<style>
        .hc-dice-box {
            display: inline-block;
            background: #f3f4f6;
            border: 1px solid #d1d5db;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 0.9em;
            color: #374151;
            margin-top: 5px;
        }
        .hc-dice-icon { font-size: 1.2em; margin-right: 5px; }
        .hc-dice-text strong { color: #4f46e5; font-size: 1.1em; }
    </style>';
});