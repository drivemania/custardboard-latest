<?php

use App\Support\PluginHelper as Helper;

$boards = Helper::getBoardList();
$base_path = Helper::getBasePath();

?>

<div class="max-w-8xl mx-auto space-y-6"
     x-data="{
        searchData: {
            board_id: '',
            start_date: '<?= date('Y-m-d', strtotime('-7 days')) ?>',
            end_date: '<?= date('Y-m-d') ?>',
            member_id: '',
            limit_count: 3
        },
        results: [],
        countList: [],
        isLoading: false,
        searched: false,

        async search() {
            if (!this.searchData.board_id) {
                alert('게시판을 선택해주세요.');
                return;
            }
            if (!this.searchData.member_id) {
                alert('멤버 ID를 입력해주세요.');
                return;
            }

            this.isLoading = true;
            this.searched = true;
            this.results = [];
            this.countList = [];

            try {
                const response = await fetch('<?= $base_path ?>/plugin/custardMannerChk/check', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(this.searchData)
                });

                if (!response.ok) throw new Error('Network response was not ok');
                
                const data = await response.json();
                this.results = data.list || [];
                this.countList = data.count || [];

            } catch (error) {
                console.error('Error:', error);
                alert('검색 중 오류가 발생했습니다.');
            } finally {
                this.isLoading = false;
            }
        }
     }">

    <div class="bg-white rounded-xl shadow-sm border border-neutral-200 p-6">
        <div class="flex flex-col md:flex-row gap-4 items-end">
            
            <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 w-full">
                
                <div class="flex items-center">
                    <label class="w-24 text-sm font-bold text-neutral-700 shrink-0">게시판</label>
                    <select x-model="searchData.board_id" class="flex-1 border border-neutral-300 rounded px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400">
                        <option value="">게시판 선택</option>
                        <?php
                            foreach($boards as $board){
                                echo '<option value="'.$board->id.'">'.$board->title.'</option>';
                            }
                        ?>
                    </select>
                </div>
                
                <div class="flex items-center">
                    <label class="w-24 text-sm font-bold text-neutral-700 shrink-0">댓글 수</label>
                    <input type="number" x-model="searchData.limit_count" class="w-16 border border-neutral-300 rounded px-2 py-2 text-sm text-center outline-none focus:border-amber-400">
                    <span class="text-sm text-neutral-600 ml-2">개 미만인 경우</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-600 ml-2">
                        경고
                    </span>
                </div>

                <div class="flex items-center">
                    <label class="w-24 text-sm font-bold text-neutral-700 shrink-0">멤버 ID</label>
                    <input type="text" x-model="searchData.member_id" placeholder="콤마(,)로 구분합니다. @all 만 입력할 경우 입력 기간 내 전체 멤버 대상으로 데이터를 가져옵니다." 
                        class="flex-1 border border-neutral-300 rounded px-3 py-2 text-sm outline-none focus:border-amber-400">
                </div>

                <div class="flex items-center">
                    <label class="w-24 text-sm font-bold text-neutral-700 shrink-0">날짜 검색</label>
                    <div class="flex items-center gap-2 flex-1">
                        <input type="date" x-model="searchData.start_date" class="w-full border border-neutral-300 rounded px-2 py-2 text-sm outline-none focus:border-amber-400">
                        <span class="text-neutral-400">~</span>
                        <input type="date" x-model="searchData.end_date" class="w-full border border-neutral-300 rounded px-2 py-2 text-sm outline-none focus:border-amber-400">
                    </div>
                </div>

            </div>

            <button @click="search()" class="h-[88px] w-24 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded shadow-sm transition-colors flex flex-col items-center justify-center shrink-0">
                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                검색
            </button>
        </div>
    </div>

    <div x-show="searched" style="display: none;">
        
        <div x-show="isLoading" class="py-12 text-center text-neutral-400 bg-white rounded-xl border border-neutral-200">
            <svg class="animate-spin h-8 w-8 mx-auto text-amber-500 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p>데이터를 조회하고 있습니다...</p>
        </div>

        <div x-show="!isLoading" class="bg-white rounded-xl shadow-sm border border-neutral-200 overflow-hidden">
            <div class="p-4 border-b border-neutral-100 bg-neutral-50 flex justify-between items-center">
                <h3 class="font-bold text-neutral-800">
                    검색 결과 <span class="text-amber-600" x-text="results.length + '건'"></span>
                </h3>
            </div>
            <table class="w-full text-sm text-left">
                <thead class="bg-neutral-50 text-neutral-500 font-medium border-b border-neutral-200">
                    <tr>
                        <th class="px-6 py-3 w-24">대상 아이디</th>
                        <th class="px-6 py-3 w-20">총 경고 갯수</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100">
                    <template x-for="item in countList" :key="item.member_id">
                        <tr class="hover:bg-neutral-50 transition-colors">
                            <td class="px-6 py-3 text-neutral-800" x-text="item.member_id"></td>
                            <td class="px-6 py-3 text-neutral-800" x-text="item.count"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
            <div class="p-4 border-b border-neutral-100 bg-neutral-50 flex justify-between items-center">
                <h3 class="font-bold text-neutral-800">
                    상세 정보
                </h3>
            </div>
            <table class="w-full text-sm text-left">
                <thead class="bg-neutral-50 text-neutral-500 font-medium border-b border-neutral-200">
                    <tr>
                        <th class="px-6 py-3 w-24">대상 아이디</th>
                        <th class="px-6 py-3 w-20">게시판명</th>
                        <th class="px-6 py-3 w-20">번호</th>
                        <th class="px-6 py-3 w-32">작성일</th>
                        <th class="px-6 py-3 w-20">댓글 쓴 게시글</th>
                        <th class="px-6 py-3 w-20 text-center">매너 체크</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100">
                    <template x-for="item in results" :key="item.doc_num">
                        <tr class="hover:bg-neutral-50 transition-colors">
                            <td class="px-6 py-3 text-neutral-800" x-text="item.member_id"></td>
                            <td class="px-6 py-3 text-neutral-800" x-text="item.board_name"></td>
                            <td class="px-6 py-3 text-neutral-800" x-text="item.doc_num"></td>
                            <td class="px-6 py-3 text-neutral-800" x-text="item.created_at"></td>
                            <td class="px-6 py-3 text-neutral-800">
                                <template x-if="item.comment_list && item.comment_list.length > 0">
                                    <div class="flex flex-wrap gap-1">
                                        <template x-for="num in item.comment_list">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-neutral-100 text-neutral-600 border border-neutral-200"
                                                x-text="'#' + num">
                                            </span>
                                        </template>
                                    </div>
                                </template>
                                
                                <template x-if="!item.comment_list || item.comment_list.length === 0">
                                    <span class="text-xs text-neutral-300">-</span>
                                </template>
                            </td>
                            <td class="px-6 py-3 text-center">
                                <template x-if="item.nomanner_chk > 0">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-600">
                                        경고
                                    </span>
                                </template>
                                
                                <template x-if="item.nomanner_chk == 0">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-600">
                                        OK
                                    </span>
                                </template>
                            </td>
                        </tr>
                    </template>
                    
                    <tr x-show="results.length === 0">
                        <td colspan="5" class="py-10 text-center text-neutral-400">
                            조건에 해당하는 게시글이 없습니다.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>