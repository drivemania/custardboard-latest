@extends($themeLayout)

@section('title', $title)

@section('content')
<div class="max-w-5xl mx-auto px-4 py-8" x-data="{ 
    currentTab: '{{ $currentTab }}',
    pwModalOpen: false 
}">

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-8 flex flex-col md:flex-row items-center md:items-start gap-6">
        <div class="w-24 h-24 rounded-full bg-amber-50 text-amber-500 flex items-center justify-center text-4xl font-bold border-4 border-white shadow-md">
            {{ mb_substr($user->nickname, 0, 1) }}
        </div>

        <div class="flex-1 text-center md:text-left">
            <h1 class="text-2xl font-bold text-gray-900 mb-1">{{ $user->nickname }}</h1>
            <p class="text-gray-500 text-sm mb-4">아이디: {{ $user->user_id }} | 가입일: {{ date('Y.m.d', strtotime($user->created_at)) }}</p>
            
            <div class="flex flex-wrap justify-center md:justify-start gap-4">
                <div class="bg-yellow-50 px-4 py-2 rounded-lg border border-yellow-200">
                    <span class="text-xs text-yellow-800 block">보유 {{ $group->point_name }}</span>
                    <span class="text-md font-bold text-yellow-900">{{ number_format($user->user_point) }} {{ $group->point_name }}</span>
                </div>
                
                <button @click="pwModalOpen = true" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50 text-sm font-bold transition">
                    비밀번호 변경
                </button>
            </div>
        </div>
    </div>

    <div class="flex border-b border-gray-200 mb-6 overflow-x-auto">
        <button @click="currentTab = 'noti'" :class="currentTab === 'noti' ? 'border-amber-500 text-amber-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="px-6 py-3 border-b-2 font-bold text-sm whitespace-nowrap transition">
            알림 내역
        </button>
        <button @click="currentTab = 'history'" :class="currentTab === 'history' ? 'border-amber-500 text-amber-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="px-6 py-3 border-b-2 font-bold text-sm whitespace-nowrap transition">
            정산 내역
        </button>
        <button @click="currentTab = 'logs'" :class="currentTab === 'logs' ? 'border-amber-500 text-amber-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="px-6 py-3 border-b-2 font-bold text-sm whitespace-nowrap transition">
            로그 목록
        </button>
        <button @click="currentTab = 'char'" :class="currentTab === 'char' ? 'border-amber-500 text-amber-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="px-6 py-3 border-b-2 font-bold text-sm whitespace-nowrap transition">
            내 캐릭터
        </button>
        <button onclick="window.open('{{ $base_path }}/memo', 'memo', 'width=650,height=700');" class="border-transparent text-gray-500 hover:text-gray-700 px-6 py-3 border-b-2 font-bold text-sm whitespace-nowrap transition">
            쪽지함
        </button>
    </div>

    <div x-show="currentTab === 'noti'" style="display: none;">
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <ul class="divide-y divide-gray-100">
                @forelse($notifications as $noti)
                <li class="p-4 hover:bg-gray-50 transition flex items-start gap-3">
                    <div class="text-xl mt-1">📢</div>
                    <div class="flex-1">
                        <p class="text-sm text-gray-800">{{ $noti->message }}</p>
                        <span class="text-xs text-gray-400">{{ date('Y-m-d H:i', strtotime($noti->created_at)) }}</span>
                    </div>
                    @if($noti->url)
                        <a href="{{ $base_path }}{{ $noti->url }}" class="text-xs bg-gray-100 px-2 py-1 rounded text-gray-600 hover:bg-amber-100 hover:text-amber-600">확인</a>
                    @endif
                </li>
                @empty
                <li class="p-8 text-center text-gray-400 text-sm">알림 내역이 없습니다.</li>
                @endforelse
            </ul>
        </div>
        <div class="mt-4 flex justify-center gap-1">
            @if ($notifications->lastPage() > 1)
                @if ($notifications->onFirstPage())
                    <span class="px-3 py-1 text-gray-400 bg-gray-100 rounded text-xs"><</span>
                @else
                    <a href="{{ $notifications->previousPageUrl() }}" class="px-3 py-1 bg-white border text-gray-600 rounded hover:bg-gray-50 text-xs"><</a>
                @endif

                @for($i = 1; $i <= $notifications->lastPage(); $i++)
                    <a href="{{ $notifications->url($i) }}" 
                       class="px-3 py-1 border rounded text-xs {{ $i == $notifications->currentPage() ? 'bg-amber-600 text-white font-bold' : 'bg-white text-gray-600 hover:bg-gray-50' }}">
                        {{ $i }}
                    </a>
                @endfor

                @if ($notifications->hasMorePages())
                    <a href="{{ $notifications->nextPageUrl() }}" class="px-3 py-1 bg-white border text-gray-600 rounded hover:bg-gray-50 text-xs">></a>
                @else
                    <span class="px-3 py-1 text-gray-400 bg-gray-100 rounded text-xs">></span>
                @endif
            @endif
        </div>
    </div>

    <div x-show="currentTab === 'history'" style="display: none;">
        <div class="overflow-x-auto bg-white rounded-lg border border-gray-200">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-600 border-b">
                    <tr>
                        <th class="p-3 w-24">날짜</th>
                        <th class="p-3 w-20 text-center">구분</th>
                        <th class="p-3">내용</th>
                        <th class="p-3 w-24 text-center">비고</th>
                        <th class="p-3 w-28 text-right">{{ $group->point_name }} 변동</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($history as $log)
                    <tr>
                        <td class="p-3 text-gray-500 text-xs">{{ date('m-d H:i', strtotime($log->created_at)) }}</td>
                        
                        <td class="p-3 text-center">
                            @if($log->log_type == 'shop')
                                <span class="bg-amber-50 text-amber-600 text-[10px] px-2 py-1 rounded font-bold">구매</span>
                            @else
                                <span class="bg-green-50 text-green-600 text-[10px] px-2 py-1 rounded font-bold">정산</span>
                            @endif
                        </td>

                        <td class="p-3 font-bold text-gray-800">
                            {{ $log->title }}
                        </td>

                        <td class="p-3 text-center text-xs text-gray-500">
                            @if($log->log_type == 'shop')
                                {{ number_format($log->quantity_or_desc) }}개
                            @else
                                {{ $log->quantity_or_desc }}
                            @endif
                        </td>

                        <td class="p-3 text-right font-mono font-bold">
                            @if($log->point_change > 0)
                                <span class="text-green-600">+{{ number_format($log->point_change) }}</span>
                            @elseif($log->point_change < 0)
                                <span class="text-red-500">{{ number_format($log->point_change) }}</span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="p-8 text-center text-gray-400">내역이 없습니다.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-4 flex justify-center gap-1">
            @if ($history->lastPage() > 1)
                @if ($history->onFirstPage())
                    <span class="px-3 py-1 text-gray-400 bg-gray-100 rounded text-xs"><</span>
                @else
                    <a href="{{ $history->previousPageUrl() }}" class="px-3 py-1 bg-white border text-gray-600 rounded hover:bg-gray-50 text-xs"><</a>
                @endif
                @for($i = 1; $i <= $history->lastPage(); $i++)
                    <a href="{{ $history->url($i) }}" class="px-3 py-1 border rounded text-xs {{ $i == $history->currentPage() ? 'bg-amber-600 text-white font-bold' : 'bg-white text-gray-600 hover:bg-gray-50' }}">{{ $i }}</a>
                @endfor
                @if ($history->hasMorePages())
                    <a href="{{ $history->nextPageUrl() }}" class="px-3 py-1 bg-white border text-gray-600 rounded hover:bg-gray-50 text-xs">></a>
                @else
                    <span class="px-3 py-1 text-gray-400 bg-gray-100 rounded text-xs">></span>
                @endif
            @endif
        </div>
    </div>

    <div x-show="currentTab === 'logs'" style="display: none;">
        <div class="space-y-2">
            @forelse($documents as $doc)
            <div class="bg-white p-4 rounded-lg border border-gray-200 hover:border-amber-300 transition shadow-sm group">
                <div class="flex justify-between items-center mb-3">
                    <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded font-bold">#{{ $doc->doc_num }}</span>
                    <span class="text-xs text-gray-400">{{ date('Y.m.d', strtotime($doc->created_at)) }}</span>
                </div>

                <a href="{{ $base_path }}/au/{{ $doc->group_slug }}/{{ $doc->menu_slug }}/{{ $doc->doc_num }}" class="flex gap-4 items-start">
                    
                    @if(!empty($doc->content))
                        <div class="w-20 h-20 rounded-lg bg-gray-100 border border-gray-100 overflow-hidden shrink-0">
                            <img src="{{ $base_path }}{{ $doc->content }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        </div>
                    @else
                        <div class="w-20 h-20 rounded-lg bg-gray-50 border border-gray-100 flex items-center justify-center shrink-0 text-gray-300">
                            <span class="text-2xl">📄</span>
                        </div>
                    @endif

                    <div class="flex-1 min-w-0"> 

                    @if(isset($doc->comments) && !empty($doc->comments) && count($doc->comments) > 0)
                        <div class="space-y-1">
                            @foreach($doc->comments as $cmt)
                                <div class="flex items-center gap-2 text-xs text-gray-500 bg-gray-50 px-2 py-1 rounded hover:bg-amber-50 transition">
                                    <span class="text-amber-400 shrink-0">💬</span>
                                    
                                    <span class="truncate flex-1">{{ strip_tags($cmt->content) }}</span>
                                    
                                    @if(isset($cmt->nickname))
                                        <span class="text-gray-400 text-[10px] shrink-0 border-l pl-2">{{ $cmt->char_name }}[{{ $cmt->nickname }}]</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-xs text-gray-400 py-2">등록된 댓글이 없습니다.</p>
                    @endif

                    </div>
                </a>
            </div>
            @empty
            <div class="text-center py-10 text-gray-400">작성한 게시글이 없습니다.</div>
            @endforelse
        </div>
        <div class="mt-4 flex justify-center gap-1">
            @if ($documents->lastPage() > 1)
                @if ($documents->onFirstPage())
                    <span class="px-3 py-1 text-gray-400 bg-gray-100 rounded text-xs"><</span>
                @else
                    <a href="{{ $documents->previousPageUrl() }}" class="px-3 py-1 bg-white border text-gray-600 rounded hover:bg-gray-50 text-xs"><</a>
                @endif

                @for($i = 1; $i <= $documents->lastPage(); $i++)
                    <a href="{{ $documents->url($i) }}" 
                       class="px-3 py-1 border rounded text-xs {{ $i == $documents->currentPage() ? 'bg-amber-600 text-white font-bold' : 'bg-white text-gray-600 hover:bg-gray-50' }}">
                        {{ $i }}
                    </a>
                @endfor

                @if ($documents->hasMorePages())
                    <a href="{{ $documents->nextPageUrl() }}" class="px-3 py-1 bg-white border text-gray-600 rounded hover:bg-gray-50 text-xs">></a>
                @else
                    <span class="px-3 py-1 text-gray-400 bg-gray-100 rounded text-xs">></span>
                @endif
            @endif
        </div>
    </div>

    <div x-show="currentTab === 'char'" style="display: none;">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($characters as $char)
            <a href="{{ $base_path }}/au/{{ $char->group_slug }}/{{ $char->menu_slug }}/{{ $char->id }}">
                <div class="bg-white rounded-lg shadow border hover:shadow-md transition overflow-hidden relative" >

                    <div class="flex p-4">
                        @if($char->is_main)
                            <span class="absolute top-2 right-2 bg-yellow-400 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow">MAIN</span>
                        @endif
                        <div class="w-20 h-20 bg-gray-200 rounded-full flex-shrink-0 overflow-hidden mr-4 border-2 border-gray-100">
                            <img src="{{ $char->image_path }}" class="w-full h-full object-cover">
                        </div>
                        
                        <div class="flex-1 overflow-hidden">
                            <h3 class="font-bold text-lg text-gray-800 truncate">{{ $char->name }}</h3>
                            <p class="text-xs text-gray-500 mt-1">{{ $char->group_name }}</p>
                        </div>
                    </div>
                </div>
            </a>
            @empty
            <div class="col-span-full text-center py-10 text-gray-400">생성된 캐릭터가 없습니다.</div>
            @endforelse
        </div>
    </div>

    <div x-show="pwModalOpen" class="fixed inset-0 z-50 flex items-center justify-center px-4" style="display: none;" x-cloak>
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="pwModalOpen = false"></div>
        <div class="bg-white w-full max-w-sm rounded-lg shadow-xl relative z-10 p-6">
            <h3 class="text-lg font-bold mb-4">비밀번호 변경</h3>
            <form action="{{ $base_path }}/info/password" method="POST">
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">현재 비밀번호</label>
                        <input type="password" name="current_password" required class="w-full border rounded p-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">새 비밀번호</label>
                        <input type="password" name="new_password" required class="w-full border rounded p-2 text-sm">
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" @click="pwModalOpen = false" class="px-4 py-2 bg-gray-100 text-gray-600 rounded text-sm font-bold">취소</button>
                    <button type="submit" class="px-4 py-2 bg-amber-600 text-white rounded text-sm font-bold hover:bg-amber-700">변경하기</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection