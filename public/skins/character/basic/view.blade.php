@extends($themeLayout)

@section('content')

@push('styles')
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
@endpush

<div class="max-w-4xl mx-auto bg-white rounded-lg shadow overflow-hidden border">
    <div class="bg-gray-800 min-h-[8rem] flex items-end pb-6">
        <div class="w-full max-w-5xl mx-auto px-4 md:px-10">
            <h1 class="text-2xl font-bold text-white break-words leading-tight mt-10 ml-36">
                {{ $character->name }}
            </h1>
        </div>
    </div>
    
    <div class="px-6 pb-6 relative">
        <div class="absolute -top-16 left-6">
            <div class="w-32 h-32 bg-white rounded-full p-1 shadow-lg">
                <img src="{{ $character->image_path }}" class="w-full h-full rounded-full object-cover bg-gray-200">
            </div>
        </div>

        <div class="ml-40 pt-2 flex justify-between items-start">
            <div>
                <p class="text-gray-500 mt-1">{{ $owner }}</p>
            </div>
            
            @if(isset($_SESSION['user_idx']) && $_SESSION['user_idx'] == $character->user_id)
            <div class="space-x-2">
                <a href="{{ $currentUrl }}/{{ $character->id }}/edit" class="text-gray-500 hover:text-blue-600 text-sm font-bold">수정</a>

                <form action="{{ $currentUrl }}/{{ $character->id }}/delete" method="POST" class="inline-block" onsubmit="return confirm('삭제하시겠습니까?')">
                    <input type="hidden" name="id" value="{{ $character->id }}">
                    <button type="submit" class="text-gray-500 hover:text-red-600 text-sm font-bold">삭제</button>
                </form>
            </div>
            @endif
        </div>
        <div class="mt-8">
            <div class="text-center">
                <p class="text-2xl font-bold"> " {{ $character->description }} "</p>
                <img src="{{ $character->image_path2 }}" class="inline-block">
            </div>
            <hr class="mb-10">
            @if(!empty($profile))
            <div class="space-y-4">
                @foreach($profile as $item)
                <div class="flex border-b border-gray-100 pb-2">
                    <span class="w-1/3 text-gray-500 font-medium pt-1">{{ $item['key'] }}</span>
                    <div class="flex-1 text-gray-800">
                        
                        @if(isset($item['type']) && $item['type'] === 'file' && $item['value'] != "")
                            @if(preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $item['value']))
                                <img src="{{ $item['value'] }}" class="max-w-xs rounded border">
                            @else
                                <a href="{{ $item['value'] }}" target="_blank" class="text-blue-600 underline">
                                    💾 첨부파일 열기 ({{ basename($item['value']) }})
                                </a>
                            @endif

                        @elseif(isset($item['type']) && $item['type'] === 'textarea')
                            <div class="whitespace-pre-wrap">{!! $item['value'] !!}</div>
                        
                        @else
                            {{ $item['value'] }}
                        @endif

                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
        <div class="mt-8 text-gray-400 py-4 bg-gray-50 rounded border">
            <h3 class="text-lg font-bold text-gray-800 ml-4 flex items-center">
                <span class="mr-2">💰</span> {{ $point }}
            </h3>
        </div>
        <div class="mt-8" x-data="inventoryModal()">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                <span class="mr-2">🎒</span> 소지품
            </h3>

            @if($inventory->isEmpty())
                <div class="text-center text-gray-400 text-sm py-4 bg-gray-50 rounded border border-dashed">
                    소지하고 있는 아이템이 없습니다.
                </div>
            @else
                <div class="grid grid-cols-4 sm:grid-cols-5 md:grid-cols-6 gap-3">
                    @foreach($inventory as $item)
                    <div @click="openItem({{ json_encode($item) }})" 
                         class="cursor-pointer relative group bg-white border border-gray-200 rounded-lg aspect-square flex items-center justify-center hover:border-indigo-400 hover:shadow-md transition">
                        
                        @if($item->icon_path)
                            <img src="{{ $base_path }}{{ $item->icon_path }}" class="w-2/3 h-2/3 object-contain" alt="{{ $item->name }}">
                        @else
                            <span class="text-2xl">📦</span>
                        @endif

                        @if($item->quantity > 1)
                            <span class="absolute bottom-1 right-1 bg-gray-800 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full">
                                {{ $item->quantity }}
                            </span>
                        @endif
                        <div class="absolute inset-0 bg-black/5 rounded-lg opacity-0 group-hover:opacity-100 transition"></div>
                    </div>
                    @endforeach
                </div>
            @endif

            <div x-show="isOpen" class="fixed inset-0 z-50 flex items-center justify-center px-4" style="display: none;" x-cloak>
                
                <div class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" 
                     x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                     @click="closeItem()"></div>

                <div class="bg-white w-full max-w-sm rounded-2xl shadow-2xl overflow-hidden transform transition-all relative z-10"
                     x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                    
                    <button @click="closeItem()" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 z-20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>

                    <div x-show="mode === 'view'" class="p-6 text-center">
                        <div class="w-24 h-24 mx-auto mb-4 bg-gray-50 rounded-full flex items-center justify-center border border-gray-100">
                            <template x-if="selectedItem.icon_path">
                                <img :src="'{{ $base_path }}' + selectedItem.icon_path" class="w-16 h-16 object-contain">
                            </template>
                            <template x-if="!selectedItem.icon_path">
                                <span class="text-4xl">📦</span>
                            </template>
                        </div>

                        <h3 class="text-xl font-bold text-gray-800 mb-1" x-text="selectedItem.name"></h3>
                        <p class="text-xs text-gray-500 font-mono mb-4">보유 수량: <span x-text="selectedItem.quantity"></span>개</p>
                        
                        <div class="bg-gray-50 rounded-lg p-3 text-sm text-gray-600 mb-6 text-left h-24 overflow-y-auto custom-scrollbar">
                            <span class="whitespace-pre-wrap" x-text="selectedItem.description || '설명이 없습니다.'"></span>
                        </div>

                        @if(isset($_SESSION['user_idx']) && $_SESSION['user_idx'] == $character->user_id)
                        <div class="grid grid-cols-2 gap-3">
                            
                            <template x-if="selectedItem.effect_type === 'create_item'">
                                <button type="button" 
                                    @click="switchMode('create')"
                                    class="w-full py-2.5 rounded-lg font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition transform active:scale-95">
                                    아이템 생성
                                </button>
                            </template>

                            <template x-if="selectedItem.effect_type !== 'create_item'">
                                <form :action="'{{ $currentUrl }}/item/' + selectedItem.inventory_id + '/use'" method="POST">
                                    <button type="submit" 
                                        class="w-full py-2.5 rounded-lg font-bold text-white shadow-sm transition transform active:scale-95"
                                        :class="selectedItem.effect_type !== 'none' ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-gray-300 cursor-not-allowed'"
                                        :disabled="selectedItem.effect_type === 'none'"
                                        onclick="return confirm('아이템을 사용하시겠습니까?');">
                                        사용하기
                                    </button>
                                </form>
                            </template>

                            <form :action="'{{ $currentUrl }}/item/' + selectedItem.inventory_id + '/sell'" method="POST">
                                <button type="submit" 
                                    class="w-full py-2.5 rounded-lg font-bold text-gray-700 border border-gray-300 hover:bg-gray-50 transition transform active:scale-95 flex flex-col items-center justify-center leading-none"
                                    :class="selectedItem.is_sellable ? '' : 'opacity-50 cursor-not-allowed'"
                                    :disabled="!selectedItem.is_sellable"
                                    onclick="return confirm('아이템을 판매하시겠습니까?');">
                                    <span>판매</span>
                                    <span class="text-[10px] text-gray-500 mt-1" x-show="selectedItem.is_sellable" x-text="selectedItem.sell_price + ' P'"></span>
                                </button>
                            </form>
                        </div>
                        <p x-show="selectedItem.effect_type === 'none'" class="text-[10px] text-gray-400 mt-2">※ 사용 효과가 없는 아이템입니다.</p>
                        @endif
                    </div>

                    <div x-show="mode === 'create'" class="p-6">
                        <h3 class="text-lg font-bold text-indigo-700 mb-4 flex items-center">
                            아이템 생성
                        </h3>

                        <form :action="'{{ $currentUrl }}/item/' + selectedItem.inventory_id + '/use'" method="POST" enctype="multipart/form-data">
                            
                            <div class="mb-4 text-center">
                                <label class="inline-block relative cursor-pointer group">
                                    <div class="w-20 h-20 rounded-lg border-2 border-dashed border-gray-300 flex items-center justify-center bg-gray-50 overflow-hidden hover:border-indigo-400 transition">
                                        <template x-if="previewImage">
                                            <img :src="previewImage" class="w-full h-full object-cover">
                                        </template>
                                        <template x-if="!previewImage">
                                            <div class="text-gray-400 text-xs text-center px-1">
                                                <span class="block text-xl mb-1">📷</span>
                                                이미지
                                            </div>
                                        </template>
                                    </div>
                                    <input type="file" name="icon" class="hidden" accept="image/*" @change="handleImageUpload">
                                    <div class="absolute bottom-0 right-0 bg-indigo-600 text-white rounded-full p-1 shadow-sm">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    </div>
                                </label>
                                <p class="text-[10px] text-gray-400 mt-1">클릭하여 아이콘 등록</p>
                            </div>

                            <div class="mb-3">
                                <label class="block text-xs font-bold text-gray-600 mb-1">아이템 이름</label>
                                <input type="text" name="name" class="w-full text-sm border-gray-300 rounded focus:ring-indigo-500" placeholder="예: 낡은 회중시계" required>
                            </div>

                            <div class="mb-4">
                                <label class="block text-xs font-bold text-gray-600 mb-1">설명</label>
                                <textarea name="description" rows="3" class="w-full text-sm border-gray-300 rounded focus:ring-indigo-500" placeholder="아이템에 대한 설명을 입력하세요." required></textarea>
                            </div>

                            <div class="flex gap-2">
                                <button type="button" @click="mode = 'view'" class="flex-1 py-2 text-sm font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded">
                                    뒤로
                                </button>
                                <button type="submit" class="flex-[2] py-2 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded" onclick="return confirm('이 정보로 아이템을 생성하시겠습니까?\n(생성 후 수정 불가, 소모품 소멸)');">
                                    생성 완료
                                </button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>

        <div class="mt-12">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                <span class="mr-2">🧩</span> 관계
            </h3>

            <div id="relation-list" class="grid grid-cols-1 md:grid-cols-1 gap-4">
            @forelse($relations as $rel)
                <div x-data="{ isEditing: false, textContent: `{{ $rel['text'] }}` }" 
                    data-id="{{ $rel['target_id'] }}" 
                    class="bg-gray-50 border border-gray-100 rounded-lg p-3 flex items-start relative group hover:shadow-sm transition">
                    
                @if(isset($_SESSION['user_idx']) && $_SESSION['user_idx'] == $character->user_id)
                <div x-show="!isEditing" class="drag-handle cursor-move absolute top-2 left-2 text-gray-300 hover:text-gray-500 z-10 p-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path></svg>
                </div>
                <div class="ml-6 w-14 h-14 rounded-full overflow-hidden flex-shrink-0 border border-gray-200 mr-3 mt-1">
                @else
                <div class="w-14 h-14 rounded-full overflow-hidden flex-shrink-0 border border-gray-200 mr-3 mt-1">
                @endif
                    <img src="{{ $rel['target_image'] }}" class="w-full h-full object-cover">
                </div>
                
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between">
                        <a href="{{ $currentUrl }}/{{ $rel['target_id'] }}" class="text-sm font-bold text-gray-800 hover:text-indigo-600 hover:underline">
                            {{ $rel['target_name'] }}
                        </a>
            
                    <span x-show="!isEditing" class="text-xs font-mono px-2 py-0.5 rounded bg-white border">
                        @if($rel['favor'] > 4)
                            💖 +{{ $rel['favor'] }}
                        @elseif($rel['favor'] > 0)
                            ❤️ +{{ $rel['favor'] }}
                        @elseif($rel['favor'] < -4)
                            💀 {{ $rel['favor'] }}
                        @elseif($rel['favor'] < 0)
                            💔 {{ $rel['favor'] }}
                        @else
                            😶 0
                        @endif
                    </span>
                </div>
        
                <div x-show="!isEditing" class="text-sm text-gray-600 mt-1 break-words leading-relaxed">
                    {!! $rel['text'] !!}
                </div>

                @if(isset($_SESSION['user_idx']) && $_SESSION['user_idx'] == $character->user_id)
                <form x-show="isEditing" 
                    action="{{ $currentUrl }}/{{ $character->id }}/relation/update" 
                    method="POST" 
                    class="mt-2"
                    x-cloak>
                    
                    <input type="hidden" name="target_id" value="{{ $rel['target_id'] }}">
                    
                    <div class="mb-2 flex items-center gap-2">
                        <label class="text-xs font-bold text-gray-500">호감도</label>
                        <input type="number" name="favor" value="{{ $rel['favor'] }}" min="-5" max="5" class="w-20 text-sm border-gray-300 rounded focus:ring-indigo-500 px-2 py-1">
                    </div>

                    <textarea name="relation_text" 
                            rows="3" 
                            class="w-full text-sm border-gray-300 rounded focus:ring-indigo-500 mb-2" 
                            required>{{ str_replace('<br />', "\n", $rel['text']) }}</textarea>

                    <div class="flex justify-end space-x-2">
                        <button type="button" @click="isEditing = false" class="text-xs bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-1 rounded font-bold">
                            취소
                        </button>
                        <button type="submit" class="text-xs bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1 rounded font-bold">
                            저장
                        </button>
                    </div>
                </form>
                @endif
            </div>

        @if(isset($_SESSION['user_idx']) && $_SESSION['user_idx'] == $character->user_id)
        <div x-show="!isEditing" class="absolute -top-2 -right-2 flex space-x-1 opacity-0 group-hover:opacity-100 transition">
            
            <button type="button" @click="isEditing = true" class="bg-blue-500 text-white rounded-full p-1 shadow hover:bg-blue-600">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
            </button>

            <form action="{{ $currentUrl }}/{{ $character->id }}/relation/delete" method="POST" onsubmit="return confirm('삭제하시겠습니까?');">
                <input type="hidden" name="target_id" value="{{ $rel['target_id'] }}">
                <button type="submit" class="bg-red-500 text-white rounded-full p-1 shadow hover:bg-red-600">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </form>
        </div>
        @endif
    </div>
@empty
@endforelse
</div>

            @if(isset($_SESSION['user_idx']) && $_SESSION['user_idx'] == $character->user_id)
            <div class="mt-6 bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                <form action="{{ $currentUrl }}/{{ $character->id }}/relation/add" method="POST" class="flex flex-col sm:flex-row gap-3 items-end sm:items-center">
                    
                    <div class="w-full sm:w-auto">
                        <label class="block text-xs font-bold text-gray-500 mb-1">대상</label>
                        <select id="otherChar-select" name="to_char_id" class="w-full text-sm border-gray-300 rounded focus:ring-indigo-500" required>
                            <option value="">캐릭터 선택</option>
                            @foreach($otherCharacters as $char)
                                <option value="{{ $char->id }}">{{ $char->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="w-24 flex-shrink-0">
                        <label class="block text-xs font-bold text-gray-500 mb-1">호감도(-5~5)</label>
                        <input type="number" name="favor" value="0" min="-5" max="5" class="w-full text-sm border-gray-300 rounded focus:ring-indigo-500">
                    </div>

                    <div class="flex-1 w-full">
                        <label class="block text-xs font-bold text-gray-500 mb-1">관계 설명 (HTML 가능)</label>
                        <textarea name="relation_text" placeholder="예: <b>짝사랑</b>. 몰래 지켜봄." class="w-full text-sm border-gray-300 rounded focus:ring-indigo-500" required></textarea>
                    </div>

                    <button type="submit" class="w-full sm:w-auto bg-indigo-600 text-white text-sm px-4 py-2 rounded hover:bg-indigo-700 font-bold h-9 mt-auto">
                        추가
                    </button>
                </form>
            </div>
            @endif
        </div>

        <div class="mt-8 text-center">
            <a href="{{ $currentUrl }}" class="inline-block bg-gray-100 text-gray-600 px-6 py-2 rounded-full font-bold hover:bg-gray-200">
                목록으로 돌아가기
            </a>
        </div>
    </div>
</div>
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        new TomSelect("#otherChar-select", {
            create: false,
            sortField: {
                field: "text",
                direction: "asc"
            },
            placeholder: "캐릭터 이름을 입력하세요...",
            plugins: ['clear_button'],
        });
    });

    function inventoryModal() {
        return {
            isOpen: false,
            mode: 'view', // 'view' | 'create'
            selectedItem: {},
            previewImage: null,

            openItem(item) {
                this.selectedItem = item;
                this.mode = 'view';
                this.previewImage = null; // 미리보기 초기화
                this.isOpen = true;
            },
            closeItem() {
                this.isOpen = false;
                this.mode = 'view';
            },
            switchMode(mode) {
                this.mode = mode;
            },
            handleImageUpload(event) {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.previewImage = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            }
        }
    }
</script>
@if(isset($_SESSION['user_idx']) && $_SESSION['user_idx'] == $character->user_id)

<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var el = document.getElementById('relation-list');
    
    if(el) {
        var sortable = Sortable.create(el, {
            animation: 150,
            handle: '.drag-handle',
            ghostClass: 'bg-indigo-50',
            onEnd: function (evt) {
                var order = sortable.toArray(); 
                fetch("{{ $currentUrl }}/{{ $character->id }}/relation/reorder", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        order: order
                    })
                })
                .then(response => {
                    if (response.ok) {
                        console.log('순서 저장 완료');
                    } else {
                        alert('순서 저장에 실패했습니다.');
                    }
                });
            }
        });
    }
});
</script>
@endif
@endpush


@endsection