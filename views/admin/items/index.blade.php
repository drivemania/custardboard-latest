@extends('layouts.admin')
@section('title', '아이템 관리')
@section('header', '아이템 관리')

@section('content')
@push('styles')

@endpush
<div x-data="itemManager('{{ $base_path }}')">
    
    <div class="flex justify-end items-center mb-6">
        <button @click="openModal()" class="bg-indigo-600 text-white px-4 py-2 rounded font-bold hover:bg-indigo-700">
            + 아이템 등록
        </button>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 text-gray-700 font-bold border-b">
                <tr>
                    <th class="p-3 w-16 text-center">ID</th>
                    <th class="p-3 w-16">아이콘</th>
                    <th class="p-3">이름/설명</th>
                    <th class="p-3 w-32">효과 타입</th>
                    <th class="p-3 w-24">판매가</th>
                    <th class="p-3 w-24 text-center">관리</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach($items as $item)
                <tr class="hover:bg-gray-50">
                    <td class="p-3 text-center text-gray-500">{{ $item->id }}</td>
                    <td class="p-3">
                        @if($item->icon_path)
                            <img src="{{ $base_path . $item->icon_path }}" class="w-10 h-10 rounded border bg-gray-100">
                        @else
                            <div class="w-10 h-10 rounded border bg-gray-100 flex items-center justify-center text-xs">No Img</div>
                        @endif
                    </td>
                    <td class="p-3">
                        <div class="font-bold text-gray-800">{{ $item->name }}</div>
                        <div class="text-xs text-gray-500 truncate max-w-xs">{{ $item->description }}</div>
                    </td>
                    <td class="p-3">
                        <span class="px-2 py-1 rounded text-xs font-bold 
                            {{ $item->effect_type == 'none' ? 'bg-gray-100 text-gray-600' : 
                              ($item->effect_type == 'lottery' ? 'bg-yellow-100 text-yellow-700' : 'bg-purple-100 text-purple-700') }}">
                            {{ $item->effect_type }}
                        </span>
                    </td>
                    <td class="p-3 text-gray-600">
                        @if($item->is_sellable)
                            {{ number_format($item->sell_price) }} P
                        @else
                            <span class="text-red-400 text-xs">판매불가</span>
                        @endif
                    </td>
                    <td class="p-3 text-center">
                        <button @click='editItem(@json($item))' class="text-blue-600 hover:underline mr-2">수정</button>
                        <form action="{{ $base_path }}/admin/items/delete" method="POST" class="inline-block" onsubmit="return confirm('삭제하시겠습니까?')">
                            <input type="hidden" name="id" value="{{ $item->id }}">
                            <button class="text-red-500 hover:underline">삭제</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div x-show="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" x-cloak>
        <div class="bg-white rounded-lg shadow-xl w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
            <form action="{{ $base_path }}/admin/items/store" method="POST" enctype="multipart/form-data" class="p-6">
                
                <h3 class="text-xl font-bold mb-4" x-text="form.id ? '아이템 수정' : '새 아이템 등록'"></h3>
                <input type="hidden" name="id" x-model="form.id">
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">아이템명</label>
                        <input type="text" name="name" x-model="form.name" class="w-full border rounded p-2" required>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">설명</label>
                        <textarea name="description" x-model="form.description" class="w-full border rounded p-2" rows="2"></textarea>
                    </div>

                    <div class="flex gap-4">
                        <div class="flex-1">
                            <label class="block text-sm font-bold text-gray-700 mb-1">아이콘 이미지</label>
                            <input type="file" name="icon" class="w-full text-sm border rounded p-1">
                            <input type="hidden" name="existing_icon_path" x-model="form.icon_path">
                        </div>
                        <template x-if="form.icon_path">
                            <div class="w-16 h-16 shrink-0 border rounded bg-gray-50 flex items-center justify-center">
                                <img :src="basePath + form.icon_path" class="max-w-full max-h-full">
                            </div>
                        </template>
                    </div>
                </div>

                <hr class="my-6 border-gray-200">

                <div class="mb-4">
                    <label class="block text-sm font-bold text-indigo-700 mb-1">✨ 아이템 효과</label>
                    <select name="effect_type" x-model="form.effect_type" class="w-full border border-indigo-200 bg-indigo-50 rounded p-2 font-bold">
                        <option value="none">효과 없음</option>
                        <option value="lottery">복권 (재화 획득)</option>
                        <option value="create_item">아이템 생성권</option>
                        <option value="random_box">랜덤 박스</option>
                    </select>
                </div>

                <div x-show="form.effect_type === 'lottery'" class="bg-yellow-50 p-3 rounded border border-yellow-200 mb-4">
                    <p class="text-xs text-yellow-800 mb-2 font-bold">💰 획득 재화 범위 설정</p>
                    <div class="flex gap-2">
                        <input type="number" name="lottery_min" x-model="form.lottery_min" placeholder="최소" class="w-1/2 border rounded p-2">
                        <span class="py-2">~</span>
                        <input type="number" name="lottery_max" x-model="form.lottery_max" placeholder="최대" class="w-1/2 border rounded p-2">
                    </div>
                </div>

                <div x-show="form.effect_type === 'random_box'" class="bg-purple-50 p-3 rounded border border-purple-200 mb-4">
                    <p class="text-xs text-purple-800 mb-2 font-bold">🎁 구성품 선택</p>
                    
                    <select id="random-box-select" multiple placeholder="구성품을 검색해서 추가하세요..." autocomplete="off" :required="form.effect_type === 'random_box'">
                        <option value="">아이템 선택...</option>
                        @foreach($items as $optItem)
                            <option value="{{ $optItem->id }}">{{ $optItem->name }}</option>
                        @endforeach
                    </select>
                
                    <input type="hidden" name="random_box_json" x-model="form.random_box_json">
                    
                    <p class="text-[10px] text-gray-500 mt-2">
                        ※ 선택된 아이템들은 동일한 확률(가중치 1)로 설정됩니다.
                    </p>
                </div>

                <hr class="my-6 border-gray-200">

                <div class="flex items-center justify-between bg-gray-50 p-3 rounded">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="is_sellable" x-model="form.is_sellable" class="w-4 h-4 text-indigo-600">
                        <span class="ml-2 text-sm font-bold">판매 가능</span>
                    </label>
                    <div x-show="form.is_sellable" class="flex items-center gap-2">
                        <span class="text-sm">판매가:</span>
                        <input type="number" name="sell_price" x-model="form.sell_price" class="w-24 border rounded p-1 text-right">
                        <span class="text-sm">P</span>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" @click="isModalOpen = false" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300 font-bold text-gray-700">취소</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 rounded hover:bg-indigo-700 font-bold text-white">저장</button>
                </div>
            </form>
        </div>
    </div>
</div>
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<script>
function itemManager(basePath = '') {
    return {
        isModalOpen: false,
        tomSelect: null,
        basePath: basePath,
        form: {
            id: null,
            name: '',
            description: '',
            icon_path: '',
            effect_type: 'none',
            is_sellable: false,
            sell_price: 0,
            lottery_min: 0,
            lottery_max: 0,
            random_box_json: ''
        },

        init() {
            this.tomSelect = new TomSelect('#random-box-select', {
                plugins: ['remove_button'],
                create: false,
                maxItems: null,
                valueField: 'value',
                labelField: 'text',
                searchField: 'text',
                onItemAdd: () => this.syncTomSelectToJSON(), 
                onItemRemove: () => this.syncTomSelectToJSON()
            });
        },

        syncTomSelectToJSON() {
            const selectedIds = this.tomSelect.getValue();
            
            if (selectedIds.length === 0) {
                this.form.random_box_json = '';
                return;
            }

            const poolData = {
                pool: selectedIds.map(id => ({
                    item_id: parseInt(id),
                    weight: 1 
                }))
            };

            this.form.random_box_json = JSON.stringify(poolData);
        },

        openModal() {
            this.resetForm();
            this.isModalOpen = true;
        },

        editItem(item) {
            this.form = {
                id: item.id,
                name: item.name,
                description: item.description,
                icon_path: item.icon_path,
                effect_type: item.effect_type,
                is_sellable: item.is_sellable == 1,
                sell_price: item.sell_price,
                lottery_min: 0,
                lottery_max: 0,
                random_box_json: ''
            };

            this.tomSelect.clear(true);

            if (item.effect_data) {
                try {
                    const data = typeof item.effect_data === 'string' ? JSON.parse(item.effect_data) : item.effect_data;
                    
                    if (item.effect_type === 'lottery') {
                        this.form.lottery_min = data.min_point || 0;
                        this.form.lottery_max = data.max_point || 0;
                    } 
                    else if (item.effect_type === 'random_box') {
                        this.form.random_box_json = JSON.stringify(data);
                        
                        if (data.pool && Array.isArray(data.pool)) {
                            const ids = data.pool.map(p => p.item_id.toString());
                            this.tomSelect.setValue(ids, true);
                        }
                    }
                } catch (e) {
                    console.error('JSON parsing error', e);
                }
            }

            this.isModalOpen = true;
        },

        resetForm() {
            this.form = {
                id: null,
                name: '',
                description: '',
                icon_path: '',
                effect_type: 'none',
                is_sellable: false,
                sell_price: 0,
                lottery_min: 0,
                lottery_max: 0,
                random_box_json: ''
            };
            if (this.tomSelect) {
                this.tomSelect.clear(true);
            }
        }
    }
}
</script>
@endpush
@endsection