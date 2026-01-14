@extends($themeLayout)

@section('content')


@php

if(!empty($document->content)){
    $document->content = str_replace('<ol>', '<ol class="list-decimal">', $document->content);
    $document->content = str_replace('<ul>', '<ul class="list-disc">', $document->content);
}

@endphp

<div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow-sm border border-neutral-200">
    
    <div class="border-b pb-4 mb-6">
        <span class="text-amber-600 font-bold text-sm">{{ $board->title }}</span>
        <h1 class="text-2xl font-bold text-neutral-800 mt-1">{{ $document->title }}</h1>
        <div class="flex items-center text-sm text-neutral-500 mt-2 space-x-4">
            <div class="flex items-center gap-1.5">
                @if(!empty($document->title_icon))
                    <img src="{{ $base_path }}{{ $document->title_icon }}" title="장착 중인 타이틀">
                @endif
                <span class="font-bold text-neutral-800">{{ $document->nickname }}</span>
            </div>
            <span>{{ date('Y-m-d H:i', strtotime($document->created_at)) }}</span>
            <span>조회 {{ number_format($document->hit) }}</span>
        </div>
    </div>
    @php
        $customFields = $board->custom_fields ? json_decode($board->custom_fields, true) : [];
        $savedData = $document->custom_data ? json_decode($document->custom_data, true) : [];
    @endphp

    @if(!empty($customFields))
    <div class="bg-neutral-50 p-4 rounded border mb-6 text-sm">
        <table class="w-full">
            @foreach($customFields as $field)
                @php $val = $savedData[$field['name']] ?? '-'; @endphp
                <tr class="border-b border-neutral-200 last:border-0">
                    <th class="w-32 py-2 text-left text-neutral-500 font-normal pl-2">{{ $field['name'] }}</th>
                    <td class="py-2 text-neutral-800 font-bold">{{ $val }}</td>
                </tr>
            @endforeach
        </table>
    </div>
    @endif
    <div class="flex space-x-4">
        {!! $document->plugin ?? '' !!}
    </div>
    <div class="min-h-[200px] mb-10 prose max-w-none">
        @if($board->use_editor)
            {!! $document->content !!}
        @else
            {!! nl2br($document->content) !!}
        @endif
    </div>

    <div class="flex justify-end space-x-2 border-b pb-6 mb-6">
        @if( (isset($_SESSION['user_idx']) && $_SESSION['user_idx'] == $document->user_id) || ($_SESSION['level'] ?? 0) >= 10 )
            <a href="{{ $currentUrl }}/edit" class="px-4 py-2 bg-neutral-100 text-neutral-700 rounded hover:bg-neutral-200 text-sm font-bold">수정</a>
            <form action="{{ $currentUrl }}/delete" method="POST" onsubmit="return confirm('정말 삭제하시겠습니까?');">
                <button class="px-4 py-2 bg-neutral-100 text-red-600 rounded hover:bg-neutral-200 text-sm font-bold">삭제</button>
            </form>
        @endif
        <a href="{{ $listUrl }}" class="px-4 py-2 bg-amber-600 text-white rounded hover:bg-amber-700 text-sm font-bold">목록</a>
    </div>

    <div class="bg-neutral-50 p-4 rounded-lg">
        <h3 class="font-bold text-neutral-700 mb-4">댓글 ({{ $document->comment_count }})</h3>

        <ul class="space-y-4 mb-6">
            @foreach($comments as $cmt)
            @php
                $isSecret = $cmt->is_secret ?? 0;
                $canViewSecret = (($_SESSION['level'] ?? 0) >= 10) || (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $cmt->user_id);
                $isReply = ($cmt->parent_id ?? 0) > 0;
            @endphp

            <li x-data="{ editMode: false, replyMode: false }" 
                class="border-b border-neutral-200 pb-3 last:border-0 {{ $isReply ? 'pl-8 sm:pl-12 relative' : '' }}">
                
                @if($isReply)
                    <div class="absolute left-2 sm:left-4 top-2 text-neutral-300">
                        <svg class="w-5 h-5 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                    </div>
                @endif

                <div class="flex justify-between items-center mb-1">
                    <div class="flex items-center gap-2">
                        @if(!empty($cmt->title_icon))
                            <img src="{{ $base_path }}{{ $cmt->title_icon }}">
                        @endif
                        <span id="comment_{{ $cmt->id }}" class="scroll-mt-24 target:bg-yellow-50 font-bold text-sm text-neutral-800">{{ $cmt->nickname }}</span>
                        @if($isSecret)
                            <span class="text-sm">🔒</span>
                        @endif
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="text-xs text-neutral-400">{{ date('m.d H:i', strtotime($cmt->created_at)) }}</span>
                        
                        @if(($_SESSION['level'] ?? 0) >= $board->comment_level)
                            <button type="button" 
                                    x-show="!editMode" 
                                    @click="replyMode = !replyMode" 
                                    class="text-xs text-neutral-500 hover:text-amber-600">
                                답글
                            </button>
                        @endif
                        
                        @if( (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $cmt->user_id) || ($_SESSION['level'] ?? 0) >= 10 )
                            <button type="button" x-show="!editMode" @click="editMode = true" class="text-xs text-neutral-400 hover:text-amber-600">수정</button>
                            
                            <form x-show="!editMode" action="{{ $base_path }}/comment/delete" method="POST" onsubmit="return confirm('댓글을 삭제할까요?');" class="flex items-center m-0 p-0">
                                <input type="hidden" name="comment_id" value="{{ $cmt->id }}">
                                <input type="hidden" name="doc_id" value="{{ $document->doc_num }}">
                                <button type="submit" class="text-xs text-red-400 hover:text-red-600">삭제</button>
                            </form>
                        @endif
                    </div>
                </div>

                @if($isSecret && !$canViewSecret)
                    <div x-show="!editMode" class="text-sm text-neutral-400 italic py-2">비밀 댓글입니다.</div>
                @else
                    {!! $cmt->plugin ?? '' !!}
                    <div x-show="!editMode" class="text-sm text-neutral-700 whitespace-pre-wrap">{!! Helper::auto_link($cmt->content) !!}</div>
                @endif

                @if( (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $cmt->user_id) || ($_SESSION['level'] ?? 0) >= 10 )
                <div x-show="editMode" x-cloak class="mt-2">
                    <form action="{{ $base_path }}/comment/update" method="POST">
                        <input type="hidden" name="comment_id" value="{{ $cmt->id }}">
                        <textarea name="content" class="w-full border rounded p-2 text-sm focus:ring-2 focus:ring-amber-200 resize-none h-20 mb-2" required>{{ $cmt->content }}</textarea>
                        <div class="flex items-center justify-between">
                            <label class="inline-flex items-center space-x-1 cursor-pointer">
                                <input type="checkbox" name="is_secret" value="1" class="w-4 h-4 text-amber-500 rounded border-neutral-300 focus:ring-amber-500" {{ $isSecret ? 'checked' : '' }}>
                                <span class="text-sm font-bold text-neutral-600">🔒 비밀댓글</span>
                            </label>
                            <div class="flex space-x-2">
                                <button type="submit" class="bg-amber-600 text-white px-3 py-1 rounded text-xs font-bold hover:bg-amber-700">저장</button>
                                <button type="button" @click="editMode = false" class="bg-neutral-200 text-neutral-600 px-3 py-1 rounded text-xs font-bold hover:bg-neutral-300">취소</button>
                            </div>
                        </div>
                    </form>
                </div>
                @endif

                @if(($_SESSION['level'] ?? 0) >= $board->comment_level)
                <div x-show="replyMode" x-cloak class="mt-3 bg-white p-3 border border-neutral-200 rounded-lg shadow-sm">
                    <form action="{{ $currentUrl }}/comment" method="POST" class="flex flex-col space-y-2">
                        <input type="hidden" name="parent_id" value="{{ $isReply ? $cmt->parent_id : $cmt->id }}">
                        
                        <div class="flex items-start space-x-2">
                            <span class="text-neutral-400 mt-2">↪</span>
                            <textarea name="content" class="w-full border rounded p-2 text-sm focus:ring-2 focus:ring-amber-200 resize-none h-16" placeholder="{{ $cmt->nickname }}님에게 답글 작성..." required></textarea>
                            <button class="bg-neutral-700 text-white px-3 py-2 rounded h-16 font-bold hover:bg-neutral-800 text-sm whitespace-nowrap">등록</button>
                        </div>
                        <div class="flex justify-between items-center pl-6">
                            <label class="inline-flex items-center space-x-1.5 cursor-pointer">
                                <input type="checkbox" name="is_secret" value="1" 
                                    class="w-4 h-4 text-amber-500 rounded border-neutral-300 focus:ring-amber-500" 
                                    {{ $isSecret ? 'checked' : '' }}>
                                <span class="text-xs font-bold text-neutral-600">🔒 비밀답글</span>
                            </label>
                            <button type="button" @click="replyMode = false" class="text-xs text-neutral-400 hover:text-neutral-600 font-bold">취소</button>
                        </div>
                    </form>
                </div>
                @endif

            </li>
            @endforeach
            
            @if($comments->isEmpty())
                <li class="text-center text-neutral-400 text-sm py-4">첫 번째 댓글을 남겨보세요!</li>
            @endif
        </ul>

        @if(($_SESSION['level'] ?? 0) >= $board->comment_level)
        <form action="{{ $currentUrl }}/comment" method="POST" class="flex flex-col space-y-2">
            <div class="flex items-start space-x-2">
                <textarea name="content" class="w-full border rounded p-2 text-sm focus:ring-2 focus:ring-amber-200 resize-none h-20" placeholder="댓글을 입력하세요..." required></textarea>
                <button class="bg-amber-600 text-white px-4 py-2 rounded h-20 font-bold hover:bg-amber-700 text-sm whitespace-nowrap">등록</button>
            </div>
            <div class="px-1">
                <label class="inline-flex items-center space-x-1.5 cursor-pointer">
                    <input type="checkbox" name="is_secret" id="is_secret" value="1" class="w-4 h-4 text-amber-500 rounded border-neutral-300 focus:ring-amber-500">
                    <span class="text-sm font-bold text-neutral-600">🔒 비밀댓글</span>
                </label>
            </div>
        </form>
        @else
            <div class="text-center text-neutral-400 text-sm py-2 border rounded bg-white">
                댓글 쓰기 권한이 없습니다.
            </div>
        @endif
    </div>

</div>
@endsection