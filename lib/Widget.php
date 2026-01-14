<?php
use Illuminate\Database\Capsule\Manager as DB;

class Widget {
    /**
     * 지정된 그룹의 메뉴 목록을 HTML 네비게이션(<nav>) 형태로 출력합니다.
     * 관리자에서 '숨김 처리(is_hidden)'되거나 삭제된 메뉴는 자동으로 제외되며, 현재 접속 중인 메뉴에는 'active' 클래스가 부여됩니다.
     * * [사용 예시]
     * // 블레이드 지시어 사용: @custard_menu('그룹슬러그')
     * // 일반 호출 사용: {!! Widget::menu($base_path, 'my_group') !!}
     * @param string $basePath 사이트 기본 경로 ($base_path 변수)
     * @param string $groupSlug 메뉴를 불러올 대상 그룹의 슬러그 (Slug)
     * @return string 조립된 <nav> HTML 문자열 (메뉴가 없으면 빈 문자열 반환)
     */
    public static function menu($basePath, $groupSlug) {
        if (!$groupSlug) return '';

        $menus = DB::table('menus')
            ->join('groups', 'menus.group_id', '=', 'groups.id')
            ->where('groups.slug', $groupSlug)
            ->where('menus.is_deleted', 0)
            ->where('menus.is_hidden', 0)
            ->orderBy('menus.order_num', 'asc')
            ->select('menus.*')
            ->get();

        if ($menus->isEmpty()) return '';

        $html = '<nav class="custard-menu-widget">';
        $html .= '<ul class="custard-menu-list">';

        foreach ($menus as $m) {
            $group = DB::table('groups')->find($m->group_id);

            $a_target = "";
            if($m->type == 'link'){
                $link = $m->target_url;
                $a_target = 'target="_blank"';
            }elseif($m->type == 'shop'){
                $link = "{$basePath}/au/{$group->slug}/shop/{$m->target_id}"; 
            }else{
                $link = "{$basePath}/au/{$group->slug}/{$m->slug}"; 
                if($group->is_default === 1){
                    $link = "{$basePath}/{$m->slug}"; 
                }
            }

            $currentUri = parse_url($_SERVER['REQUEST_URI'])['path'] ?? '';
            $isActive = '';
            if(strpos($currentUri, $m->slug) !== false){
                if($m->type != 'link'){
                    if($m->type != 'shop') {
                        $isActive = ' active';
                    }elseif($m->type == 'shop' && strpos($currentUri, 'shop/' . $m->target_id)){
                        $isActive = ' active';
                    }
                }
            }

            $html .= '<li class="custard-menu-item' . $isActive . '">';
            $html .= '<a href="' . $link . '" class="custard-menu-link" '.$a_target.'>' . htmlspecialchars($m->title) . '</a>';
            $html .= '</li>';
        }

        $html .= '</ul>';
        $html .= '</nav>';

        return $html;
    }
    /**
     * 로그인 상태에 따라 '로그인 폼' 또는 '유저 프로필(대표 캐릭터, 쪽지함 알림)' 위젯을 출력합니다.
     * 로그인된 유저라면 현재 접속 중인 그룹($groupSlug)의 대표 캐릭터 아이콘과 오너 닉네임이 함께 표시됩니다.
     * [사용 예시]
     * // 블레이드 지시어 사용: @custard_login('그룹슬러그')
     * // 일반 호출 사용: {!! Widget::login($base_path, 'my_group') !!}
     * @param string $basePath 사이트 기본 경로 ($base_path 변수)
     * @param string|null $groupSlug 현재 표시 중인 그룹의 슬러그 (선택 사항)
     * @return string 조립된 로그인/프로필 영역 HTML 문자열
     */
    public static function login($basePath, $groupSlug = null) {
        $html = '<div class="custard-login-widget">';
        $charUrl = "";

        if (isset($_SESSION['user_idx'])) {
            $userIdx = $_SESSION['user_idx'] ?? 0;
            $nickname = htmlspecialchars($_SESSION['nickname']);

            $mainChar = null;
            if ($groupSlug && $userIdx) {
                $mainChar = DB::table('characters')
                    ->join('groups', 'characters.group_id', '=', 'groups.id') 
                    ->where('groups.slug', $groupSlug) 
                    ->where('characters.user_id', $userIdx)
                    ->where('characters.is_main', 1)
                    ->select('characters.*') 
                    ->first();
                if(!empty($mainChar)){
                    $menus = DB::table('menus')
                    ->where('target_id', $mainChar->board_id)
                    ->first();
                    $charUrl = 'onclick="location.href=\'';
                    $charUrl .= $basePath."/au/".$groupSlug."/".$menus->slug."/".$mainChar->id;
                    $charUrl .= '\'"';
                    $charUrl .= ' style="cursor: pointer;"';
                }
                
            }

            $html .= '<div x-data="{ count: 0 }" x-init="fetch(\'' . $basePath . '/api/memo/count\').then(r => r.json()).then(d => count = d.count)" class="custard-memo-alert">';
            $html .= '<a href="#" onclick="window.open(\'' . $basePath . '/memo\', \'memo\', \'width=650,height=700\'); return false;" class="custard-memo-link">';
            $html .= '📩 쪽지함 ';
            $html .= '<span x-show="count > 0" class="custard-memo-badge" x-text="count" style="display:none;"></span>';
            $html .= '</a>';
            $html .= '</div>';

            if ($mainChar) {
                $html .= '<div class="custard-main-char">';
                
                $imgSrc = $mainChar->image_path ? $mainChar->image_path : '';
                $html .= '<div class="custard-main-char-img" '.$charUrl.'>';
                $html .= '<img src="' . $imgSrc . '" alt="Main Character">';
                $html .= '</div>';
                
                $html .= '<div class="custard-main-char-text" '.$charUrl.'>';
                $html .= '<span class="custard-main-char-name">' . htmlspecialchars($mainChar->name) . '</span>';
                $html .= '</div>';
                
                $html .= '</div>';
            } else {
                $html .= '<div class="custard-no-char">';
                $html .= '<div class="custard-no-char-icon">😊</div>';
                $html .= '</div>';
            }
            
            $html .= '<div class="custard-login-info">';
            $html .= '오너 : <span class="custard-login-nickname">' . $nickname . '</span>';
            $html .= '</div>';
            
            $html .= '<div class="custard-login-actions">';
            if (($_SESSION['level'] ?? 0) >= 10) {
                $html .= '<a href="' . $basePath . '/admin" class="custard-login-btn-admin" target="_blank">관리자</a>';
            }
            $html .= '<a href="' . $basePath . '/logout" class="custard-login-btn-logout">로그아웃</a>';
            $html .= '<a href="' . $basePath . '/info" class="custard-login-btn-info">내 정보</a>';
            $html .= '</div>';

        } else {
            $html .= '<form action="' . $basePath . '/login" method="POST" class="custard-login-form">';
            $html .= '<div class="custard-login-inputs">';
            $html .= '<input type="text" name="user_id" placeholder="아이디" class="custard-login-input-id">';
            $html .= '<input type="password" name="password" placeholder="비밀번호" class="custard-login-input-pw">';
            $html .= '</div>';
            $html .= '<div class="custard-login-auto-login">';
            $html .= '<input type="checkbox" id="auto_login" name="auto_login">';
            $html .= '<label for="auto_login">자동 로그인</label>';
            $html .= '</div>';
            
            $html .= '<div class="custard-login-btn-wrap">';
            $html .= '<button type="submit" class="custard-login-btn-submit">로그인</button>';
            $html .= '</div>';
            
            $html .= '<div class="custard-login-links">';
            $html .= '<a href="' . $basePath . '/register" class="custard-login-link-register">회원가입</a>';
            $html .= '</div>';
            $html .= '</form>';
        }

        $html .= '</div>';
        return $html;
    }
    /**
     * 게시판의 최신 글과 최신 댓글을 하나의 리스트로 통합하여 출력합니다.
     * 비밀글 및 삭제된 글/댓글은 보안상 자동으로 제외되며, 24시간 이내 작성된 항목에는 'N' 뱃지가 붙습니다.
     * [사용 예시]
     * // 특정 게시판(freeboard, notice)의 최신글 5개만, 제목 15자 제한으로 불러오기
     * {!! Widget::latestPosts($base_path, 5, 15, ['freeboard', 'notice']) !!}
     * @param string $basePath 사이트 기본 경로 ($base_path 변수)
     * @param int $limit 노출할 최신 항목의 총 개수 (기본값: 10)
     * @param int $cutSubject 제목 또는 댓글 내용을 자를 최대 글자 수 (기본값: 20)
     * @param array $menuSlug 특정 메뉴(게시판)만 불러올 경우 슬러그 배열 지정 (비워두면 전체 메뉴 대상)
     * @param array $groupSlug 특정 그룹의 게시물만 불러올 경우 그룹 슬러그 배열 지정 (비워두면 전체 그룹 대상)
     * @return string 조립된 최신글 <ul> HTML 문자열
     */
    public static function latestPosts($basePath, $limit = 10, $cutSubject = 20, $menuSlug = [], $groupSlug = []) {

        $docs = DB::table('documents')
            ->join('menus', function($join) {
                $join->on('documents.board_id', '=', 'menus.target_id')
                    ->on('documents.group_id', '=', 'menus.group_id')
                    ->whereIn('menus.type', array('board', 'load'));
            })
            ->where('documents.is_deleted', 0)
            ->where('menus.is_deleted', 0)
            ->where('documents.is_secret', 0)
            ->select(
                'documents.title as subject',
                'documents.created_at',
                'menus.slug as menu_slug',
                'documents.id as doc_id',
                'documents.doc_num as doc_num',
                'menus.type as menu_type',
                DB::raw("NULL as comment_id"),
                DB::raw("'doc' as type")
                );  

        if (!empty($groupSlug) && count($groupSlug) > 0) {
            $docs->join('groups', 'menus.group_id', '=', 'groups.id')
                ->whereIn('groups.slug', $groupSlug);
        }

        if (!empty($menuSlug) && count($menuSlug) > 0) {
            $docs->whereIn('menus.slug', $menuSlug);
        }

        $comments = DB::table('comments')
            ->join('documents', 'comments.doc_id', '=', 'documents.id')
            ->join('menus', function($join) {
                $join->on('documents.board_id', '=', 'menus.target_id')
                    ->on('documents.group_id', '=', 'menus.group_id')
                    ->whereIn('menus.type', array('board', 'load'));
            })
            ->where('comments.is_deleted', 0)
            ->where('comments.is_secret', 0)
            ->where('menus.is_deleted', 0)
            ->where('documents.is_secret', 0)
            ->where('documents.is_deleted', 0)
            ->select(
                'comments.content as subject',
                'comments.created_at',
                'menus.slug as menu_slug',
                'documents.id as doc_id',
                'documents.doc_num as doc_num',
                'menus.type as menu_type',
                'comments.id as comment_id',
                DB::raw("'cmt' as type")
            );

        if (!empty($groupSlug) && count($groupSlug) > 0) {
            $comments->join('groups', 'menus.group_id', '=', 'groups.id')
                ->whereIn('groups.slug', $groupSlug);
        }

        if (!empty($menuSlug) && count($menuSlug) > 0) {
            $comments->whereIn('menus.slug', $menuSlug);
        }

        $items = $docs->unionAll($comments)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        $groups = DB::table('groups')
            ->where('slug', $groupSlug)
            ->where('is_deleted', 0)
            ->first();


        $html = '<div class="custard-latest-widget">';
        $html .= '<h3 class="custard-latest-title">최신 글 & 댓글</h3>';
        $html .= '<ul class="custard-latest-list">';

        if ($items->isEmpty()) {
            $html .= '<li class="custard-latest-empty">등록된 새 글이 없습니다.</li>';
        } else {
            foreach ($items as $item) {
                $subject = strip_tags($item->subject);
                if (mb_strlen($subject) > $cutSubject) {
                    $subject = mb_substr($subject, 0, $cutSubject) . '...';
                }

                if(mb_strlen($subject) <= 0){
                    $subject = '...';
                }

                $url = "$basePath/au/$groupSlug/$item->menu_slug/$item->doc_num";
                if($groups->is_default > 0){
                    $url = $basePath . '/' . $item->menu_slug . '/' . $item->doc_num;

                }
                if ($item->type === 'cmt') {
                    $url .= '#comment_' . $item->comment_id;
                }

                $date = date('m-d', strtotime($item->created_at));
                
                if (date('Y-m-d') == date('Y-m-d', strtotime($item->created_at))) {
                    $date = date('H:i', strtotime($item->created_at));
                }

                $typeLabel = ($item->type === 'doc') ? '<span class="custard-latest-badge-doc">글</span>' : '<span class="custard-latest-badge-cmt">댓글</span>';

                $html .= '<li class="custard-latest-item">';
                $html .= '<div class="custard-latest-left">';
                $html .= $typeLabel;
                $html .= '<a href="' . $url . '" class="custard-latest-subject">' . htmlspecialchars($subject) . '</a>';

                if (strtotime($item->created_at) > time() - 86400) {
                    $html .= '<span class="custard-latest-new">N</span>';
                }
                $html .= '</div>';
                $html .= '<span class="custard-latest-date">' . $date . '</span>';
                $html .= '</li>';
            }
        }

        $html .= '</ul>';
        $html .= '</div>';

        return $html;
    }
}