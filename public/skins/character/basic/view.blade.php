@extends($themeLayout)

@section('content')


<div class="max-w-4xl mx-auto bg-white rounded-lg shadow overflow-hidden border">
    <div class="bg-neutral-800 min-h-[8rem] flex items-end pb-6">
        <div class="w-full max-w-5xl mx-auto px-4 md:px-10">
            <h1 class="text-2xl font-bold text-white break-words leading-tight mt-10 ml-36">
                {{ $character->name }}
            </h1>
        </div>
    </div>
    
    <div class="px-6 pb-6 relative">
        <div class="absolute -top-16 left-6">
            <div class="w-32 h-32 bg-white rounded-full p-1 shadow-lg">
                <img src="{{ $character->image_path }}" class="w-full h-full rounded-full object-cover bg-neutral-200">
            </div>
        </div>

        <div class="ml-40 pt-2 flex justify-between items-start">
            <div>
                <p class="text-neutral-500 mt-1">{{ $owner }}</p>
            </div>
            
            @if(isset($_SESSION['user_idx']) && ($_SESSION['user_idx'] == $character->user_id || $_SESSION['level'] == 10))
            <div class="space-x-2">
                <a href="{{ $currentUrl }}/{{ $character->id }}/edit" class="text-neutral-500 hover:text-amber-600 text-sm font-bold">수정</a>

                <form action="{{ $currentUrl }}/{{ $character->id }}/delete" method="POST" class="inline-block" onsubmit="return confirm('삭제하시겠습니까?')">
                    <input type="hidden" name="id" value="{{ $character->id }}">
                    <button type="submit" class="text-neutral-500 hover:text-red-600 text-sm font-bold">삭제</button>
                </form>
            </div>
            @endif
        </div>
        <div class="mt-8">
            <div class="text-center">
                <p class="text-2xl font-bold mb-3"> " {{ $character->description }} "</p>
                <img src="{{ $character->image_path2 }}" class="inline-block">
            </div>
            <hr class="mb-10">
            @if(!empty($profile))
            <div class="space-y-4">
                @foreach($profile as $item)
                <div class="flex border-b border-neutral-100 pb-2">
                    <span class="w-1/3 text-neutral-500 font-medium pt-1">{{ $item['key'] }}</span>
                    <div class="flex-1 text-neutral-800">
                        
                        @if(isset($item['type']) && $item['type'] === 'file' && $item['value'] != "")
                            @if(preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $item['value']))
                                <img src="{{ $item['value'] }}" class="max-w-xs rounded border">
                            @else
                                <a href="{{ $item['value'] }}" target="_blank" class="text-amber-600 underline">
                                    첨부파일 열기 ({{ basename($item['value']) }})
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
        <div class="mt-8 text-neutral-400 py-4 bg-neutral-50 rounded border">
            <h3 class="text-lg font-bold text-neutral-800 ml-4 flex items-center">
                <span class="mr-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                      </svg>                      
                    </span> {{ $point }}
            </h3>
        </div>
        <div class="mt-8" x-data="titleModal()">
            <h3 class="text-lg font-bold text-neutral-800 mb-4 flex items-center">
                <span class="mr-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 0 0 2.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 0 1 2.916.52 6.003 6.003 0 0 1-5.395 4.972m0 0a6.726 6.726 0 0 1-2.749 1.35m0 0a6.772 6.772 0 0 1-3.044 0" />
                      </svg>
                    </span> 타이틀
            </h3>

            @if(empty($titles) || count($titles) == 0)
                <div class="text-center text-neutral-400 text-sm py-4 bg-neutral-50 rounded border border-dashed">
                    보유 중인 타이틀이 없습니다.
                </div>
            @else
                <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3">
                    @foreach($titles as $title)
                    <div @click="openTitle({{ json_encode($title) }})" 
                         class="cursor-pointer relative group bg-white border rounded-lg p-4 flex flex-col items-center justify-center text-center hover:shadow-md transition-all"
                         class="{{ $title->is_display ? 'border-amber-400 bg-amber-50 shadow-sm' : 'border-neutral-200 hover:border-amber-300' }}">
                        
                        @if($title->is_display)
                            <span class="absolute top-2 left-2 text-[10px] bg-amber-500 text-white px-1.5 py-0.5 rounded font-bold shadow-sm z-10">장착중</span>
                        @endif

                        @if($title->icon_path)
                        <div class="mb-2 flex items-center justify-center">
                            <img src="{{ $base_path }}{{ $title->icon_path }}">
                        </div>
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
                     @click="closeTitle()"></div>

                <div class="bg-white w-full max-w-sm rounded-2xl shadow-2xl overflow-hidden transform transition-all relative z-10"
                     x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                    
                    <button @click="closeTitle()" class="absolute top-3 right-3 text-neutral-400 hover:text-neutral-600 z-20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>

                    <div class="p-6 text-center">
                    <div class="mx-auto mb-4 flex items-center justify-center">
                        <template x-if="selectedTitle.icon_path">
                            <img :src="selectedTitle.icon_path ? '{{ $base_path }}' + selectedTitle.icon_path : ''">
                        </template>
                    </div>

                    <h3 class="text-xl font-bold text-neutral-800 mb-4" x-text="selectedTitle.name"></h3>

                    <div class="bg-neutral-50 rounded-lg p-3 text-sm text-neutral-600 mb-6 text-left h-24 overflow-y-auto custom-scrollbar border border-neutral-100">
                        <span class="whitespace-pre-wrap leading-relaxed" x-text="selectedTitle.description || '상세 설명이 없습니다.'"></span>
                    </div>

                    @if(isset($_SESSION['user_idx']) && $_SESSION['user_idx'] == $character->user_id)

                    <form action="{{ $currentUrl }}/{{ $character->id }}/title/equip" method="POST" x-show="selectedTitle.is_display == 1" x-cloak>
                        <input type="hidden" name="equip_title_id" value="0">
                        <button type="submit" 
                                class="w-full py-2.5 rounded-lg font-bold text-neutral-600 bg-neutral-200 hover:bg-neutral-300 shadow-sm transition transform active:scale-95">
                            장착 해제하기
                        </button>
                    </form>

                    <form action="{{ $currentUrl }}/{{ $character->id }}/title/equip" method="POST" x-show="selectedTitle.is_display != 1" x-cloak>
                        <input type="text" name="equip_title_id" x-model="selectedTitle.id" style="display: none;">
                        <button type="submit" 
                                class="w-full py-2.5 rounded-lg font-bold text-white bg-amber-600 hover:bg-amber-700 shadow-sm transition transform active:scale-95">
                            대표 타이틀로 적용
                        </button>
                    </form>

                    @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-8" x-data="inventoryModal()">
            <h3 class="text-lg font-bold text-neutral-800 mb-4 flex items-center">
                <span class="mr-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                      </svg>                      
                </span> 소지품
            </h3>

            @if($inventory->isEmpty())
                <div class="text-center text-neutral-400 text-sm py-4 bg-neutral-50 rounded border border-dashed">
                    소지하고 있는 아이템이 없습니다.
                </div>
            @else
                <div class="grid grid-cols-4 sm:grid-cols-5 md:grid-cols-6 gap-3">
                    @foreach($inventory as $item)
                    <div @click="openItem({{ json_encode($item) }})" 
                         class="cursor-pointer relative group bg-white border border-neutral-200 rounded-lg aspect-square flex items-center justify-center hover:border-amber-400 hover:shadow-md transition">
                        
                        @if($item->icon_path)
                            <img src="{{ $base_path }}{{ $item->icon_path }}" class="w-2/3 h-2/3 object-contain" alt="{{ $item->name }}">
                        @else
                            <span class="text-2xl">📦</span>
                        @endif

                        @if($item->quantity > 1)
                            <span class="absolute bottom-1 right-1 bg-neutral-800 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full">
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
                    
                    <button @click="closeItem()" class="absolute top-3 right-3 text-neutral-400 hover:text-neutral-600 z-20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>

                    <div x-show="mode === 'view'" class="p-6 text-center">
                        <div class="w-24 h-24 mx-auto mb-4 bg-neutral-50 rounded-full flex items-center justify-center border border-neutral-100">
                        <template x-if="selectedItem.icon_path">
                            <img :src="selectedItem.icon_path ? '{{ $base_path }}' + selectedItem.icon_path : ''" class="w-16 h-16 object-contain">
                        </template>
                            <template x-if="!selectedItem.icon_path">
                                <span class="text-4xl">📦</span>
                            </template>
                        </div>

                        <div class="flex justify-center gap-1 mb-2">
                            <template x-if="selectedItem.is_binding == 1">
                                <span class="text-[10px] bg-red-100 text-red-600 px-1.5 py-0.5 rounded border border-red-200">귀속</span>
                            </template>
                            <template x-if="selectedItem.is_permanent == 1">
                                <span class="text-[10px] bg-green-100 text-green-600 px-1.5 py-0.5 rounded border border-green-200">영구</span>
                            </template>
                        </div>

                        <h3 class="text-xl font-bold text-neutral-800 mb-1" x-text="selectedItem.name"></h3>
                        <p class="text-xs text-neutral-500 font-mono mb-4">보유 수량: <span x-text="selectedItem.quantity"></span>개</p>
                        
                        <div class="bg-neutral-50 rounded-lg p-3 text-sm text-neutral-600 mb-6 text-left h-24 overflow-y-auto custom-scrollbar">
                            <span class="whitespace-pre-wrap" x-text="selectedItem.description || '설명이 없습니다.'"></span>
                        </div>

                        <div x-show="selectedItem.comment" class="bg-yellow-50 rounded-lg p-3 italic text-sm text-yellow-600 mb-6 text-center h-16 overflow-y-auto custom-scrollbar">
                            <span class="whitespace-pre-wrap" x-text="selectedItem.comment"></span>
                        </div>

                        @if(isset($_SESSION['user_idx']) && $_SESSION['user_idx'] == $character->user_id)
                        <div class="grid md:grid-cols-3 gap-3">
                            
                            <template x-if="selectedItem.effect_type === 'create_item'">
                                <button type="button" 
                                    @click="switchMode('create')"
                                    class="w-full py-2.5 rounded-lg font-bold text-white bg-amber-600 hover:bg-amber-700 shadow-sm transition transform active:scale-95">
                                    아이템 생성
                                </button>
                            </template>

                            <template x-if="selectedItem.effect_type !== 'create_item'">
                                <form :action="'{{ $currentUrl }}/item/' + selectedItem.inventory_id + '/use'" method="POST">
                                    <button type="submit" 
                                        class="w-full py-2.5 rounded-lg font-bold text-white shadow-sm transition transform active:scale-95"
                                        :class="selectedItem.effect_type !== 'none' ? 'bg-amber-600 hover:bg-amber-700' : 'bg-neutral-300 cursor-not-allowed'"
                                        :disabled="selectedItem.effect_type === 'none'"
                                        onclick="return confirm('아이템을 사용하시겠습니까?');">
                                        사용하기
                                    </button>
                                </form>
                            </template>

                            <template x-if="selectedItem.is_binding != 1">
                                <button type="button" 
                                    @click="switchMode('gift')"
                                    class="py-2.5 rounded-lg font-bold text-neutral-700 bg-pink-100 hover:bg-pink-200 shadow-sm transition transform active:scale-95 flex flex-col items-center justify-center leading-none">
                                    선물하기
                                </button>
                            </template>

                            <form :action="'{{ $currentUrl }}/item/' + selectedItem.inventory_id + '/sell'" method="POST">
                                <button type="submit" 
                                    class="w-full py-2.5 rounded-lg font-bold text-neutral-700 border border-neutral-300 hover:bg-neutral-50 transition transform active:scale-95 flex flex-col items-center justify-center leading-none"
                                    :class="selectedItem.is_sellable ? '' : 'opacity-50 cursor-not-allowed'"
                                    :disabled="!selectedItem.is_sellable"
                                    onclick="return confirm('아이템을 판매하시겠습니까?');">
                                    <span>판매</span>
                                    <span class="text-[10px] text-neutral-500 mt-1" x-show="selectedItem.is_sellable" x-text="selectedItem.sell_price + ' P'"></span>
                                </button>
                            </form>
                            
                        </div>
                        <p x-show="selectedItem.effect_type === 'none'" class="text-[10px] text-neutral-400 mt-2">※ 사용 효과가 없는 아이템입니다.</p>
                        @endif
                    </div>

                    <div x-show="mode === 'gift'" class="p-6">
                        <h3 class="text-lg font-bold text-pink-600 mb-4 flex items-center">
                            <span class="mr-2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 11.25v8.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 1 0 9.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1 1 14.625 7.5H12m0 0V21m-8.625-9.75h18c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125h-18c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                                  </svg>
                                </span> 선물하기
                        </h3>
                        
                        <div class="bg-neutral-50 p-3 rounded mb-4 flex items-center gap-3">
                        <template x-if="selectedItem.icon_path">
                            <img :src="selectedItem.icon_path ? '{{ $base_path }}' + selectedItem.icon_path : ''" class="w-10 h-10 object-contain rounded bg-white border">
                        </template>
                            <div>
                                <div class="text-xs text-neutral-500">보낼 아이템</div>
                                <div class="font-bold text-sm" x-text="selectedItem.name"></div>
                            </div>
                        </div>

                        <form :action="'{{ $currentUrl }}/item/' + selectedItem.inventory_id + '/gift'" method="POST">
                            <div class="mb-4">
                                <label class="block text-xs font-bold text-neutral-600 mb-1">받는 캐릭터 이름</label>
                                <select id="target-id-select" name="target_id" class="w-full text-sm border-neutral-300 rounded focus:ring-amber-500" required>
                                    <option value="">캐릭터 선택</option>
                                    @foreach($giftCharacters as $char)
                                        <option value="{{ $char->id }}">{{ $char->name }}</option>
                                    @endforeach
                                </select>
                                <p class="text-[10px] text-red-400 mt-1">※ 한 번 보낸 선물은 취소할 수 없습니다.</p>
                            </div>
                            <div class="mb-4">
                                <label class="block text-xs font-bold text-neutral-600 mb-1">코멘트</label>
                                <textarea name="comment" class="w-full text-sm border-neutral-300 rounded focus:ring-amber-500" placeholder="함께 전할 말을 입력하세요." required></textarea>
                            </div>

                            <div class="flex gap-2">
                                <button type="button" @click="mode = 'view'" class="flex-1 py-2 text-sm font-bold text-neutral-600 bg-neutral-100 hover:bg-neutral-200 rounded">
                                    뒤로
                                </button>
                                <button type="submit" class="flex-[2] py-2 text-sm font-bold text-white bg-pink-500 hover:bg-pink-600 rounded" onclick="return confirm('정말로 선물하시겠습니까?');">
                                    선물 보내기
                                </button>
                            </div>
                        </form>
                    </div>

                    <div x-show="mode === 'create'" class="p-6">
                        <h3 class="text-lg font-bold text-amber-700 mb-4 flex items-center">
                            아이템 생성
                        </h3>

                        <form :action="'{{ $currentUrl }}/item/' + selectedItem.inventory_id + '/use'" method="POST" enctype="multipart/form-data">
                            
                            <div class="mb-4 text-center">
                                <label class="inline-block relative cursor-pointer group">
                                    <div class="w-20 h-20 rounded-lg border-2 border-dashed border-neutral-300 flex items-center justify-center bg-neutral-50 overflow-hidden hover:border-amber-400 transition">
                                        <template x-if="previewImage">
                                            <img :src="previewImage" class="w-full h-full object-cover">
                                        </template>
                                        <template x-if="!previewImage">
                                            <div class="text-neutral-400 text-xs text-center px-1">
                                                No Image
                                            </div>
                                        </template>
                                    </div>
                                    <input type="file" name="icon" class="hidden" accept="image/*" @change="handleImageUpload">
                                    <div class="absolute bottom-0 right-0 bg-amber-600 text-white rounded-full p-1 shadow-sm">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    </div>
                                </label>
                                <p class="text-[10px] text-neutral-400 mt-1">클릭하여 아이콘 등록</p>
                            </div>

                            <div class="mb-3">
                                <label class="block text-xs font-bold text-neutral-600 mb-1">아이템 이름</label>
                                <input type="text" name="name" class="w-full text-sm border-neutral-300 rounded focus:ring-amber-500" placeholder="예: 낡은 회중시계" required>
                            </div>

                            <div class="mb-4">
                                <label class="block text-xs font-bold text-neutral-600 mb-1">설명</label>
                                <textarea name="description" rows="3" class="w-full text-sm border-neutral-300 rounded focus:ring-amber-500" placeholder="아이템에 대한 설명을 입력하세요." required></textarea>
                            </div>

                            <div class="flex gap-2">
                                <button type="button" @click="mode = 'view'" class="flex-1 py-2 text-sm font-bold text-neutral-600 bg-neutral-100 hover:bg-neutral-200 rounded">
                                    뒤로
                                </button>
                                <button type="submit" class="flex-[2] py-2 text-sm font-bold text-white bg-amber-600 hover:bg-amber-700 rounded" onclick="return confirm('이 정보로 아이템을 생성하시겠습니까?\n(생성 후 수정 불가, 소모품 소멸)');">
                                    생성 완료
                                </button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>

        <div class="mt-12">
            <h3 class="text-lg font-bold text-neutral-800 mb-4 flex items-center">
                <span class="mr-2">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                        <path fill-rule="evenodd" d="M19.902 4.098a3.75 3.75 0 0 0-5.304 0l-4.5 4.5a3.75 3.75 0 0 0 1.035 6.037.75.75 0 0 1-.646 1.353 5.25 5.25 0 0 1-1.449-8.45l4.5-4.5a5.25 5.25 0 1 1 7.424 7.424l-1.757 1.757a.75.75 0 1 1-1.06-1.06l1.757-1.757a3.75 3.75 0 0 0 0-5.304Zm-7.389 4.267a.75.75 0 0 1 1-.353 5.25 5.25 0 0 1 1.449 8.45l-4.5 4.5a5.25 5.25 0 1 1-7.424-7.424l1.757-1.757a.75.75 0 1 1 1.06 1.06l-1.757 1.757a3.75 3.75 0 1 0 5.304 5.304l4.5-4.5a3.75 3.75 0 0 0-1.035-6.037.75.75 0 0 1-.354-1Z" clip-rule="evenodd" />
                      </svg>
                </span> 관계
            </h3>

            <div id="relation-list" class="grid grid-cols-1 md:grid-cols-1 gap-4">
            @forelse($relations as $rel)
                <div x-data="{ isEditing: false, textContent: `{{ $rel['text'] }}` }" 
                    data-id="{{ $rel['target_id'] }}" 
                    class="bg-neutral-50 border border-neutral-100 rounded-lg p-3 flex items-start relative group hover:shadow-sm transition">
                    
                @if(isset($_SESSION['user_idx']) && $_SESSION['user_idx'] == $character->user_id)
                <div x-show="!isEditing" class="drag-handle cursor-move absolute top-2 left-2 text-neutral-300 hover:text-neutral-500 z-10 p-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path></svg>
                </div>
                <div class="ml-6 w-14 h-14 rounded-full overflow-hidden flex-shrink-0 border border-neutral-200 mr-3 mt-1">
                @else
                <div class="w-14 h-14 rounded-full overflow-hidden flex-shrink-0 border border-neutral-200 mr-3 mt-1">
                @endif
                    <img src="{{ $rel['target_image'] }}" class="w-full h-full object-cover">
                </div>
                
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between">
                        <a href="{{ $currentUrl }}/{{ $rel['target_id'] }}" class="text-sm font-bold text-neutral-800 hover:text-amber-600 hover:underline">
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
        
                <div x-show="!isEditing" class="text-sm text-neutral-600 mt-1 break-words leading-relaxed">
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
                        <label class="text-xs font-bold text-neutral-500">호감도</label>
                        <input type="number" name="favor" value="{{ $rel['favor'] }}" min="-5" max="5" class="w-20 text-sm border-neutral-300 rounded focus:ring-amber-500 px-2 py-1">
                    </div>

                    <textarea name="relation_text" 
                            rows="3" 
                            class="w-full text-sm border-neutral-300 rounded focus:ring-amber-500 mb-2" 
                            required>{{ str_replace('<br />', "\n", $rel['text']) }}</textarea>

                    <div class="flex justify-end space-x-2">
                        <button type="button" @click="isEditing = false" class="text-xs bg-neutral-200 hover:bg-neutral-300 text-neutral-700 px-3 py-1 rounded font-bold">
                            취소
                        </button>
                        <button type="submit" class="text-xs bg-amber-600 hover:bg-amber-700 text-white px-3 py-1 rounded font-bold">
                            저장
                        </button>
                    </div>
                </form>
                @endif
            </div>

        @if(isset($_SESSION['user_idx']) && $_SESSION['user_idx'] == $character->user_id)
        <div x-show="!isEditing" class="absolute -top-2 -right-2 flex space-x-1 opacity-0 group-hover:opacity-100 transition">
            
            <button type="button" @click="isEditing = true" class="bg-amber-500 text-white rounded-full p-1 shadow hover:bg-amber-600">
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
            <div class="mt-6 bg-white border border-neutral-200 rounded-lg p-4 shadow-sm">
                <form action="{{ $currentUrl }}/{{ $character->id }}/relation/add" method="POST" class="flex flex-col sm:flex-row gap-3 items-end sm:items-center">
                    
                    <div class="w-full sm:w-auto">
                        <label class="block text-xs font-bold text-neutral-500 mb-1">대상</label>
                        <select id="otherChar-select" name="to_char_id" class="w-full text-sm border-neutral-300 rounded focus:ring-amber-500" required>
                            <option value="">캐릭터 선택</option>
                            @foreach($otherCharacters as $char)
                                <option value="{{ $char->id }}">{{ $char->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="w-24 flex-shrink-0">
                        <label class="block text-xs font-bold text-neutral-500 mb-1">호감도(-5~5)</label>
                        <input type="number" name="favor" value="0" min="-5" max="5" class="w-full text-sm border-neutral-300 rounded focus:ring-amber-500">
                    </div>

                    <div class="flex-1 w-full">
                        <label class="block text-xs font-bold text-neutral-500 mb-1">관계 설명</label>
                        <textarea name="relation_text" placeholder="관계 설명을 입력하세요." class="w-full text-sm border-neutral-300 rounded focus:ring-amber-500" required></textarea>
                    </div>

                    <button type="submit" class="w-full sm:w-auto bg-amber-600 text-white text-sm px-4 py-2 rounded hover:bg-amber-700 font-bold h-9 mt-auto">
                        추가
                    </button>
                </form>
            </div>
            @endif
        </div>

        <div class="mt-8 text-center">
            <a href="{{ $currentUrl }}" class="inline-block bg-neutral-100 text-neutral-600 px-6 py-2 rounded-full font-bold hover:bg-neutral-200">
                목록으로 돌아가기
            </a>
        </div>
    </div>
</div>

<script>
    window.titleModal = function() {
        return {
            isOpen: false,
            selectedTitle: {},

            openTitle(title) {
                this.selectedTitle = title;
                this.isOpen = true;
            },
            closeTitle() {
                this.isOpen = false;
            }
        }
    }

    window.inventoryModal = function() {
        return {
            isOpen: false,
            mode: 'view', 
            selectedItem: {},
            previewImage: null,

            openItem(item) {
                this.selectedItem = item;
                this.mode = 'view';
                this.previewImage = null;
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
                    reader.onload = (e) => { this.previewImage = e.target.result; };
                    reader.readAsDataURL(file);
                }
            }
        }
    }

    function initTomSelects() {
        if(document.getElementById('otherChar-select')) {
            new TomSelect("#otherChar-select", {
                create: false,
                sortField: { field: "text", direction: "asc" },
                placeholder: "캐릭터 이름을 입력하세요...",
                plugins: ['clear_button'],
            });
        }
        if(document.getElementById('target-id-select')) {
            new TomSelect("#target-id-select", {
                create: false,
                sortField: { field: "text", direction: "asc" },
                placeholder: "캐릭터 이름을 입력하세요...",
                plugins: ['clear_button'],
            });
        }
    }
    
    if(typeof TomSelect !== 'undefined') initTomSelects();


</script>

@if(isset($_SESSION['user_idx']) && $_SESSION['user_idx'] == $character->user_id)
<script>
    function initSortable() {
        var el = document.getElementById('relation-list');
        if(el) {
            Sortable.create(el, {
                animation: 150,
                handle: '.drag-handle',
                ghostClass: 'bg-amber-50',
                onEnd: function (evt) {
                    var order = this.toArray();
                    fetch("{{ $currentUrl }}/{{ $character->id }}/relation/reorder", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ order: order })
                    })
                    .then(response => {
                        if (!response.ok) {
                            alert('순서 저장에 실패했습니다.');
                        }
                    });
                }
            });
        }
    }
    
    if(typeof Sortable !== 'undefined') initSortable();
</script>
@endif

@endsection