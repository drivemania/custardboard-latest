<?php

namespace Plugins\CustardMannerChk;

use App\Support\PluginHelper as Helper;

class ApiController {
    public function check($request, $response, $args = []) {

        $userId = $_SESSION['user_id'] ?? "";
        if($userId != ""){
            $adminChk = Helper::getUserInfo($userId);
            if(!$adminChk || $adminChk->level < 10){
                $response->getBody()->write(json_encode(['error' => '관리자만 이용 가능합니다.']));
                return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
            }
        }else{
            $response->getBody()->write(json_encode(['error' => '로그인 후 이용해주세요.']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $data = json_decode($request->getBody()->getContents(), true);
        
        $boardId = $data['board_id'] ?? null;
        $memberList = $data['member_id'] ?? null;
        $startDate = $data['start_date'] ?? date('Y-m-d', strtotime('-7 days'));
        $endDate = $data['end_date'] ?? date('Y-m-d');
        $endDateTime = $endDate." 23:59:59";
        $limitCount = $data['limit_count'] ?? 3;

        if (!$boardId || !$memberList) {
            $response->getBody()->write(json_encode(['error' => '필수 값이 누락되었습니다.']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $boardList = Helper::getBoardList($boardId);
        $boardName = count($boardList) > 0 ? $boardList[0]->title : "";

        $targetUsers = [];
        if($memberList === "@all"){
            $targetUsers = Helper::getWritersInPeriod($boardId, $startDate, $endDateTime);
        } else {
            $memberArr = array_map('trim', explode(',', $memberList));
            foreach($memberArr as $mid) {
                $uInfo = Helper::getUserInfo($mid);
                if($uInfo) {
                    $targetUsers[$uInfo->id ?? 0] = $uInfo->user_id; 
                }
            }
        }

        $list = [];
        $count = [];

        foreach($targetUsers as $realId => $strId) {
            if(!$realId) continue;

            $noMannerChkCnt = 0;

            $userDocs = Helper::getUserDocumentList($strId, $boardId, [$startDate, $endDateTime], 'desc');

            foreach ($userDocs as $doc) {
                $prevDocs = Helper::getPrevDocuments($boardId, $doc->doc_num, $limitCount);

                if ($prevDocs->isEmpty()) continue;

                $prevDocIds = $prevDocs->pluck('id')->toArray();

                $commentedDocIds = Helper::getUserCommentedDocIds($realId, $prevDocIds);

                $myCommentList = [];
                foreach ($prevDocs as $pDoc) {
                    if (in_array($pDoc->id, $commentedDocIds)) {
                        $myCommentList[] = $pDoc->doc_num;
                    }
                }

                $noMannerChk = 0;
                if (count($myCommentList) < $limitCount) {
                    $noMannerChk = 1;
                    $noMannerChkCnt++;
                }

                $list[] = [
                    'member_id' => $strId,
                    'board_name' => $boardName,
                    'doc_num' => $doc->doc_num,
                    'created_at' => $doc->created_at,
                    'comment_list' => $myCommentList,
                    'nomanner_chk' => $noMannerChk
                ];
            }

            $count[] = [
                'member_id' => $strId,
                'count' => $noMannerChkCnt
            ];
        }

        $response->getBody()->write(json_encode(['count' => $count, 'list' => $list]));
        return $response->withHeader('Content-Type', 'application/json');
    }
}