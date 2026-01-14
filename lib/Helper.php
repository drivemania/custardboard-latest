<?php
use Illuminate\Database\Capsule\Manager as DB;

class Helper {
    /**
     * 본문 내용 중 http://, https:// 와 같은 텍스트 링크를 자동으로 <a> 태그로 변환합니다.
     * [사용 예시] {!! Helper::auto_link($document->content) !!}
     * @param string $text 원본 내용
     * @return string 링크가 변환된 HTML 문자열
     */
    public static function auto_link($text) {
        $pattern = '/(?<!src=["\'])(?<!href=["\'])(http|https|ftp):\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/';
        $replacement = '<a href="$0" target="_blank" class="custard-auto-link">$0</a>';
        return preg_replace($pattern, $replacement, $text);
    }

    /**
     * 본문 내용 중 해시태그(#태그)를 해당 게시판의 검색 링크로 변환합니다.
     * [사용 예시] {!! Helper::auto_hashtag($text, $base_path.'/board_slug') !!}
     * @param string $text 원본 내용
     * @param string $currentUrl 링크를 연결할 현재 게시판의 목록 URL
     * @return string 해시태그가 링크로 변환된 HTML 문자열
     */
    public static function auto_hashtag($text, $currentUrl) {
        $pattern = '/(?<!\w)#([a-zA-Z0-9_가-힣]+)/u';
        $replacement = '<a href="'.$currentUrl.'?search_target=hashtag&keyword=$1" target="_blank" class="custard-hashtag">#$1</a>';
        return preg_replace($pattern, $replacement, $text);
    }

    /**
     * 본문 내용 중 [[강조 텍스트]] 또는 @캐릭터ID 형태의 호출/앵커를 감지하여 스타일 및 링크를 적용합니다.
     * [사용 예시] {!! Helper::auto_summon($text, $currentUrl) !!}
     * @param string $text 원본 내용
     * @param string $currentUrl 링크를 연결할 기준 URL
     * @return string 호출 태그가 변환된 HTML 문자열
     */
    public static function auto_summon($text, $currentUrl) {
        if (preg_match_all('/\[\[(.*?)\]\]/', $text, $matches)) {
            foreach ($matches[1] as $value) {
                $text = str_replace("[[{$value}]]", "<span style=\"background-color:rgb(255, 242, 170); margin: auto 1px; padding: 0px 2px\">🔔<b>{$value}</b></span>", $text);
            }
        }
        if (preg_match_all('/@(\d+)/', $text, $matches2)) {
            foreach ($matches2[1] as $value) {
                $replacement = '<a href="'.$currentUrl.'/$1" target="_blank" class="custard-hashtag">@$1</a>';
                $text = preg_replace('/@(\d+)/', $replacement, $text);
            }
        }
        return $text;
    }

    /**
     * 특정 유저가 특정 그룹에서 설정한 '대표 캐릭터' 정보를 불러옵니다.
     * [사용 예시] $mainChar = Helper::getMyMainChr($_SESSION['user_idx'], $group->id);
     * @param int|string $mid 유저의 고유 ID (user_id)
     * @param int|string $gid 그룹의 고유 ID (group_id)
     * @return object|null 캐릭터 ID, 이미지, 이름, 메뉴 슬러그 객체 (없으면 null)
     */
    public static function getMyMainChr($mid, $gid) {
        $results = DB::table('characters')
            ->join('menus', 'characters.board_id', '=', 'menus.target_id')
            
            ->where('characters.is_deleted', 0)
            ->where('characters.is_main', 1)
            ->where('characters.group_id', $gid)
            ->where('characters.user_id', $mid)
            ->where('menus.group_id', $gid)
            ->where('menus.is_deleted', 0)
            ->select([
                'characters.id', 
                'characters.image_path', 
                'characters.name',
                'menus.slug as menu_slug'
            ])
            ->first();
        return $results;
    }

    /**
     * CSS, JS 등의 정적 파일 경로에 파일 수정 시간(Timestamp)을 붙여 캐시 갱신을 유도합니다.
     * [사용 예시] <link href="{{ Helper::asset('css/style.css') }}" rel="stylesheet">
     * @param string $path 파일의 상대 경로
     * @return string 버전(?v=12345678)이 붙은 경로
     */
    public static function asset($path) {
        $realPath = __DIR__ . '/../../' . $path;
        
        if (file_exists($realPath)) {
            $ver = filemtime($realPath);
            return $path . '?v=' . $ver;
        }
        
        return $path;
    }

    /**
     * 날짜/시간을 '방금 전', 'O분 전', 'O시간 전' 등의 상대적 시간으로 변환합니다.
     * [사용 예시] {{ Helper::time_ago($document->created_at) }}
     * @param string $datetime Y-m-d H:i:s 형식의 날짜 문자열
     * @return string 변환된 상대 시간 문자열
     */
    public static function time_ago($datetime) {
        $time = strtotime($datetime);
        $diff = time() - $time;

        if ($diff < 60) return '방금 전';
        if ($diff < 3600) return floor($diff / 60) . '분 전';
        if ($diff < 86400) return floor($diff / 3600) . '시간 전';
        if ($diff < 172800) return '어제';
        if ($diff < 2592000) return floor($diff / 86400) . '일 전';
        
        return date('Y-m-d', $time);
    }

    /**
     * 문자열이 지정된 길이를 초과하면 자르고 말줄임표(...)를 붙입니다.
     * [사용 예시] {{ Helper::truncate($document->title, 20) }}
     * @param string $string 자를 원본 문자열
     * @param int $length 남길 최대 글자 수 (기본 30자)
     * @param string $append 잘렸을 때 뒤에 붙일 문자열 (기본 '...')
     * @return string
     */
    public static function truncate($string, $length = 30, $append = '...') {
        $string = strip_tags($string);
        
        if (mb_strlen($string, 'UTF-8') <= $length) {
            return $string;
        }
        return mb_substr($string, 0, $length, 'UTF-8') . $append;
    }

    /**
     * 아이콘 이미지 태그를 생성합니다. 이미지가 없으면 기본 대체 박스를 출력합니다.
     * [사용 예시] {!! Helper::icon($base_path, $item->icon_path, 'w-10 h-10', 'No Img') !!}
     * @param string $basePath 사이트 기본 경로 ($base_path 변수)
     * @param string|null $iconPath DB에 저장된 이미지 경로
     * @param string $cssClass <img> 또는 대체 박스에 적용할 Tailwind CSS 클래스
     * @param string $fallbackText 이미지가 없을 때 띄울 내용, 이모지 가능 (기본 'No Img')
     * @return string HTML 태그 문자열
     */
    public static function icon($basePath, $iconPath, $cssClass = 'w-10 h-10', $fallbackText = 'No Img') {
        if (!empty($iconPath)) {
            $src = rtrim($basePath, '/') . '/' . ltrim($iconPath, '/');
            return "<img src=\"{$src}\" class=\"{$cssClass} object-cover rounded border bg-neutral-100\">";
        }
        
        return "<div class=\"{$cssClass} rounded border bg-neutral-100 flex items-center justify-center text-xs text-neutral-400\">{$fallbackText}</div>";
    }
}