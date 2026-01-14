@extends('layouts.admin')
@section('title', '캐릭터 관리')
@section('header', '캐릭터 관리')

@section('content')
<div x-data="characterManager()">
    <div class="bg-white p-4 rounded-lg shadow-sm mb-6 flex justify-between items-center">
        <div class="flex items-center space-x-3">
            <div class="text-sm text-neutral-500">
                총 <b>{{ $characters->total() }}</b>명
            </div>
            
            <button type="button" 
                x-show="selectedIds.length > 0" 
                @click="openBulkMoveModal()"
                class="bg-amber-500 text-white px-4 py-2 rounded text-sm font-bold hover:bg-amber-700 transition"
                style="display: none;">
                <span x-text="selectedIds.length"></span>명 일괄 이동
            </button>
        </div>

        <form method="GET" class="flex space-x-2">
            <input type="text" name="search" value="{{ $search }}" placeholder="캐릭터명 또는 오너명" class="border rounded px-3 py-2 text-sm outline-none focus:border-amber-400 w-64">
            <button type="submit" class="bg-neutral-800 text-white px-4 py-2 rounded text-sm font-bold hover:bg-black">검색</button>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-neutral-50 border-b text-neutral-600 text-sm uppercase">
                    <th class="px-6 py-3 w-10">
                        <input type="checkbox" @change="toggleAll($event)" class="w-4 h-4 rounded text-amber-500">
                    </th>
                    <th class="px-6 py-3">ID</th>
                    <th class="px-6 py-3">이미지</th>
                    <th class="px-6 py-3">캐릭터 정보</th>
                    <th class="px-6 py-3">소속 그룹</th>
                    <th class="px-6 py-3">현재 위치</th>
                    <th class="px-6 py-3 text-right">관리</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-200 text-sm">
                @foreach($characters as $char)
                <tr class="hover:bg-neutral-50 transition">
                    <td class="px-6 py-4">
                        <input type="checkbox" value="{{ $char->id }}_{{ $char->group_id }}" x-model="selectedItems" class="w-4 h-4 rounded text-amber-500">
                    </td>
                    <td class="px-6 py-4 text-neutral-400">{{ $char->id }}</td>
                    <td class="px-6 py-4">
                        <div class="w-10 h-10 rounded-full bg-neutral-200 overflow-hidden border">
                            <img src="{{ $char->image_path ?? 'https://via.placeholder.com/50' }}" class="w-full h-full object-cover">
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="font-bold text-neutral-800">{{ $char->name }}</div>
                        <div class="text-xs text-neutral-500">오너: {{ $char->owner_name }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="bg-neutral-100 text-neutral-600 px-2 py-1 rounded text-xs">{{ $char->group_name }}</span>
                    </td>
                    <td class="px-6 py-4 text-amber-500 font-medium">
                        {{ $char->board_title ?? '(미지정)' }}
                    </td>
                    <td class="px-6 py-4 text-right">
                        <button type="button" 
                            @click="openMoveModal({{ $char->id }}, {{ $char->group_id }}, '{{ $char->name }}')"
                            class="bg-white border border-neutral-300 text-neutral-700 px-3 py-1 rounded hover:bg-neutral-50 text-xs font-bold">
                            개별 이동
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        @if($characters->hasPages())
        <div class="p-4 border-t flex justify-center">
            {{ $characters->links('vendor.pagination.tailwind') }}
        </div>
        @endif
    </div>

    <div x-show="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" style="display: none;">
        <div class="bg-white rounded-lg shadow-lg w-96 p-6" @click.away="closeModal()">
            <h3 class="text-lg font-bold mb-4 border-b pb-2">캐릭터 이동</h3>
            
            <p class="text-sm text-neutral-600 mb-4">
                <span x-html="targetDisplayName"></span>
                <br>이동시킬 게시판을 선택하세요.
            </p>

            <form action="{{ $base_path }}/admin/characters/move" method="POST">
                <input type="hidden" name="char_ids" x-model="targetCharIds">
                
                <div class="mb-4">
                    <label class="block text-xs font-bold text-neutral-500 mb-1">이동 대상 게시판</label>
                    <select name="target_board_id" class="w-full border rounded px-3 py-2 bg-white" required>
                        <option value="">선택하세요</option>
                        <template x-for="board in boards" :key="board.id">
                            <option :value="board.id" x-text="board.title + ' (' + board.id + ')'"></option>
                        </template>
                    </select>
                </div>

                <div class="flex justify-end space-x-2">
                    <button type="button" @click="closeModal()" class="px-4 py-2 bg-neutral-200 text-neutral-700 rounded hover:bg-neutral-300 font-bold text-sm">취소</button>
                    <button type="submit" class="px-4 py-2 bg-amber-500 text-white rounded hover:bg-amber-700 font-bold text-sm">이동확인</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    function characterManager() {
        return {
            showModal: false,
            selectedItems: [],
            targetCharIds: '',
            targetDisplayName: '',
            boards: [],
    
            get selectedIds() {
                return this.selectedItems.map(item => item.split('_')[0]);
            },
    
            toggleAll(e) {
                if (e.target.checked) {
                    this.selectedItems = Array.from(document.querySelectorAll('input[x-model="selectedItems"]')).map(el => el.value);
                } else {
                    this.selectedItems = [];
                }
            },
    
            openMoveModal(charId, groupId, charName) {
                this.targetCharIds = charId;
                this.targetDisplayName = "<b>" + charName + "</b> 캐릭터를";
                this.loadBoardsAndShow(groupId);
            },
    
            openBulkMoveModal() {
                if (this.selectedItems.length === 0) return;
    
                let firstGroupId = this.selectedItems[0].split('_')[1];
                let isSameGroup = this.selectedItems.every(item => item.split('_')[1] === firstGroupId);
    
                if (!isSameGroup) {
                    alert('서로 다른 그룹의 캐릭터를 동시에 이동할 수 없습니다.\n같은 그룹의 캐릭터만 선택해주세요.');
                    return;
                }
    
                this.targetCharIds = this.selectedIds.join(',');
                this.targetDisplayName = "선택한 <b>" + this.selectedIds.length + "명</b>의 캐릭터를";
                this.loadBoardsAndShow(firstGroupId);
            },
    
            loadBoardsAndShow(groupId) {
                this.boards = [];
                fetch('{{ $base_path }}/admin/characters/boards/' + groupId)
                    .then(res => res.json())
                    .then(data => {
                        this.boards = data;
                        this.showModal = true;
                    });
            },
    
            closeModal() {
                this.showModal = false;
            }
        }
    }
</script>

@endsection