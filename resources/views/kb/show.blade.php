<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Article Reader') }}
            </h2>
            <a href="{{ route('kb.index') }}" class="text-sm font-medium text-gray-500 hover:text-gray-700 transition">
                &larr; Back to Knowledge Base
            </a>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-t-4 border-red-600">
                <div class="p-8 md:p-12">

                    <div class="mb-8 border-b border-gray-200 pb-8">
                        @if(!$article->is_published)
                            <span class="inline-block bg-yellow-100 text-yellow-800 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wide mb-4">
                                Draft Preview Mode
                            </span>
                        @endif
                        <h1 class="text-3xl font-extrabold text-gray-900 mb-4 leading-tight">{{ $article->title }}</h1>
                        <div class="flex items-center text-sm text-gray-500">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            Last updated on {{ $article->updated_at->format('F j, Y \a\t g:i A') }}
                        </div>
                    </div>

                    <div class="prose max-w-none prose-red text-gray-800">
                        {!! nl2br(e($article->content)) !!}
                    </div>

                    <div class="mt-12 pt-8 border-t border-gray-200 text-center">
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Did this answer your question?</h3>
                        <p class="text-gray-600 mb-6">If you still need help, our IT Support team is ready to assist you.</p>
                        <a href="{{ route('tickets.create') }}" class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-red-600 hover:bg-red-700 shadow-sm transition">
                            Submit a Support Ticket
                        </a>
                    </div>

                </div>
            </div>

        </div>
    </div>
</x-app-layout>
