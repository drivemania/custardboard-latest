@extends('layouts.admin')

@section('title', '이모티콘 관리')
@section('header', '이모티콘 설정')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    
    <div class="md:col-span-1">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sticky top-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">새 이모티콘 등록</h3>
            
            <form action="{{ $base_path }}/admin/emoticons" method="POST" enctype="multipart/form-data">
                <div class="mb-4">
                    <label class="block text-sm font-bold text-gray-700 mb-1">예약어 (Code)</label>
                    <input type="text" name="code" placeholder="예: /애환" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required>
                    <p class="text-xs text-gray-400 mt-1">본문에 이 단어가 나오면 이미지로 변환됩니다.</p>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-bold text-gray-700 mb-1">이미지 파일</label>
                    <input type="file" name="image" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" required>
                </div>

                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition">
                    등록하기
                </button>
            </form>
        </div>
    </div>

    <div class="md:col-span-2">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-4 border-b bg-gray-50 flex justify-between items-center">
                <span class="font-bold text-gray-700">등록된 이모티콘 ({{ count($emoticons) }})</span>
            </div>

            @if($emoticons->isEmpty())
                <div class="p-8 text-center text-gray-400">
                    등록된 이모티콘이 없습니다.
                </div>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 p-4">
                    @foreach($emoticons as $emo)
                    <div class="relative group bg-gray-50 border rounded-lg p-3 flex flex-col items-center justify-center hover:shadow-md transition">
                        <div class="h-16 flex items-center justify-center mb-2">
                            <img src="{{ $base_path }}{{ $emo->image_path }}" alt="{{ $emo->code }}" class="max-h-full max-w-full object-contain">
                        </div>
                        
                        <code class="text-xs bg-gray-200 px-2 py-1 rounded text-gray-700 font-mono mb-2">{{ $emo->code }}</code>

                        <form action="{{ $base_path }}/admin/emoticons/delete" method="POST" onsubmit="return confirm('정말 삭제하시겠습니까?');" class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition">
                            <input type="hidden" name="id" value="{{ $emo->id }}">
                            <button type="submit" class="bg-red-500 text-white p-1 rounded-full hover:bg-red-600 shadow-sm" title="삭제">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </form>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection