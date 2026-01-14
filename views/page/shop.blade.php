@extends($themeLayout)

@section('title', $title)

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8" x-data="shopClient()">
    
    <div class="relative mb-16 mt-6">
        <div class="absolute bottom-0 w-full h-1/3 bg-gray-100 rounded-3xl -z-10"></div>

        <div class="flex flex-col md:flex-row items-center md:items-end gap-6 md:gap-10 px-4">
            
            <div class="w-40 md:w-56 shrink-0 relative z-10 flex justify-center">
                @if($shop->npc_image_path)
                    <img src="{{ $base_path }}{{ $shop->npc_image_path }}" 
                         class="w-full h-auto object-contain drop-shadow-2xl filter hover:brightness-110 transition duration-300 origin-bottom hover:scale-105"
                         style="max-height: 300px;">
                @else
                    <div class="w-32 h-32 md:w-40 md:h-40 rounded-full bg-gray-200 border-4 border-white shadow-lg flex items-center justify-center text-5xl text-gray-400">
                        👤
                    </div>
                @endif
                
                <div class="absolute -bottom-3 bg-gray-800 text-white text-xs font-bold px-3 py-1 rounded-full border-2 border-white shadow-md">
                    {{ $shop->npc_name ?? "NPC" }}
                </div>
            </div>

            <div class="flex-1 w-full relative z-0 mb-4 md:mb-8">
                <div class="relative bg-white border-2 border-gray-800 rounded-2xl p-6 md:p-8 shadow-[8px_8px_0px_0px_rgba(0,0,0,0.15)]">
                    
                    <div class="absolute 
                                -top-3 left-1/2 -translate-x-1/2 
                                md:top-auto md:bottom-8 md:-left-3 md:translate-x-0 md:translate-y-0
                                w-6 h-6 bg-white border-t-2 border-l-2 border-gray-800 
                                transform rotate-45 md:-rotate-45">
                    </div>

                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <h1 class="text-2xl md:text-3xl font-black text-gray-800 tracking-tight">
                                {{ $shop->name }}
                            </h1>
                        </div>
                        
                        <div class="bg-yellow-50 border-2 border-yellow-400 rounded-lg px-3 py-1.5 text-right shadow-sm">
                            <p class="text-[10px] text-yellow-700 font-bold uppercase tracking-wider">My Point</p>
                            <p class="text-lg font-bold text-gray-800 font-mono leading-none">
                                {{ number_format($userPoint) }} <span class="text-sm">{{ $group->point_name }}</span>
                            </p>
                        </div>
                    </div>

                    <hr class="border-gray-200 border-dashed my-3">

                    <div class="min-h-[3rem] flex items-center">
                        <p class="text-gray-700 text-lg md:text-xl font-medium leading-relaxed">
                            "{{ $shop->description ?: '' }}"
                        </p>
                    </div>
                    
                    <div class="absolute bottom-3 right-4 animate-bounce text-amber-500">
                        ▼
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @foreach($items as $item)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md hover:border-amber-200 transition group flex flex-col h-full relative">
            
            <div class="absolute top-3 left-3 flex flex-col gap-1 z-10">
                @if($item->is_binding)
                    <span class="bg-red-100 text-red-600 text-[10px] font-bold px-2 py-0.5 rounded border border-red-200 shadow-sm">귀속</span>
                @endif
                @if($item->is_permanent)
                    <span class="bg-green-100 text-green-600 text-[10px] font-bold px-2 py-0.5 rounded border border-green-200 shadow-sm">영구</span>
                @endif
            </div>

            <div class="p-6 pb-0 flex justify-center">
                <div class="w-24 h-24 rounded-lg bg-gray-50 border border-gray-100 flex items-center justify-center group-hover:scale-110 transition duration-300">
                    @if($item->icon_path)
                        <img src="{{ $base_path }}{{ $item->icon_path }}" class="w-16 h-16 object-contain">
                    @else
                        <span class="text-4xl">📦</span>
                    @endif
                </div>
            </div>

            <div class="p-5 flex-1 flex flex-col text-center">
                <h3 class="font-bold text-gray-800 mb-1 truncate">{{ $item->name }}</h3>
                <p class="text-xs text-gray-500 line-clamp-2 mb-3 h-8">{{ $item->description }}</p>
                
                <div class="mt-auto pt-3 border-t border-gray-100">
                    <div class="text-lg font-bold text-amber-700 font-mono">
                        {{ number_format($item->price) }} P
                    </div>
                    
                    @if($item->purchase_limit > 0)
                        <div class="text-[10px] text-gray-400 mt-1">
                            (구매 제한: {{ $item->purchase_limit }}개)
                        </div>
                    @endif
                </div>
            </div>

            <div class="p-4 pt-0">
                <button type="button" 
                    @click="openBuyModal({{ json_encode($item) }})"
                    class="w-full py-2 rounded-lg font-bold text-sm transition shadow-sm
                    {{ $userPoint >= $item->price ? 'bg-amber-600 text-white hover:bg-amber-700' : 'bg-gray-300 text-gray-500 cursor-not-allowed' }}"
                    {{ $userPoint < $item->price ? 'disabled' : '' }}>
                    {{ $userPoint >= $item->price ? '구매하기' : '포인트 부족' }}
                </button>
            </div>
        </div>
        @endforeach
    </div>

    <div x-show="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center px-4" x-cloak style="display: none;">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" @click="closeModal()"></div>

        <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl relative z-10 overflow-hidden transform transition-all">
            
            <div class="bg-amber-600 p-4 text-white flex justify-between items-center">
                <h3 class="font-bold">아이템 구매</h3>
                <button @click="closeModal()" class="text-white/80 hover:text-white">✕</button>
            </div>

            <form action="{{ $currentUrl }}/purchase" method="POST" class="p-6">
                <input type="hidden" name="item_id" :value="selectedItem.id">
                
                <div class="flex items-center gap-4 mb-6 pb-6 border-b">
                    <div class="w-16 h-16 bg-gray-50 rounded border flex items-center justify-center shrink-0">
                        <template x-if="selectedItem.icon_path">
                            <img :src="'{{ $base_path }}' + selectedItem.icon_path" class="w-10 h-10 object-contain">
                        </template>
                        <template x-if="!selectedItem.icon_path">
                            <span class="text-2xl">📦</span>
                        </template>
                    </div>
                    <div>
                        <div class="font-bold text-lg text-gray-800" x-text="selectedItem.name"></div>
                        <div class="text-amber-600 font-bold font-mono" x-text="formatNumber(selectedItem.price) + ' P'"></div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-600 mb-1">구매 대상 캐릭터</label>
                    <select name="target_character_id" class="w-full border-gray-300 rounded-lg text-sm focus:ring-amber-500" required>
                        @foreach($myCharacters as $char)
                            <option value="{{ $char->id }}">
                                {{ $char->name }} 
                                @if($loop->first) (대표) @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-6">
                    <label class="block text-xs font-bold text-gray-600 mb-1">구매 수량</label>
                    <div class="flex items-center gap-3">
                        <div class="flex items-center border border-gray-300 rounded-lg overflow-hidden w-32">
                            <button type="button" @click="quantity > 1 ? quantity-- : null" class="w-8 h-10 bg-gray-50 hover:bg-gray-100 text-gray-600 border-r">-</button>
                            <input type="number" name="quantity" x-model="quantity" min="1" class="w-full h-10 text-center border-none focus:ring-0 text-sm font-bold" readonly>
                            <button type="button" @click="quantity++" class="w-8 h-10 bg-gray-50 hover:bg-gray-100 text-gray-600 border-l">+</button>
                        </div>
                        <div class="text-xs text-gray-500">
                            총 결제 금액: <span class="font-bold text-amber-600 text-base" x-text="formatNumber(totalPrice)"></span> P
                        </div>
                    </div>
                    <p x-show="totalPrice > {{ $userPoint }}" class="text-xs text-red-500 mt-2 font-bold animate-pulse">
                        ⚠️ 보유 포인트가 부족합니다!
                    </p>
                </div>

                <button type="submit" 
                    class="w-full py-3 rounded-lg font-bold text-white shadow-lg transition transform active:scale-95 flex justify-center items-center gap-2"
                    :class="totalPrice > {{ $userPoint }} ? 'bg-gray-400 cursor-not-allowed' : 'bg-amber-600 hover:bg-amber-700'"
                    :disabled="totalPrice > {{ $userPoint }}">
                    <span>구매 확정</span>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    window.shopClient = function() {
        return {
            isModalOpen: false,
            selectedItem: {},
            quantity: 1,
            
            get totalPrice() {
                return (this.selectedItem.price || 0) * this.quantity;
            },

            openBuyModal(item) {
                this.selectedItem = item;
                this.quantity = 1;
                this.isModalOpen = true;
            },

            closeModal() {
                this.isModalOpen = false;
            },

            formatNumber(num) {
                return new Intl.NumberFormat().format(num);
            }
        }
    };
</script>

@endsection