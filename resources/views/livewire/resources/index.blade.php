<?php

use App\Models\Asset;
use function Livewire\Volt\{computed, state};

state([
    'search' => '',
    'type' => '',
]);

$assets = computed(function () {
    return Asset::with('media', 'stop')
        ->when($this->search, fn ($q) => $q->where(function ($q) {
            $q->where('title', 'like', '%' . $this->search . '%')
              ->orWhere('description', 'like', '%' . $this->search . '%');
        }))
        ->when($this->type, fn ($q) => $q->where('type', $this->type))
        ->orderBy('created_at', 'desc')
        ->get();
});

?>

<div class="min-h-screen bg-base-100">

    {{-- Navigation --}}
    <x-mary-nav sticky class="bg-base-200 border-b border-base-300">
        <x-slot:brand>
            <span class="font-bold text-lg">Jesse Kregel Trail</span>
        </x-slot:brand>
        <x-slot:actions>
            <x-mary-button label="Map" link="{{ route('home') }}" class="btn-ghost btn-sm" />
            @auth
                <x-mary-button label="Admin" link="/admin" class="btn-ghost btn-sm" />
            @endauth
            @guest
                <x-mary-button label="Sign In" link="/admin/login" class="btn-primary btn-sm" />
            @endguest
        </x-slot:actions>
    </x-mary-nav>

    {{-- Page header --}}
    <section class="max-w-5xl mx-auto px-6 pt-20 pb-10">
        <h1 class="text-5xl font-bold tracking-tight mb-4">Resources</h1>
        <p class="text-xl text-base-content/60">Browse audio, video, and photo resources from the trail.</p>
    </section>

    {{-- Filters --}}
    <section class="max-w-5xl mx-auto px-6 pb-10 flex flex-wrap items-center gap-4">
        <x-mary-input placeholder="Search..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass" class="w-64" />
        <x-mary-select
            :options="[
                ['id' => '', 'name' => 'All types'],
                ['id' => 'audio', 'name' => 'Audio'],
                ['id' => 'video', 'name' => 'Video'],
                ['id' => 'photo', 'name' => 'Photo'],
            ]"
            wire:model.live="type"
            placeholder="All types"
            class="w-48"
        />
        <span class="text-sm text-base-content/40">{{ $this->assets->count() }} result{{ $this->assets->count() === 1 ? '' : 's' }}</span>
    </section>

    {{-- Content --}}
    <section class="max-w-5xl mx-auto px-6 pb-20" x-data="{ lightbox: null }">

        @if ($this->assets->isEmpty())
            <p class="text-base-content/40 text-center py-16">No resources found.</p>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($this->assets as $asset)
                    @php $file = $asset->getFirstMedia('file'); @endphp
                    <div class="card bg-base-200 shadow-sm">
                        {{-- Photo --}}
                        @if ($asset->type === 'photo' && $file)
                            <figure class="cursor-zoom-in" @click="lightbox = { type: 'photo', url: '{{ $file->getUrl() }}', mime: '{{ $file->mime_type }}', title: '{{ addslashes($asset->title) }}' }">
                                <img src="{{ $file->getUrl() }}" alt="{{ $asset->title }}" class="w-full h-48 object-cover hover:opacity-90 transition-opacity">
                            </figure>
                        @endif

                        {{-- Video thumbnail --}}
                        @if ($asset->type === 'video' && $file)
                            <figure class="relative cursor-pointer bg-black" @click="lightbox = { type: 'video', url: '{{ $file->getUrl() }}', mime: '{{ $file->mime_type }}', title: '{{ addslashes($asset->title) }}' }">
                                <video class="w-full h-48 object-cover opacity-70" preload="metadata">
                                    <source src="{{ $file->getUrl() }}#t=0.5" type="{{ $file->mime_type }}">
                                </video>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <div class="w-14 h-14 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center hover:bg-white/30 transition-colors">
                                        <svg class="w-6 h-6 text-white ml-1" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                    </div>
                                </div>
                            </figure>
                        @endif

                        <div class="card-body p-4">
                            <div class="flex items-start justify-between gap-2 mb-1">
                                <h2 class="card-title text-base">{{ $asset->title }}</h2>
                                <span class="badge badge-sm {{ match($asset->type) { 'audio' => 'badge-info', 'video' => 'badge-warning', 'photo' => 'badge-success', default => 'badge-ghost' } }}">
                                    {{ ucfirst($asset->type) }}
                                </span>
                            </div>

                            @if ($asset->stop)
                                <p class="text-xs text-base-content/40 mb-2">{{ $asset->stop->title }}</p>
                            @endif

                            @if ($asset->description)
                                <p class="text-sm text-base-content/60 mb-3">{{ $asset->description }}</p>
                            @endif

                            {{-- Audio player --}}
                            @if ($asset->type === 'audio' && $file)
                                <audio controls class="w-full mt-2" style="border-radius: 0.375rem;">
                                    <source src="{{ $file->getUrl() }}" type="{{ $file->mime_type }}">
                                </audio>
                            @endif

                            {{-- Video open button --}}
                            @if ($asset->type === 'video' && $file)
                                <button class="btn btn-sm btn-outline mt-2 w-full"
                                        @click="lightbox = { type: 'video', url: '{{ $file->getUrl() }}', mime: '{{ $file->mime_type }}', title: '{{ addslashes($asset->title) }}' }">
                                    ▶ Watch
                                </button>
                            @endif

                            @if ($asset->stop)
                                <a href="{{ route('home') }}?stop={{ $asset->stop_id }}"
                                   wire:navigate
                                   class="btn btn-sm btn-ghost mt-2 w-full text-base-content/50">
                                    Show on map →
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Lightbox --}}
        <template x-teleport="body">
            <div x-show="lightbox"
                 x-transition.opacity
                 @click.self="lightbox = null"
                 @keydown.escape.window="lightbox = null"
                 class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4"
                 style="display: none;">
                <button @click="lightbox = null"
                        class="fixed top-4 right-4 z-[10000] flex items-center justify-center w-10 h-10 rounded-full text-white text-lg cursor-pointer backdrop-blur-sm"
                        style="background: rgba(0,0,0,0.6); border: 1px solid rgba(255,255,255,0.3);">
                    ✕
                </button>
                <div class="relative max-w-5xl w-full">

                    <template x-if="lightbox?.type === 'photo'">
                        <img :src="lightbox.url" :alt="lightbox.title" class="w-full max-h-[85vh] object-contain rounded-lg shadow-2xl">
                    </template>

                    <template x-if="lightbox?.type === 'video'">
                        <video controls autoplay class="w-full max-h-[85vh] rounded-lg shadow-2xl">
                            <source :src="lightbox.url" :type="lightbox.mime">
                        </video>
                    </template>

                    <p class="text-center text-white/60 text-sm mt-3" x-text="lightbox?.title"></p>
                </div>
            </div>
        </template>

    </section>

    {{-- Footer --}}
    <footer class="border-t border-base-300 bg-base-200">
        <div class="max-w-5xl mx-auto px-6 py-6 flex items-center justify-between text-sm text-base-content/50">
            <span>Jesse Kregel Trail</span>
            @guest
                <a href="/admin/login" class="hover:text-base-content transition-colors">Contributor login →</a>
            @endguest
        </div>
    </footer>

</div>
