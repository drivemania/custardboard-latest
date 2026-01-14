@extends('layouts.admin')
@section('title', '메뉴 관리 > ' . $group->name)
@section('header', '메뉴 관리 > ' . $group->name)

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    <div class="bg-white p-6 rounded-lg shadow-sm">
        <h3 class="font-bold text-lg mb-4 border-b pb-2 flex justify-between items-center">
            <span>📋 현재 메뉴 목록</span>
            <span class="text-xs text-neutral-400 font-normal">드래그하여 순서 변경 가능</span>
        </h3>
        
        <ul class="space-y-3" id="menu-list">
            @foreach($menus as $menu)
            <li data-id="{{ $menu->id }}" class="flex justify-between items-center bg-neutral-50 p-3 rounded border hover:shadow-sm transition cursor-move group">
                <div class="flex items-center">
                    <div class="mr-3 text-neutral-400 group-hover:text-amber-400 cursor-grab">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </div>

                    <div>
                        @php
                        if($menu->type == 'link'){
                            $href = $menu->target_url;
                        }elseif($menu->type == 'shop'){
                            $href = $base_path.'/au/'.$group->slug.'/shop/'.$menu->target_id;
                        }else{
                            $href = $base_path.'/au/'.$group->slug.'/'.$menu->slug;
                        }
                        @endphp
                        <span class="font-bold text-neutral-800">{{ $menu->title }}</span>
                        <a class="text-xs text-neutral-500 block mt-1" href="{{ $href }}" target="_blank">
                            URL: <span class="text-amber-500 font-mono">{{ ($menu->slug && $menu->slug != "") ? $menu->slug : ($menu->type == 'shop' ? $menu->title : $menu->target_url) }}</span>
                        </a>
                        
                        <div class="mt-1">
                            @if($menu->type === 'board')
                                <span class="text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded">
                                    📄 게시판: {{ $menu->board_title }} (ID:{{ $menu->target_id }})
                                </span>
                            @elseif($menu->type === 'load')
                                <span class="text-xs bg-yellow-100 text-green-700 px-2 py-0.5 rounded">
                                    🎨 로드비 게시판: {{ $menu->board_title }} (ID:{{ $menu->target_id }})
                                </span>
                            @elseif($menu->type === 'character')
                                <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded">
                                    🧙‍♂️ 캐릭터 게시판: {{ $menu->board_title }} (ID:{{ $menu->target_id }})
                                </span>
                            @elseif($menu->type === 'page')
                                <span class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded">
                                    📑 페이지: {{ $menu->board_title }} (ID:{{ $menu->target_id }})
                                </span>
                            @elseif($menu->type === 'shop')
                                <span class="text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded">
                                    🏪 상점: {{ $menu->board_title }} (ID:{{ $menu->target_id }})
                                </span>
                            @else
                                <span class="text-xs bg-neutral-100 text-neutral-700 px-2 py-0.5 rounded">
                                    🔗 링크
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <form action="{{ $base_path }}/admin/menus/delete" method="POST" onsubmit="return confirm('이 메뉴를 삭제하시겠습니까?');" class="ml-2">
                    <input type="hidden" name="menu_id" value="{{ $menu->id }}">
                    <input type="hidden" name="group_id" value="{{ $group->id }}">
                    <button class="text-red-500 hover:text-red-700 text-sm font-bold p-2 hover:bg-red-50 rounded">삭제</button>
                </form>
            </li>
            @endforeach
            
            @if($menus->isEmpty())
                <li class="text-center py-10 text-neutral-400 bg-neutral-50 rounded border border-dashed">
                    등록된 메뉴가 없습니다.<br>오른쪽에서 메뉴를 추가해주세요.
                </li>
            @endif
        </ul>
        <div class="mt-6 text-center">
            <a href="{{ $base_path }}/admin/menus" class="text-neutral-500 text-sm hover:underline">⬅ 그룹 선택으로 돌아가기</a>
        </div>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-sm h-fit sticky top-6">
        <h3 class="font-bold text-lg mb-4 border-b pb-2">➕ 메뉴 추가</h3>
        
        <form action="{{ $base_path }}/admin/menus" method="POST" x-data="{ menuType: 'board' }">
            <input type="hidden" name="group_id" value="{{ $group->id }}">
            
            <div class="mb-5">
                <label class="block text-sm font-bold mb-2 text-neutral-700">메뉴 타입</label>
                <select name="type" x-model="menuType" class="w-full border border-neutral-300 rounded px-3 py-2 bg-white focus:ring-2 focus:ring-amber-400 outline-none">
                    <option value="board">📄 일반 게시판 연결</option>
                    <option value="load">🎨 로드비 게시판 연결</option>
                    <option value="character">🧙‍♂️ 캐릭터 게시판 연결</option>
                    <option value="page">📑 페이지 연결</option>
                    <option value="shop">🏪 상점 연결</option>
                    <option value="link">🔗 링크 연결</option>
                </select>
            </div>

            <div class="mb-5" x-show="menuType == 'shop'">
                <label class="block text-sm font-bold mb-2 text-neutral-700">연결할 상점</label>
                <select name="shop_id" class="w-full border border-neutral-300 rounded px-3 py-2 focus:ring-2 focus:ring-amber-400 outline-none" :required="menuType == 'shop'">
                    <optgroup label="🏪 상점" x-show="menuType === 'shop'">
                        @foreach($shops as $shop)
                            <option value="{{ $shop->id }}">{{ $shop->name }}</option>
                        @endforeach
                    </optgroup>
                </select>

            </div>

            <div class="mb-5" x-show="menuType != 'link' && menuType != 'shop'">
                <label class="block text-sm font-bold mb-2 text-neutral-700">연결할 게시판 원본</label>
                <select name="target_id" class="w-full border border-neutral-300 rounded px-3 py-2 focus:ring-2 focus:ring-amber-400 outline-none" :required="menuType != 'link' && menuType != 'shop'">
                    <option value="">게시판을 선택하세요</option>
                    <optgroup label="📄 일반 게시판" x-show="menuType === 'board'">
                        @foreach($allBoards as $board)
                            @if($board->type === 'document')
                                <option value="{{ $board->id }}">{{ $board->title }} (ID: {{ $board->id }})</option>
                            @endif
                        @endforeach
                    </optgroup>

                    <optgroup label="🎨 로드비 게시판" x-show="menuType === 'load'">
                        @foreach($allBoards as $board)
                            @if($board->type === 'load')
                                <option value="{{ $board->id }}">{{ $board->title }} (ID: {{ $board->id }})</option>
                            @endif
                        @endforeach
                    </optgroup>
            
                    <optgroup label="🧙‍♂️ 캐릭터 게시판" x-show="menuType === 'character'">
                        @foreach($allBoards as $board)
                            @if($board->type === 'character')
                                <option value="{{ $board->id }}">{{ $board->title }} (ID: {{ $board->id }})</option>
                            @endif
                        @endforeach
                    </optgroup>

                    <optgroup label="📑 페이지" x-show="menuType === 'page'">
                        @foreach($allBoards as $board)
                            @if($board->type === 'page')
                                <option value="{{ $board->id }}">{{ $board->title }} (ID: {{ $board->id }})</option>
                            @endif
                        @endforeach
                    </optgroup>
                </select>
                <p class="text-xs text-neutral-500 mt-1">※ '게시판 관리'에서 생성한 게시판 목록입니다.</p>
            </div>

            <div class="mb-5" x-show="menuType === 'link'">
                <label class="block text-sm font-bold mb-2 text-neutral-700">링크 주소</label>
                <input type="text" name="target_url" class="w-full border border-neutral-300 rounded px-3 py-2 focus:ring-2 focus:ring-amber-400 outline-none" placeholder="https://~~~" :required="menuType === 'link'">
            </div>

            <div class="mb-5">
                <label class="block text-sm font-bold mb-2 text-neutral-700">메뉴 이름</label>
                <input type="text" name="title" class="w-full border border-neutral-300 rounded px-3 py-2 focus:ring-2 focus:ring-amber-400 outline-none" placeholder="예: 자유게시판, 멤버소개" required>
            </div>

            <div class="mb-5" x-show="menuType != 'link' && menuType != 'shop'">
                <label class="block text-sm font-bold mb-2 text-neutral-700">접속 URL (Slug)</label>
                <div class="flex items-center">
                    <span class="bg-neutral-100 border border-r-0 border-neutral-300 rounded-l px-3 py-2 text-neutral-500 text-sm">/{{ $group->slug }}/</span>
                    <input type="text" name="slug" pattern="[a-z0-9\-_]+" class="w-full border border-neutral-300 rounded-r px-3 py-2 focus:ring-2 focus:ring-amber-400 outline-none" placeholder="free, member" :required="menuType != 'link' && menuType != 'shop'">
                </div>
            </div>

            <div class="mb-6" x-show="menuType != 'link' && menuType != 'shop'">
                <input type="checkbox" id="is_hidden" name="is_hidden" value="1">
                <label class="font-bold mb-2 text-neutral-700" for="is_hidden">메뉴 목록에서 숨기기</label>
                <span class="block text-xs text-neutral-500">체크하면 @custard_menu 로 불러오는 메뉴 목록에 표시되지 않습니다. (라우팅만 하고 싶을 때 체크)</span>
            </div>

            <button type="submit" class="w-full bg-amber-500 text-white py-3 rounded-lg font-bold hover:bg-amber-700 transition shadow-md">
                메뉴 추가하기
            </button>
        </form>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var el = document.getElementById('menu-list');
    
    if(el) {
        var sortable = Sortable.create(el, {
            animation: 150,
            handle: '.cursor-grab',
            ghostClass: 'bg-amber-50',
            
            onEnd: function (evt) {
                var order = [];
                el.querySelectorAll('li').forEach(function(item) {
                    order.push(item.getAttribute('data-id'));
                });

                fetch('{{ $base_path }}/admin/menus/reorder', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ order: order })
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                    } else {
                        alert('순서 저장 실패');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('통신 오류가 발생했습니다.');
                });
            }
        });
    }
});
</script>
@endsection