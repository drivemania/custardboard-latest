<?php
namespace App\Support;

use Illuminate\Database\Capsule\Manager as DB;

class PluginHelper {

    private static $basePath = '';

    /**
     * 플러그인 전용 메타데이터를 저장합니다. 배열이나 객체는 자동으로 JSON 변환됩니다.
     * @param string $targetType 대상 종류 (예: 'document', 'comment', 'user', 'character')
     * @param int $targetId 대상의 고유 ID
     * @param string $pluginName 플러그인 고유 식별자
     * @param string $key 저장할 데이터의 키 이름
     * @param mixed $value 저장할 값 (문자열, 숫자, 배열, 객체 모두 가능)
     * @return void
     */
    public static function save(string $targetType, int $targetId, string $pluginName, string $key, $value) {
        if (is_array($value) || is_object($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        DB::table('plugin_meta')->updateOrInsert(
            [
                'target_type' => $targetType,
                'target_id'   => $targetId,
                'plugin_name' => $pluginName,
                'key_name'    => $key
            ],
            ['value' => $value, 'updated_at' => date('Y-m-d H:i:s')]
        );
    }

    /**
     * 플러그인 전용 메타데이터를 불러옵니다. JSON 데이터는 자동으로 배열로 디코딩됩니다.
     * @param string $targetType 대상 종류
     * @param int $targetId 대상의 고유 ID
     * @param string $pluginName 플러그인 고유 식별자
     * @param string $key 불러올 데이터의 키 이름
     * @return mixed 데이터가 없으면 null 반환
     */
    public static function get(string $targetType, int $targetId, string $pluginName, string $key) {
        $row = DB::table('plugin_meta')
                 ->where('target_type', $targetType)
                 ->where('target_id', $targetId)
                 ->where('plugin_name', $pluginName)
                 ->where('key_name', $key)
                 ->first();
                 
        if (!$row) return null;

        $decoded = json_decode($row->value, true);
        return (json_last_error() === JSON_ERROR_NONE) ? $decoded : $row->value;
    }

    /**
     * 플러그인 전용 메타데이터를 저장합니다. 배열이나 객체는 자동으로 JSON 변환됩니다.
     * @param string $targetType 대상 종류 (예: 'document', 'comment', 'user', 'character')
     * @param int $targetId 대상의 고유 ID
     * @param string $pluginName 플러그인 고유 식별자
     * @param string $key 저장할 데이터의 키 이름
     * @param mixed $value 저장할 값 (문자열, 숫자, 배열, 객체 모두 가능)
     * @return void
     */
    public static function saveCommentMeta(string $pluginName, int $commentId, string $key, $value) {
        self::save('comment', $commentId, $pluginName, $key, $value);
    }

    /**
     * 플러그인 전용 메타데이터를 불러옵니다. JSON 데이터는 자동으로 배열로 디코딩됩니다.
     * @param string $targetType 대상 종류
     * @param int $targetId 대상의 고유 ID
     * @param string $pluginName 플러그인 고유 식별자
     * @param string $key 불러올 데이터의 키 이름
     * @return mixed 데이터가 없으면 null 반환
     */
    public static function getCommentMeta(string $pluginName, int $commentId, string $key) {
        return self::get('comment', $commentId, $pluginName, $key);
    }

    /**
     * 플러그인 전용 메타데이터를 저장합니다. 배열이나 객체는 자동으로 JSON 변환됩니다.
     * @param string $targetType 대상 종류 (예: 'document', 'comment', 'user', 'character')
     * @param int $targetId 대상의 고유 ID
     * @param string $pluginName 플러그인 고유 식별자
     * @param string $key 저장할 데이터의 키 이름
     * @param mixed $value 저장할 값 (문자열, 숫자, 배열, 객체 모두 가능)
     * @return void
     */
    public static function saveDocumentMeta(string $pluginName, int $postId, string $key, $value) {
        self::save('document', $postId, $pluginName, $key, $value);
    }

    /**
     * 플러그인 전용 메타데이터를 불러옵니다. JSON 데이터는 자동으로 배열로 디코딩됩니다.
     * @param string $targetType 대상 종류
     * @param int $targetId 대상의 고유 ID
     * @param string $pluginName 플러그인 고유 식별자
     * @param string $key 불러올 데이터의 키 이름
     * @return mixed 데이터가 없으면 null 반환
     */
    public static function getDocumentMeta(string $pluginName, int $postId, string $key) {
        return self::get('document', $postId, $pluginName, $key);
    }

    /**
     * 플러그인 전용 메타데이터를 저장합니다. 배열이나 객체는 자동으로 JSON 변환됩니다.
     * @param string $targetType 대상 종류 (예: 'document', 'comment', 'user', 'character')
     * @param int $targetId 대상의 고유 ID
     * @param string $pluginName 플러그인 고유 식별자
     * @param string $key 저장할 데이터의 키 이름
     * @param mixed $value 저장할 값 (문자열, 숫자, 배열, 객체 모두 가능)
     * @return void
     */
    public static function saveUserMeta(string $pluginName, int $userId, string $key, $value) {
        self::save('user', $userId, $pluginName, $key, $value);
    }

    /**
     * 플러그인 전용 메타데이터를 불러옵니다. JSON 데이터는 자동으로 배열로 디코딩됩니다.
     * @param string $targetType 대상 종류
     * @param int $targetId 대상의 고유 ID
     * @param string $pluginName 플러그인 고유 식별자
     * @param string $key 불러올 데이터의 키 이름
     * @return mixed 데이터가 없으면 null 반환
     */
    public static function getUserMeta(string $pluginName, int $userId, string $key) {
        return self::get('user', $userId, $pluginName, $key);
    }

    /**
     * 특정 타겟의 플러그인 메타데이터를 삭제합니다.
     * @param string $targetType 대상 종류
     * @param int $targetId 대상 고유 ID
     * @param string $pluginName 플러그인 식별자
     * @param string|null $key 특정 키만 삭제. null일 경우 해당 플러그인의 모든 메타 삭제
     */
    public static function deleteMeta(string $targetType, int $targetId, string $pluginName, string $key = null) {
        $query = DB::table('plugin_meta')
            ->where('target_type', $targetType)
            ->where('target_id', $targetId)
            ->where('plugin_name', $pluginName);
            
        if ($key !== null) {
            $query->where('key_name', $key);
        }
        $query->delete();
    }

    /**
     * 특정 유저의 정보를 가져오거나, 전체 유저 목록을 반환합니다.
     * [사용 예시] $user = PluginHelper::getUserInfo('test_user');
     * @param string $userId 특정 유저의 ID (문자열). 비워둘 경우 전체 유저 컬렉션 반환
     * @return \Illuminate\Support\Collection|object|null
     */
    public static function getUserInfo(string $userId="") {
        $user = DB::table('users')->select('user_id', 'nickname', 'level', 'email', 'birthdate', 'user_point', 'last_login_at')
            ->where('is_deleted', 0);
        if($userId != ""){
            $user = $user->where('user_id', $userId)->first();
        }else{
            $user = $user->get();
        }
        return $user;
    }

    /**
     * 특정 캐릭터의 상세 정보 및 프로필 데이터를 가져옵니다.
     * @param string|int $charId 캐릭터의 고유 ID
     * @return object|null 캐릭터가 없거나 삭제된 경우 null
     */
    public static function getCharacterInfo(string $charId) {
        $user = DB::table('characters')->select('id', 'group_id', 'user_id', 'name', 'image_path', 'image_path2', 'description', 'profile_data', 'relationship', 'is_main')
            ->where('id', $charId)
            ->where('is_deleted', 0)
            ->first();
        return $user;
    }

    /**
     * 게시판 목록을 가져오거나 특정 게시판의 설정 정보를 가져옵니다.
     * (캐릭터, 페이지 타입은 제외됩니다.)
     * @param string|int $boardId 게시판 ID. 비워둘 경우 전체 목록 반환
     * @return \Illuminate\Support\Collection
     */
    public static function getBoardList($boardId = "") {
        $document = DB::table('boards')
            ->select([
                'id',
                'title',
                'type',
                'read_level',
                'write_level',
                'use_secret',
                'use_editor',
                'custom_fields'
            ])
            ->where('is_deleted', 0)
            ->whereNotIn('type', ['character', 'page']);
        if($boardId != ""){
            $document = $document->where('id', $boardId);
        }
        
        return $document->orderBy('id', 'asc')->get();
    }

    /**
     * 특정 기간 동안 특정 유저가 특정 게시판에 작성한 게시글 목록을 불러옵니다.
     * @param string $userId 작성자 유저 ID
     * @param string $boardId 게시판 ID
     * @param array $date [시작일, 종료일] 배열 (형식: YmdHis). 비워두면 현재 시간 기준
     * @param string $orderBy 정렬 기준 (기본값: 'asc')
     * @return \Illuminate\Support\Collection
     */
    public static function getUserDocumentList(string $userId, string $boardId, array $date, $orderBy = 'asc') {
        $userIdx = DB::table('users')->select('id')->where('user_id', $userId)->where('is_deleted', 0)->first();

        $document = DB::table('documents')->select('id', 'group_id', 'board_id', 'nickname', 'title', 'content', 'custom_data', 'hit', 'comment_count', 'doc_num', 'created_at')
            ->where('user_id', $userIdx->id)
            ->where('board_id', $boardId)
            ->where('is_deleted', 0);

        if(count($date) > 0){
            $startDt = $date[0] ?? date("YmdHis");
            $endDt = $date[1] ?? date("YmdHis");
            $document = $document->whereBetween('created_at', [$startDt, $endDt]);
        }

        return $document->orderBy('id', $orderBy)->get();
    }

    /**
     * 특정 기간 동안 특정 유저가 특정 게시판에 작성한 게시글 목록을 불러옵니다.
     * @param string $docNum 게시판 글 번호
     * @param string $boardId 게시판 ID
     * @param array $date [시작일, 종료일] 배열 (형식: YmdHis). 비워두면 현재 시간 기준
     * @param string $orderBy 정렬 기준 (기본값: 'asc')
     * @return \Illuminate\Support\Collection
     */
    public static function getDocumentData(string $docNum, string $boardId, array $date) {
        $document = DB::table('documents')
            ->leftJoin('users', 'documents.user_id', '=', 'users.id')
            ->select(
                'documents.id', 
                'documents.group_id', 
                'documents.board_id', 
                'users.user_id', 
                'documents.nickname', 
                'documents.title', 
                'documents.content', 
                'documents.custom_data', 
                'documents.hit', 
                'documents.comment_count', 
                'documents.doc_num',
                'documents.created_at')
            ->where('documents.doc_num', $docNum)
            ->where('documents.board_id', $boardId)
            ->where('documents.is_deleted', 0);

        if(count($date) > 0){
            $startDt = $date[0] ?? date("YmdHis");
            $endDt = $date[1] ?? date("YmdHis");
            $document = $document->whereBetween('created_at', [$startDt, $endDt]);
        }

        return $document->first();
    }

    /**
     * 특정 게시판에 작성한 댓글 목록을 불러옵니다.
     * @param string $docId 게시판 글 번호
     * @param string $orderBy 정렬 기준 (기본값: 'asc')
     * @return \Illuminate\Support\Collection
     */
    public static function getDocumentCommentData(string $docId, $orderBy = 'asc') {
        $comment = DB::table('comments')
            ->leftJoin('users', 'comments.user_id', '=', 'users.id')
            ->select(
                'comments.id', 
                'comments.board_id', 
                'comments.doc_id', 
                'users.user_id', 
                'comments.nickname', 
                'comments.content',
                'comments.created_at')
            ->where('comments.doc_id', $docId)
            ->where('comments.is_deleted', 0)
            ->orderBy('comments.id', $orderBy)
            ->get();
        return $comment;
    }

    /**
     * 플러그인의 고유 설정값(JSON)을 불러옵니다.
     * [사용 예시] $settings = PluginHelper::getPluginSettings('my_plugin');
     * @param string $pluginName 플러그인 이름
     * @return array 설정 데이터 배열. 설정이 없으면 빈 배열([]) 반환
     */
    public static function getPluginSettings(string $pluginName) {
        $row = DB::table('plugins')
                 ->where('name', $pluginName) 
                 ->first();

        if (!$row || empty($row->settings)) {
            return [];
        }

        $decoded = json_decode($row->settings, true);
        return (json_last_error() === JSON_ERROR_NONE) ? $decoded : [];
    }

    /**
     * 플러그인의 고유 설정값(JSON)을 저장합니다.
     * @param string $pluginName 플러그인 이름
     * @param array  $data 설정 데이터 배열
     */
    public static function savePluginSettings(string $pluginName, array $data) {
        $currentSettings = self::getPluginSettings($pluginName);
        
        $newSettings = array_merge($currentSettings, $data);

        DB::table('plugins')
            ->where('name', $pluginName)
            ->update([
                'settings' => json_encode($newSettings, JSON_UNESCAPED_UNICODE),
            ]);
    }

    public static function setBasePath($path) {
        self::$basePath = rtrim($path, '/');
    }

    /**
     * 현재 CUSTARDBOARD가 설치된 디렉터리의 Path를 반환합니다.
     * @return string
     */
    public static function getBasePath() {
        return self::$basePath;
    }

    /**
     * 특정 게시판에서 특정 글 다음에 작성된 최신 글 목록을 불러옵니다.
     * @param string $boardId 게시판 ID
     * @param int $currentDocNum 기준이 되는 현재 글 번호
     * @param int $limit 불러올 개수
     * @return \Illuminate\Support\Collection
     */
    public static function getNextDocuments(string $boardId, int $currentDocNum, int $limit) {
        return DB::table('documents')
            ->select('id', 'doc_num', 'title', 'created_at')
            ->where('board_id', $boardId)
            ->where('doc_num', '>', $currentDocNum)
            ->where('is_deleted', 0)
            ->orderBy('doc_num', 'asc') // 다음 글이므로 오름차순
            ->limit($limit)
            ->get();
    }

    /**
     * 특정 게시판에서 특정 글 이전에 작성된 최신 글 목록을 불러옵니다.
     * @param string $boardId 게시판 ID
     * @param int $currentDocNum 기준이 되는 현재 글 번호
     * @param int $limit 불러올 개수
     * @return \Illuminate\Support\Collection
     */
    public static function getPrevDocuments(string $boardId, int $currentDocNum, int $limit) {
        return DB::table('documents')
            ->select('id', 'doc_num', 'title', 'created_at') // 필요한 최소 컬럼만
            ->where('board_id', $boardId)
            ->where('doc_num', '<', $currentDocNum)
            ->where('is_deleted', 0)
            ->orderBy('doc_num', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * 특정 게시글 목록 중에서, 특정 유저가 댓글을 남긴 게시글의 ID 목록만 추려냅니다.
     * [사용 예시] 
     * $commentedDocs = PluginHelper::getUserCommentedDocIds(1, [15, 16, 17, 18]);
     * // 결과: [15, 18] (15번, 18번 글에 댓글을 달았을 경우)
     * @param int $userId 확인할 유저의 고유 ID
     * @param array $docIds 검사할 대상 게시글 ID 배열
     * @return array 유저가 댓글을 단 게시글 ID 배열. 빈 배열을 넣으면 빈 배열([])을 반환합니다.
     */
    public static function getUserCommentedDocIds(int $userId, array $docIds) {
        if (empty($docIds)) return [];

        return DB::table('comments')
            ->whereIn('doc_id', $docIds)
            ->where('user_id', $userId)
            ->where('is_deleted', 0)
            ->distinct()
            ->pluck('doc_id')
            ->toArray();
    }

    /**
     * 특정 기간 동안 특정 게시판에 1개 이상의 게시글을 작성한 '활동 유저' 목록을 불러옵니다.
     * * [사용 예시] 
     * $activeUsers = PluginHelper::getWritersInPeriod('freeboard', '2026-02-01 00:00:00', '2026-02-28 23:59:59');
     * // 결과: [ 1 => 'admin', 5 => 'user_abc' ] (key: users.id, value: users.user_id)
     * @param string $boardId 대상 게시판 ID
     * @param string $startDate 검색 시작 일시 (Y-m-d H:i:s 형식)
     * @param string $endDate 검색 종료 일시 (Y-m-d H:i:s 형식)
     * @return array 유저 PK(id)를 키(Key)로 하고, 로그인 아이디(user_id)를 값(Value)으로 가지는 연관 배열
     */
    public static function getWritersInPeriod(string $boardId, string $startDate, string $endDate) {
        return DB::table('documents')
            ->join('users', 'documents.user_id', '=', 'users.id')
            ->where('documents.board_id', $boardId)
            ->where('documents.is_deleted', 0)
            ->whereBetween('documents.created_at', [$startDate, $endDate])
            ->distinct()
            ->pluck('users.user_id', 'users.id')
            ->toArray();
    }

    /**
     * 유저의 포인트를 증감시킵니다.
     * [사용 예시] PluginHelper::addPoint(1, 500); // 500P 지급
     * [사용 예시] PluginHelper::addPoint(1, -200); // 200P 차감
     * @param int $userId 유저 고유 ID (테이블 PK)
     * @param int $amount 증감할 포인트량 (음수 가능)
     * @return bool 성공 여부
     */
    public static function addPoint(int $userId, int $amount) {
        if ($amount === 0) return true;
        
        $user = DB::table('users')->find($userId);
        if (!$user) return false;

        if ($amount < 0 && $user->user_point < abs($amount)) return false;

        DB::table('users')->where('id', $userId)->increment('user_point', $amount);
        return true;
    }

    /**
     * 캐릭터가 특정 타이틀을 보유하고 있는지 확인합니다.
     * @param int $charId 캐릭터 ID
     * @param int $titleId 확인할 타이틀 ID
     * @return bool 보유 여부
     */
    public static function hasTitle(int $charId, int $titleId) {
        return DB::table('character_titles')
            ->where('character_id', $charId)
            ->where('title_id', $titleId)
            ->exists();
    }
    
}