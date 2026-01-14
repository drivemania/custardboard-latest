<?php
$drawList = $settings['lists'] ?? [];

if (empty($drawList) && !empty($settings['keyword'])) {
    $drawList[] = [
        'keyword' => $settings['keyword'],
        'trigger' => $settings['trigger'],
        'items'   => $settings['items'] ?? ''
    ];
}

if (empty($drawList)) {
    $drawList[] = ['keyword' => '', 'items' => ''];
}
?>

<div class="bg-white rounded-xl shadow-sm border border-neutral-200 overflow-hidden max-w-4xl mx-auto"
     x-data="{ 
        lists: <?= htmlspecialchars(json_encode($drawList), ENT_QUOTES, 'UTF-8') ?>,
        addDraw() {
            this.lists.push({ keyword: '', trigger: '', items: '' });
        },
        removeDraw(index) {
            if(this.lists.length > 1) {
                if(confirm('정말 이 설정을 삭제하시겠습니까?')) {
                    this.lists.splice(index, 1);
                }
            } else {
                alert('최소 하나의 설정은 필요합니다.');
            }
        }
     }">
    
    <div class="p-6 border-b border-neutral-100 bg-neutral-50 flex justify-between items-center">
        <div>
            <h3 class="text-lg font-bold text-neutral-800">🎲 랜덤 출력 메세지 그룹 설정</h3>
            <p class="text-sm text-neutral-500 mt-1">여러 개의 랜덤 출력 메세지 그룹을 만들어 관리할 수 있습니다.</p>
        </div>
        <button type="button" @click="addDraw()" 
                class="bg-white border border-neutral-300 hover:bg-neutral-50 text-neutral-700 px-3 py-1.5 rounded-lg text-sm font-bold shadow-sm transition-colors flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            그룹 추가
        </button>
    </div>

    <form method="POST" class="p-6 space-y-8">
        
        <template x-for="(draw, index) in lists" :key="index">
            <div class="relative bg-neutral-50/50 rounded-xl p-5 border border-neutral-200 group transition-all hover:border-amber-300 hover:shadow-sm">
                
                <button type="button" @click="removeDraw(index)" 
                        class="absolute top-4 right-4 text-neutral-400 hover:text-red-500 transition-colors p-1" title="삭제">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </button>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-neutral-700 mb-2">
                            <span class="text-amber-600 mr-1" x-text="'#' + (index + 1)"></span>
                            메세지 그룹 이름
                            <span class="text-xs font-normal text-neutral-400 ml-1">체크박스 옆에 표시될 이름입니다.</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-neutral-400 font-bold">□</span>
                            </div>
                            <input type="text" 
                                   :name="'lists[' + index + '][keyword]'" 
                                   x-model="draw.keyword"
                                   class="w-full pl-10 pr-12 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-amber-200 focus:border-amber-400 outline-none transition-all text-sm bg-white placeholder-neutral-300"
                                   placeholder="예: 점심 메뉴 뽑기">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-neutral-700 mb-2">
                            트리거명
                            <span class="text-xs font-normal text-neutral-400 ml-1">기본 키값입니다. 다른 그룹과 겹치지 않는 영어 이름으로 생성해주세요.</span>
                        </label>
                        <div class="relative">
                            <input type="text" 
                                   :name="'lists[' + index + '][trigger]'" 
                                   x-model="draw.trigger"
                                   class="w-full pl-3 pr-12 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-amber-200 focus:border-amber-400 outline-none transition-all text-sm bg-white placeholder-neutral-300"
                                   placeholder="random">
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label class="block text-sm font-bold text-neutral-700">출력 항목 리스트</label>
                            <span class="text-xs text-amber-600 bg-amber-50 px-2 py-1 rounded font-bold">한 줄에 하나씩</span>
                        </div>
                        <textarea :name="'lists[' + index + '][items]'" 
                                  x-model="draw.items"
                                  rows="5" 
                                  class="w-full p-4 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-amber-200 focus:border-amber-400 outline-none transition-all text-sm leading-relaxed resize-none scrollbar-hide bg-white"
                                  placeholder="항목1&#10;항목2&#10;항목3"></textarea>
                        <p class="text-xs text-neutral-400">
                            입력한 항목 중 하나가 무작위로 선택되어 댓글에 표시됩니다. HTML 입력 가능하나 한 줄 안에 입력해주세요.
                        </p>
                    </div>
                </div>
            </div>
        </template>

        <div class="pt-4 border-t border-neutral-100 flex justify-end">
            <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-white font-bold py-2.5 px-6 rounded-lg shadow-sm transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                설정 저장
            </button>
        </div>

    </form>
</div>