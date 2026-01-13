
@extends($themeLayout)

@section('title', '회원가입')

@section('content')
<div class="flex flex-col items-center justify-center py-12">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md border border-gray-200">
        <h2 class="text-2xl font-bold mb-6 text-center text-gray-800">회원가입</h2>
        
        <form action="{{ $base_path }}/register" method="POST">
            <div class="mb-4">
                <label for="user_id" class="block text-gray-700 text-sm font-bold mb-2">아이디 *</label>
                <input type="text" name="user_id" id="user_id" class="w-full border rounded px-3 py-2" required placeholder="영문, 숫자">
            </div>

            <div class="mb-4">
                <label for="password" class="block text-gray-700 text-sm font-bold mb-2">비밀번호 *</label>
                <input type="password" name="password" id="password" class="w-full border rounded px-3 py-2" required>
            </div>

            <div class="mb-4">
                <label for="password_confirm" class="block text-gray-700 text-sm font-bold mb-2">비밀번호 확인 *</label>
                <input type="password" name="password_confirm" id="password_confirm" class="w-full border rounded px-3 py-2" required>
            </div>

            <div class="mb-4">
                <label for="nickname" class="block text-gray-700 text-sm font-bold mb-2">닉네임 *</label>
                <input type="text" name="nickname" id="nickname" class="w-full border rounded px-3 py-2" required>
            </div>

            <div class="mb-4">
                <label for="email" class="block text-gray-700 text-sm font-bold mb-2">이메일 *</label>
                <input type="email" name="email" id="email" class="w-full border rounded px-3 py-2" required>
            </div>

            <div class="mb-4">
                <label for="birthdate" class="block text-gray-700 text-sm font-bold mb-2">생년월일</label>
                <input type="date" name="birthdate" id="birthdate" class="w-full border rounded px-3 py-2">
            </div>

            <button type="submit" class="w-full bg-green-600 text-white font-bold py-3 rounded hover:bg-green-700 transition">
                가입하기
            </button>
        </form>

        <div class="mt-6 text-center text-sm">
            <a href="{{ $base_path }}/login" class="text-blue-600 hover:underline">이미 계정이 있으신가요?</a>
        </div>
    </div>
</div>
@endsection