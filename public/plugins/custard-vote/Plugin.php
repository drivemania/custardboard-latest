<?php

namespace Plugins\CustardVote;

use App\Support\Hook;
use App\Support\PluginHelper;

class Vote {
    public static $data = "";

    public function beforeSaveData($type, $data) {
        if ($data['custard_vote_chk'] == 1) {
            $data = json_decode($data['vote_data'], true);
            $result = [];
    
            $result['title'] = $data['title'];
            $result['deadline'] = date("Y-m-d H:i:s", strtotime($data['deadline']));
            $result['showVoters'] = $data['showVoters'];
            $result['singleVote'] = $data['singleVote'];
            $result['totalVotes'] = 0;
            $result['votersList'] = [];
            $i = 1;
            foreach($data['items'] as $value){
                $result['items'][] = array(
                    'id' => $i,
                    'text' => $value,
                    'votes' => 0,
                    'voters' => []
                );
                $i++;
            }
    
            Vote::$data = json_encode($result, JSON_UNESCAPED_UNICODE);
        }
        return $data;
    }

    public function afterSaveData($type, $id){
        if(Vote::$data !== ""){
            $base_path = PluginHelper::getBasePath();
            $data = Vote::$data;
            
            $html = <<<HTML
            <div class="my-4 border border-neutral-200 rounded-2xl overflow-hidden bg-white shadow-sm w-full"
                 x-data="{
                    isLoading: true,
                    voteData: null,
                    selectedChoices: [],
                    id: {$id},
                    
                    async init() {
                        try {
                            const response = await fetch('{$base_path}/plugin/custardVote/{$type}Get?id=' + this.id);
                            const result = await response.json();
                            
                            if(result && result.voteData) {
                                this.voteData = result.voteData;
                            } else {
                                this.voteData = null;
                            }
                        } catch (error) {
                            console.error('투표 데이터를 불러오지 못했습니다.', error);
                            this.voteData = null;
                        } finally {
                            this.isLoading = false;
                        }
                    },
                    
                    toggleSelection(itemId) {
                        if(!this.voteData || this.voteData.isClosed) return;
                        
                        if(this.voteData.singleVote) {
                            this.selectedChoices = [itemId];
                        } else {
                            if(this.selectedChoices.includes(itemId)) {
                                this.selectedChoices = this.selectedChoices.filter(v => v !== itemId);
                            } else {
                                this.selectedChoices.push(itemId);
                            }
                        }
                    },
                    
                    async submitVote() {
                        try {
                            const response = await fetch('{$base_path}/plugin/custardVote/{$type}Vote', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({
                                    id: this.id,
                                    choices: this.selectedChoices
                                })
                            });
                            
                            const result = await response.json();
                            if(result.success) {
                                alert('투표가 완료되었습니다!');
                                this.init(); 
                                this.selectedChoices = [];
                            } else {
                                alert(result.error || '투표 처리 중 오류가 발생했습니다.');
                            }
                        } catch(e) {
                            alert('통신 오류가 발생했습니다.');
                        }
                    }
                 }"
                 x-show="isLoading || voteData"
                 style="display: none;"
                 x-transition>
        
                <div x-show="isLoading" class="p-8 text-center text-neutral-400">
                    <svg class="animate-spin h-6 w-6 mx-auto text-amber-500 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="text-xs font-bold">투표 데이터를 불러오는 중...</p>
                </div>
        
                <div x-show="!isLoading && voteData">
                    <div class="p-5 border-b border-neutral-100 bg-neutral-50">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <span class="inline-block px-2 py-1 bg-amber-100 text-amber-700 text-xs font-bold rounded mb-2">투표</span>
                                <h3 class="text-lg font-bold text-neutral-800" x-text="voteData?.title"></h3>
                            </div>
                            <div class="text-right shrink-0">
                                <span x-show="!voteData?.isClosed" class="text-xs font-bold text-green-600 bg-green-50 px-2 py-1 rounded-full border border-green-200">진행중</span>
                                <span x-show="voteData?.isClosed" class="text-xs font-bold text-neutral-500 bg-neutral-100 px-2 py-1 rounded-full border border-neutral-200">마감됨</span>
                                <span x-show="voteData?.isVoted" class="text-xs font-bold text-blue-500 bg-blue-100 px-2 py-1 rounded-full border border-blue-200">투표완료</span>
                            </div>
                        </div>
                        <p class="text-xs text-neutral-400 mt-2 flex items-center gap-2">
                            <span>총 <b class="text-neutral-600" x-text="voteData?.totalVotes"></b>명 참여</span>
                            <span>•</span>
                            <span x-text="'마감: ' + voteData?.deadline"></span>
                        </p>
                    </div>
        
                    <div class="p-5 space-y-4">
                        <template x-for="item in voteData?.items" :key="item.id">
                            <div class="relative group">
                                <button type="button" 
                                        @click="toggleSelection(item.id)"
                                        :class="selectedChoices.includes(item.id) ? 'border-amber-400 bg-amber-50 ring-1 ring-amber-400' : 'border-neutral-200 bg-white hover:border-amber-300'"
                                        class="relative w-full text-left border rounded-xl overflow-hidden transition-all duration-200"
                                        :disabled="voteData?.isClosed || voteData?.isVoted">
                                    
                                    <div class="absolute top-0 left-0 h-full bg-amber-100/50 transition-all duration-500" 
                                         :style="'width: ' + (voteData?.totalVotes > 0 ? (item.votes / voteData?.totalVotes) * 100 : 0) + '%'"></div>
                                    
                                    <div class="relative p-3.5 flex items-center justify-between z-10">
                                        <div class="flex items-center gap-3">
                                            <div class="w-5 h-5 rounded-full border flex items-center justify-center flex-shrink-0"
                                                 :class="[
                                                     voteData?.singleVote ? 'rounded-full' : 'rounded',
                                                     selectedChoices.includes(item.id) ? 'border-amber-500 bg-amber-500' : 'border-neutral-300 bg-white'
                                                 ]">
                                                <svg x-show="selectedChoices.includes(item.id)" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                            </div>
                                            <span class="font-bold text-neutral-800 text-sm" x-text="item.text"></span>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-xs font-bold text-amber-600" x-text="voteData?.totalVotes > 0 ? Math.round((item.votes / voteData?.totalVotes) * 100) + '%' : '0%'"></span>
                                            <span class="text-xs text-neutral-400 ml-1" x-text="'(' + item.votes + '표)'"></span>
                                        </div>
                                    </div>
                                </button>
        
                                <template x-if="voteData?.showVoters && item.voters && item.voters.length > 0">
                                    <div class="mt-1.5 ml-1 flex flex-wrap gap-1">
                                        <template x-for="voter in item.voters">
                                            <span class="px-1.5 py-0.5 bg-neutral-100 text-neutral-500 text-[11px] rounded border border-neutral-200" x-text="voter"></span>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
        
                    <div class="p-4 bg-neutral-50 border-t border-neutral-100" x-show="!voteData?.isClosed && !voteData?.isVoted">
                        <button type="button" 
                                @click.prevent.stop="submitVote()"
                                class="w-full bg-amber-600 hover:bg-amber-700 text-white font-bold py-2.5 rounded-lg shadow-sm transition-colors text-sm"
                                :class="{ 'opacity-50 cursor-not-allowed': selectedChoices.length === 0 }"
                                :disabled="selectedChoices.length === 0">
                            투표하기
                        </button>
                        <p class="text-center text-xs text-neutral-400 mt-2" x-text="voteData?.singleVote ? '* 한 항목만 선택할 수 있습니다.' : '* 여러 항목을 선택할 수 있습니다.'"></p>
                    </div>
                </div>
            </div>
            HTML;

            switch($type){
                case 'comment': {
                    PluginHelper::saveCommentMeta('custardVote', $id, 'result', $html);
                    PluginHelper::saveCommentMeta('custardVote', $id, 'vote', $data);
                    break;
                }
                case 'document': {
                    PluginHelper::saveDocumentMeta('custardVote', $id, 'result', $html);
                    PluginHelper::saveDocumentMeta('custardVote', $id, 'vote', $data);
                    break;
                }
            }
    
            
        }
    
        Vote::$data = null;
    
    }

    public function contentHead($type, $id){
        switch($type){
            case 'comment': {
                if($id && $id > 0){
                    $data = PluginHelper::getCommentMeta('custardVote', $id, 'vote');
                    
                }

                break;
            }
            case 'document': {
                if($id && $id > 0){
                    $data = PluginHelper::getDocumentMeta('custardVote', $id, 'vote');
                }
                break;
            }
        }

        if(!$data) {
            $data = array(
                'title' => '',
                'deadline' => '',
                'showVoters' => false,
                'singleVote' => true
            );
            $items = "['', '']";
            $useVote = json_encode((bool)false);
        }else{
            $values = array_column($data['items'], 'text');
            $items = !empty($values) ? "['" . implode("', '", $values) . "']" : "[]";
            $useVote = json_encode((bool)true);
        }

        $data['showVoters'] = json_encode((bool)$data['showVoters']);
        $data['singleVote'] = json_encode((bool)$data['singleVote']);

        echo <<<HTML
            <div class="relative flex items-center mr-4"
                x-data="{
                    useVote: {$useVote},
                    isOpen: false,
                    vote: { title: '{$data['title']}', deadline: '{$data['deadline']}', showVoters: {$data['showVoters']}, singleVote: {$data['singleVote']}, items: {$items} },
                    addItem() { this.vote.items.push(''); },
                    removeItem(index) { if(this.vote.items.length > 2) this.vote.items.splice(index, 1); else alert('최소 2개의 항목이 필요합니다.'); }
                }">
                
                <div class="flex gap-1.5">
                    <label class="text-sm">
                    <input type="checkbox" name="custard_vote_chk" value="1" x-model="useVote" @change="if(useVote) { isOpen = true; }">
                    투표 첨부하기</label>
                    
                    <button type="button" x-show="useVote" @click="isOpen = true" style="display: none;"
                            class="text-neutral-400 hover:text-amber-600 transition-colors outline-none p-1" 
                            title="투표 설정 열기">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    </button>
                </div>
        
                <input type="hidden" name="vote_data" :value="useVote ? JSON.stringify(vote) : ''">
        
                <template x-teleport="body">
                    <div x-show="isOpen" 
                        style="display: none;"
                        class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/40 p-4"
                        x-transition.opacity>
                        
                        <div @click.outside="isOpen = false"
                            x-show="isOpen"
                            class="w-full max-w-sm bg-white border border-neutral-200 rounded-xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
                            
                            <div class="px-4 py-3 bg-neutral-50 border-b border-neutral-100 flex items-center justify-between shrink-0">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-neutral-700 text-sm">투표 상세 설정</span>
                                </div>
                                <button type="button" @click="isOpen = false" class="text-neutral-400 hover:text-neutral-600 bg-white border border-neutral-200 rounded p-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>
        
                            <div class="p-4 space-y-4 overflow-y-auto">
                                <div>
                                    <label class="block text-xs font-bold text-neutral-700 mb-1">투표 주제</label>
                                    <input type="text" x-model="vote.title" class="w-full bg-white border border-neutral-300 rounded text-sm px-2.5 py-1.5 outline-none focus:border-amber-400">
                                </div>
        
                                <div>
                                    <label class="block text-xs font-bold text-neutral-700 mb-1">항목 작성</label>
                                    <div class="space-y-1.5">
                                        <template x-for="(item, index) in vote.items" :key="index">
                                            <div class="flex gap-1.5">
                                                <input type="text" x-model="vote.items[index]" class="flex-1 bg-white border border-neutral-300 rounded text-sm px-2.5 py-1.5 outline-none focus:border-amber-400" :placeholder="'항목 ' + (index + 1)">
                                                <button type="button" @click="removeItem(index)" class="px-2 py-1.5 border border-neutral-300 rounded text-neutral-400 hover:text-red-500 bg-neutral-50">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                    <button type="button" @click="addItem()" class="mt-2 text-xs text-amber-600 font-bold hover:underline">+ 항목 하나 더 추가하기</button>
                                </div>
        
                                <div class="pt-3 border-t border-neutral-100 space-y-3">
                                    <div>
                                        <label class="block text-xs font-bold text-neutral-700 mb-1">마감일시</label>
                                        <input type="datetime-local" x-model="vote.deadline" class="w-full bg-white border border-neutral-300 rounded px-2.5 py-1.5 text-sm outline-none focus:border-amber-400">
                                    </div>
                                    <div class="flex flex-col gap-1.5 bg-neutral-50 p-2.5 rounded border border-neutral-200">
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" x-model="vote.showVoters" class="rounded border-gray-300 text-amber-500 w-3.5 h-3.5">
                                            <span class="text-xs text-neutral-700 font-medium">누가 투표했는지 공개</span>
                                        </label>
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" x-model="vote.singleVote" class="rounded border-gray-300 text-amber-500 w-3.5 h-3.5">
                                            <span class="text-xs text-neutral-700 font-medium">한 개만 투표 가능</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
        
                            <div class="p-3 bg-neutral-50 border-t border-neutral-200 shrink-0">
                                <button type="button" @click="isOpen = false" class="w-full py-2 bg-neutral-800 text-white text-sm font-bold rounded-lg hover:bg-neutral-900 transition-colors">
                                    설정 완료
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        HTML;
    }
}

Hook::add('before_comment_save', function($data) {
    $vote = new Vote();
    return $vote->beforeSaveData('comment', $data);
});

Hook::add('after_comment_save', function($id) {
    $vote = new Vote();
    $vote->afterSaveData('comment', $id);
});

Hook::add('before_document_save', function($data) {
    $vote = new Vote();
    return $vote->beforeSaveData('document', $data);
});

Hook::add('after_document_save', function($id) {
    $vote = new Vote();
    $vote->afterSaveData('document', $id);
});

Hook::add('comment_content_list', function($id = 0) {
    $vote = new Vote();
    $vote->contentHead('comment', $id);
});

Hook::add('document_content_list', function($id = 0) {
    $vote = new Vote();
    $vote->contentHead('document', $id);
});

