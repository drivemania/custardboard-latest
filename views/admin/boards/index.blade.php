@extends('layouts.admin')
@section('title', '게시판 관리')
@section('header', '게시판/페이지 관리')

@section('content')

<div x-data="{ showModal: false }">
    <div class="flex justify-end mb-4">
        <button @click="showModal = true" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 font-bold flex items-center">
            <span class="mr-2">➕</span> 새 게시판 생성
        </button>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-100 border-b text-gray-600 text-sm uppercase">
                    <th class="px-6 py-3">게시판 이름</th>
                    <th class="px-6 py-3">스킨</th>
                    <th class="px-6 py-3 text-right">관리</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($boards as $b)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 font-bold text-gray-800">{{ $b->title }}</td>
                    <td class="px-6 py-4 text-xs text-gray-500">{{ $b->board_skin }}</td>
                    <td class="px-6 py-4 text-right space-x-2">
                        <a href="{{ $base_path }}/admin/boards/{{ $b->id }}" class="inline-block text-blue-600 bg-blue-50 py-1 px-3 rounded text-sm">⚙️ 설정</a>
                        <button type="button" onclick="copyBoard({{ $b->id }})" class="inline-block text-green-600 bg-green-50 rounded py-1 px-3 text-sm">복사</button>
                        <form action="{{ $base_path }}/admin/boards/delete" method="POST" class="inline-block" onsubmit="return confirm('삭제하시겠습니까?');">
                            <input type="hidden" name="id" value="{{ $b->id }}">
                            <button class="text-red-500 text-sm py-1 px-3">삭제</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md shadow-xl" @click.away="showModal = false">
            <h3 class="text-lg font-bold mb-4">새 게시판 만들기</h3>
            <form action="{{ $base_path }}/admin/boards" method="POST">
                
            <div class="mb-4">
                <label class="block text-sm font-bold text-gray-700 mb-2">게시판 종류</label>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    
                    <label class="cursor-pointer group">
                        <input type="radio" name="type" value="document" class="peer sr-only" checked>
                        <div class="h-full flex flex-col items-center justify-center py-4 px-2 rounded-xl border border-gray-200 bg-white text-gray-500 transition-all duration-200 hover:bg-gray-50 hover:border-gray-300
                                    peer-checked:border-blue-500 peer-checked:text-blue-600 peer-checked:bg-blue-50 peer-checked:ring-1 peer-checked:ring-blue-500 peer-checked:shadow-sm">
                            <svg class="w-6 h-6 mb-2 text-gray-400 group-hover:text-gray-600 peer-checked:text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <span class="text-sm font-bold">일반<BR>게시판</span>
                        </div>
                    </label>

                    <label class="cursor-pointer group">
                        <input type="radio" name="type" value="character" class="peer sr-only">
                        <div class="h-full flex flex-col items-center justify-center py-4 px-2 rounded-xl border border-gray-200 bg-white text-gray-500 transition-all duration-200 hover:bg-gray-50 hover:border-gray-300
                                    peer-checked:border-green-500 peer-checked:text-green-600 peer-checked:bg-green-50 peer-checked:ring-1 peer-checked:ring-green-500 peer-checked:shadow-sm">
                            <svg class="w-6 h-6 mb-2 text-gray-400 group-hover:text-gray-600 peer-checked:text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            <span class="text-sm font-bold">캐릭터<BR>게시판</span>
                        </div>
                    </label>

                    <label class="cursor-pointer group">
                        <input type="radio" name="type" value="load" class="peer sr-only">
                        <div class="h-full flex flex-col items-center justify-center py-4 px-2 rounded-xl border border-gray-200 bg-white text-gray-500 transition-all duration-200 hover:bg-gray-50 hover:border-gray-300
                                    peer-checked:border-amber-500 peer-checked:text-amber-700 peer-checked:bg-amber-50 peer-checked:ring-1 peer-checked:ring-amber-500 peer-checked:shadow-sm">
                            <svg class="w-6 h-6 mb-2 text-gray-400 group-hover:text-gray-600 peer-checked:text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                            <span class="text-sm font-bold">로드비<BR>게시판</span>
                        </div>
                    </label>

                    <label class="cursor-pointer group">
                        <input type="radio" name="type" value="page" class="peer sr-only">
                        <div class="h-full flex flex-col items-center justify-center py-4 px-2 rounded-xl border border-gray-200 bg-white text-gray-500 transition-all duration-200 hover:bg-gray-50 hover:border-gray-300
                                    peer-checked:border-purple-500 peer-checked:text-purple-600 peer-checked:bg-purple-50 peer-checked:ring-1 peer-checked:ring-purple-500 peer-checked:shadow-sm">
                            <svg class="w-6 h-6 mb-2 text-gray-400 group-hover:text-gray-600 peer-checked:text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                            <span class="text-sm font-bold">페이지</span>
                        </div>
                    </label>

                </div>
                <p class="text-xs text-gray-400 mt-2 ml-1">※ 생성 후에는 변경할 수 없습니다.</p>
            </div>

                <div class="mb-4">
                    <label class="block text-sm font-bold mb-2">게시판 이름</label>
                    <input type="text" name="title" class="w-full border rounded px-3 py-2" placeholder="예: 자유게시판" required>
                </div>

                <div class="flex justify-end space-x-2">
                    <button type="button" @click="showModal = false" class="px-4 py-2 border rounded text-gray-600">취소</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">생성</button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="copyForm" action="{{ $base_path }}/admin/boards/copy" method="POST" style="display:none;">
    <input type="hidden" name="board_id" id="copy_board_id">
</form>

@push('scripts')
<script>
    function copyBoard(id) {
        document.getElementById('copy_board_id').value = id;
        document.getElementById('copyForm').submit();
    }
</script>
@endpush
@endsection