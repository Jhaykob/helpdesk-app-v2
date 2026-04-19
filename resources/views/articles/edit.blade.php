<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Article') }}: <span class="text-gray-500 font-normal">{{ $article->title }}</span>
            </h2>
            <a href="{{ route('articles.index') }}" class="text-sm font-medium text-gray-500 hover:text-gray-700 transition">
                &larr; Back to Articles
            </a>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            @if ($errors->any())
                <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative shadow-sm">
                    <ul class="list-disc pl-5 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <form method="POST" action="{{ route('articles.update', $article) }}" class="p-8">
                    @csrf
                    @method('PATCH')

                    <div class="space-y-6">
                        <div>
                            <label for="title" class="block text-sm font-bold text-gray-700 mb-1">Article Title</label>
                            <input type="text" name="title" id="title" value="{{ old('title', $article->title) }}" required class="block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 text-lg font-medium py-3">
                        </div>

                        <div>
                            <label for="content" class="block text-sm font-bold text-gray-700 mb-1">Article Content</label>
                            <textarea name="content" id="content" rows="15" required class="block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm font-mono">{{ old('content', $article->content) }}</textarea>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-md border border-gray-200">
                            <div class="flex items-center">
                                <input type="checkbox" name="is_published" id="is_published" value="1" {{ old('is_published', $article->is_published) ? 'checked' : '' }} class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded cursor-pointer">
                                <label for="is_published" class="ml-2 block text-sm font-bold text-gray-900 cursor-pointer">Article is Published</label>
                            </div>
                            <p class="text-xs text-gray-500 ml-6 mt-1">If unchecked, this article will be reverted to a draft and hidden from normal users.</p>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end gap-3 border-t pt-6">
                        <a href="{{ route('articles.index') }}" class="bg-white py-2 px-6 border border-gray-300 rounded shadow-sm text-sm font-bold text-gray-700 hover:bg-gray-50 transition">
                            Cancel
                        </a>
                        <button type="submit" class="bg-gray-900 hover:bg-black text-white font-bold py-2 px-8 rounded shadow transition flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
