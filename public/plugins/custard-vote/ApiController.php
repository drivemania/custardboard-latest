<?php

namespace Plugins\CustardVote;

use App\Support\PluginHelper as Helper;

class ApiController {
    public function commentGet($request, $response, $args=[]) {
        $id = $_GET['id'] ?? "";
        if($id === ""){
            $response->getBody()->write(json_encode(['voteData' => null]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
        
        $meta = Helper::getCommentMeta('custardVote', $id, 'vote');

        $meta['isVoted'] = false;
        $meta['isClosed'] = false;
        
        if(strtotime($meta['deadline']) < strtotime('now')){
            $meta['isClosed'] = true;
        }

        if(in_array($_SESSION['user_idx'], $meta['votersList'])){
            $meta['isVoted'] = true;
        }

        $response->getBody()->write(json_encode(['voteData' => $meta]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function documentGet($request, $response, $args=[]) {
        $id = $_GET['id'] ?? "";
        if($id === ""){
            $response->getBody()->write(json_encode(['voteData' => null]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
        
        $meta = Helper::getDocumentMeta('custardVote', $id, 'vote');

        $meta['isVoted'] = false;
        $meta['isClosed'] = false;
        
        if(strtotime($meta['deadline']) < strtotime('now')){
            $meta['isClosed'] = true;
        }

        if(in_array($_SESSION['user_idx'], $meta['votersList'])){
            $meta['isVoted'] = true;
        }

        $response->getBody()->write(json_encode(['voteData' => $meta]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function commentVote($request, $response, $args=[]) {
        $data = json_decode($request->getBody()->getContents(), true);

        $commentId = $data['id'] ?? '';
        $choices = $data['choices'] ?? [];

        if($commentId === "" || count($choices) <= 0){
            $response->getBody()->write(json_encode(['success' => false]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $meta = Helper::getCommentMeta('custardVote', $commentId, 'vote');
        if($meta === null){
            $response->getBody()->write(json_encode(['success' => false]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        if(strtotime($meta['deadline']) < strtotime('now')){
            $response->getBody()->write(json_encode(['success' => false]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        foreach($meta['items'] as $key => $value){
            if(in_array($value['id'], $choices)){
                $meta['items'][$key]['votes'] ++ ;
                $meta['items'][$key]['voters'][] = $_SESSION['nickname'] ?? '손님' ;
            }
        }
        $meta['totalVotes'] ++ ;
        $meta['votersList'][] = $_SESSION['user_idx'] ?? 0 ;

        Helper::saveCommentMeta('custardVote', $commentId, 'vote', json_encode($meta, JSON_UNESCAPED_UNICODE));

        $response->getBody()->write(json_encode(['success' => true]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function documentVote($request, $response, $args=[]) {
        $data = json_decode($request->getBody()->getContents(), true);

        $documentId = $data['id'] ?? '';
        $choices = $data['choices'] ?? [];

        if($documentId === "" || count($choices) <= 0){
            $response->getBody()->write(json_encode(['success' => false]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $meta = Helper::getDocumentMeta('custardVote', $documentId, 'vote');
        if($meta === null){
            $response->getBody()->write(json_encode(['success' => false]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        if(strtotime($meta['deadline']) < strtotime('now')){
            $response->getBody()->write(json_encode(['success' => false]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        foreach($meta['items'] as $key => $value){
            if(in_array($value['id'], $choices)){
                $meta['items'][$key]['votes'] ++ ;
                $meta['items'][$key]['voters'][] = $_SESSION['nickname'] ?? '손님' ;
            }
        }
        $meta['totalVotes'] ++ ;
        $meta['votersList'][] = $_SESSION['user_idx'] ?? 0 ;

        Helper::saveDocumentMeta('custardVote', $documentId, 'vote', json_encode($meta, JSON_UNESCAPED_UNICODE));

        $response->getBody()->write(json_encode(['success' => true]));
        return $response->withHeader('Content-Type', 'application/json');
    }
}