<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Knowledge Base') }}
            </h2>
            @if(Auth::user()->hasRole('admin') || Auth::user()->can('manage_kb_articles'))
                <a href="{{ route('articles.index') }}" class="text-sm font-bold text-red-600 hover:text-red-800 transition">
                    Go to KB CMS &rarr;
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white p-8 rounded-lg shadow-sm border border-gray-200 mb-8 text-center">
                <h3 class="text-2xl font-bold text-gray-900 mb-2">How can we help you today?</h3>
                <p class="text-gray-500 mb-6">Search our knowledge base for quick answers and tutorials.</p>

                <form method="GET" action="{{ route('kb.index') }}" class="max-w-2xl mx-auto flex">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search for 'VPN', 'Password Reset', 'Printer'..." class="w-full rounded-l-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 text-lg py-3 px-4">
                    <button type="submit" class="bg-red-600 text-white px-8 py-3 rounded-r-md hover:bg-red-700 transition font-bold text-lg">Search</button>
                </form>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($articles as $article)
                    <a href="{{ route('kb.show', $article) }}" class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 hover:shadow-md hover:border-red-300 transition group flex flex-col h-full">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="p-2 bg-red-50 rounded text-red-600 group-hover:bg-red-600 group-hover:text-white transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                            </div>
                            <h4 class="font-bold text-lg text-gray-900 group-hover:text-red-600 transition line-clamp-1">{{ $article->title }}</h4>
                        </div>
                        <p class="text-sm text-gray-600 line-clamp-3 mb-4 flex-grow">
                            {{ strip_tags($article->content) }}
                        </p>
                        <div class="text-xs text-gray-400 font-medium pt-4 border-t border-gray-100 mt-auto">
                            Last updated {{ $article->updated_at->diffForHumans() }}
                        </div>
                    </a>
                @empty
                    <div class="col-span-full bg-white p-12 text-center rounded-lg shadow-sm border border-gray-200">
                        <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-1">No articles found</h3>
                        <p class="text-gray-500">Try adjusting your search terms.</p>
                    </div>
                @endforelse
            </div>

            @if($articles->hasPages())
                <div class="mt-8">
                    {{ $articles->links() }}
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
