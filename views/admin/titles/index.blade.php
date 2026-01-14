@extends('layouts.admin')
@section('title', '타이틀 관리')
@section('header', '타이틀 관리')

@section('content')
<div x-data="titleManager('{{ $base_path }}')">
    
    <div class="flex justify-end items-center mb-6">
        <button @click="openModal()" class="bg-amber-500 text-white px-4 py-2 rounded font-bold hover:bg-amber-700 shadow-sm transition-colors">
            + 타이틀 등록
        </button>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full text-sm text-left">
            <thead class="bg-neutral-50 text-neutral-700 font-bold border-b">
                <tr>
                    <th class="p-3 w-16 text-center">ID</th>
                    <th class="p-3 w-20 text-center">아이콘</th>
                    <th class="p-3">타이틀명 및 설명</th>
                    <th class="p-3 w-32 text-center">관리</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($titles as $title)
                <tr class="hover:bg-neutral-50 transition-colors">
                    <td class="p-3 text-center text-neutral-500">{{ $title->id }}</td>
                    <td class="p-3">
                        <div class="flex justify-center">
                            @if($title->icon_path)
                                <img src="{{ $base_path . $title->icon_path }}" class="w-10 h-10 rounded-sm border bg-neutral-100 object-cover" title="{{ $title->name }}">
                            @else
                                <div class="w-10 h-10 rounded border bg-neutral-100 flex items-center justify-center text-[10px] text-neutral-400">No Img</div>
                            @endif
                        </div>
                    </td>
                    <td class="p-3">
                        <div class="font-bold text-neutral-800 text-base mb-1">{{ $title->name }}</div>
                        <div class="text-xs text-neutral-500 break-keep">{{ $title->description }}</div>
                    </td>
                    <td class="p-3 text-center">
                        <button @click='editTitle(@json($title))' class="text-amber-600 font-bold hover:underline mr-3">수정</button>
                        <form action="{{ $base_path }}/admin/titles/delete" method="POST" class="inline-block" onsubmit="return confirm('이 타이틀을 정말 삭제하시겠습니까?\n(유저들이 장착 중인 타이틀도 함께 삭제될 수 있습니다)')">
                            <input type="hidden" name="id" value="{{ $title->id }}">
                            <button class="text-red-500 font-bold hover:underline">삭제</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="p-8 text-center text-neutral-400">등록된 타이틀이 없습니다.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div x-show="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40" x-cloak x-transition.opacity>
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 overflow-hidden" @click.outside="isModalOpen = false" x-show="isModalOpen" x-transition>
            
            <div class="px-6 py-4 border-b border-neutral-100 bg-neutral-50 flex justify-between items-center">
                <h3 class="text-lg font-bold text-neutral-800" x-text="form.id ? '🏆 타이틀 수정' : '🏆 새 타이틀 등록'"></h3>
                <button type="button" @click="isModalOpen = false" class="text-neutral-400 hover:text-neutral-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form action="{{ $base_path }}/admin/titles/store" method="POST" enctype="multipart/form-data" class="p-6">
                <input type="hidden" name="id" x-model="form.id">
                
                <div class="space-y-5">
                    <div>
                        <label class="block text-sm font-bold text-neutral-700 mb-1.5">타이틀명 <span class="text-red-500">*</span></label>
                        <input type="text" name="name" x-model="form.name" class="w-full border border-neutral-300 rounded-lg p-2.5 focus:ring-2 focus:ring-amber-200 focus:border-amber-400 outline-none transition-all" required>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-bold text-neutral-700 mb-1.5">상세 설명</label>
                        <textarea name="description" x-model="form.description" class="w-full border border-neutral-300 rounded-lg p-2.5 h-24 resize-none focus:ring-2 focus:ring-amber-200 focus:border-amber-400 outline-none transition-all"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-neutral-700 mb-1.5">아이콘 이미지 <span x-show="!form.id" class="text-red-500">*</span></label>
                        <div class="flex gap-4 items-center">
                            <div class="flex-1">
                                <input type="file" name="icon" class="w-full text-sm border border-neutral-300 rounded-lg p-2 bg-white cursor-pointer file:mr-4 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-bold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100" :required="!form.id">
                                <input type="hidden" name="existing_icon_path" x-model="form.icon_path">
                                <p class="text-[11px] text-neutral-400 mt-1.5">* 게시판 닉네임 옆에 표시될 아이콘을 설정합니다.</p>
                            </div>
                            
                            <template x-if="form.icon_path">
                                <div class="w-16 h-16 shrink-0 border border-neutral-200 rounded-lg bg-neutral-50 flex items-center justify-center p-1 shadow-inner">
                                    <img :src="basePath + form.icon_path" class="max-w-full max-h-full rounded-sm object-cover">
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex justify-end gap-2">
                    <button type="button" @click="isModalOpen = false" class="px-5 py-2.5 bg-neutral-100 rounded-lg hover:bg-neutral-200 font-bold text-neutral-600 transition-colors">취소</button>
                    <button type="submit" class="px-5 py-2.5 bg-amber-600 rounded-lg hover:bg-amber-700 font-bold text-white transition-colors">저장하기</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function titleManager(basePath = '') {
    return {
        basePath: basePath,
        isModalOpen: false,

        form: {
            id: null,
            name: '',
            description: '',
            icon_path: ''
        },

        openModal() {
            this.resetForm();
            this.isModalOpen = true;
        },

        editTitle(title) {
            this.resetForm();
            this.form.id = title.id;
            this.form.name = title.name;
            this.form.description = title.description;
            this.form.icon_path = title.icon_path;
            
            this.isModalOpen = true;
        },

        resetForm() {
            this.form = {
                id: null,
                name: '',
                description: '',
                icon_path: ''
            };
        }
    }
}
</script>
@endsection