@extends('layouts.app')

@section('theme_content')

@push('styles')
<script src="https://cdn.tailwindcss.com"></script>
<link href="{{ $themeUrl }}/style.css?v={{ date("YmdHis") }}"  rel="stylesheet" type="text/css"></link>
@endpush

    {{-- [Basic 테마] 상단 네비게이션 --}}
    <header class="bg-white border-b border-gray-200">
        <div class="max-w-5xl mx-auto px-4 py-4 flex justify-between items-center">
            <a href="{{ $mainUrl }}" class="text-xl font-bold text-indigo-600">
                {{ $group->name ?? 'HOCHOBOARD' }}
            </a>
            @if(isset($group))
                @hc_menu($group->slug)
            @endif
            <nav class="space-x-4 text-sm font-medium text-gray-500">
                @if(isset($_SESSION['user_id']))
                    <span class="text-gray-800">{{ $_SESSION['nickname'] }}님</span>
                    <a href="{{ $base_path }}/logout" class="text-red-500">로그아웃</a>
                @else
                    <a href="{{ $base_path }}/login">로그인</a>
                @endif
            </nav>
        </div>
    </header>

    {{-- [Basic 테마] 메인 컨텐츠 영역 --}}
    <main class="max-w-5xl mx-auto px-4 py-8 min-h-[500px]">
        @yield('content')
    </main>
    
    {{-- [Basic 테마] 푸터 --}}
    <footer class="bg-gray-50 border-t py-8 text-center text-gray-400 text-sm">
        &copy; 2026 {{ $group->name ?? '' }}. Basic Theme by LH커뮤공사.
    </footer>

@endsection