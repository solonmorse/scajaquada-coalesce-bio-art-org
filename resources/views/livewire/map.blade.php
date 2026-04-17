<?php

use App\Models\Stop;
use Illuminate\Support\Js;
use function Livewire\Volt\{computed, layout, state};

layout('layouts.map');

$stopsJson = computed(function () {
    return Stop::published()
        ->orderBy('trail_order')
        ->with('media', 'assets.media')
        ->get()
        ->map(function (Stop $stop) {
            return [
                'id' => $stop->id,
                'title' => $stop->title,
                'description' => $stop->description,
                'type' => $stop->type?->value,
                'type_label' => $stop->type?->label(),
                'type_color' => $stop->type?->color(),
                'latitude' => (float)$stop->latitude,
                'longitude' => (float)$stop->longitude,
                'trail_order' => $stop->trail_order,
                'icon_url' => $stop->getFirstMediaUrl('icon') ?: null,
                'photo' => $stop->getMedia('photo')->map(fn($m) => [
                    'url' => $m->getUrl(),
                    'mime_type' => $m->mime_type,
                    'name' => $m->name,
                ])->toBase()->merge(
                    $stop->assets->where('type', 'photo')->map(fn($a) => [
                        'url' => $a->getFirstMediaUrl('file'),
                        'mime_type' => $a->getFirstMedia('file')?->mime_type,
                        'name' => $a->title,
                    ])
                )->values()->toArray(),
                'audio' => $stop->getMedia('audio')->map(fn($m) => [
                    'url' => $m->getUrl(),
                    'mime_type' => $m->mime_type,
                    'name' => $m->name,
                ])->toBase()->merge(
                    $stop->assets->where('type', 'audio')->map(fn($a) => [
                        'url' => $a->getFirstMediaUrl('file'),
                        'mime_type' => $a->getFirstMedia('file')?->mime_type,
                        'name' => $a->title,
                    ])
                )->values()->toArray(),
                'video' => $stop->getMedia('video')->map(fn($m) => [
                    'url' => $m->getUrl(),
                    'mime_type' => $m->mime_type,
                    'name' => $m->name,
                ])->toBase()->merge(
                    $stop->assets->where('type', 'video')->map(fn($a) => [
                        'url' => $a->getFirstMediaUrl('file'),
                        'mime_type' => $a->getFirstMedia('file')?->mime_type,
                        'name' => $a->title,
                    ])
                )->values()->toArray(),
            ];
        })
        ->values()
        ->toArray();
});

?>

<div id="map-root"
     x-data="mapApp({{ json_encode($this->stopsJson) }}, {{ request()->query('stop', 'null') }})"
     x-init="init()">

    {{-- Mobile backdrop --}}
    <div id="sidebar-backdrop"
         x-show="sidebarOpen"
         x-transition.opacity
         @click="sidebarOpen = false"
         style="display:none;"></div>

    {{-- Sidebar --}}
    <div id="sidebar" :class="sidebarOpen ? 'sidebar-open' : ''">

        {{-- Header --}}
        <div
            style="padding: 1rem 1.25rem; border-bottom: 2px solid oklch(var(--b3)); background: oklch(var(--b2)); flex-shrink: 0; display: flex; align-items: flex-start; justify-content: space-between;">
            <div>
                <h1 style="margin: 0; font-size: 1rem; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; color: oklch(var(--bc));">
                    Trail Stops</h1>
                <p style="margin: 0.2rem 0 0; font-size: 0.75rem; color: oklch(var(--bc) / 0.45);">Select a stop to explore media</p>
            </div>
            <button @click="sidebarOpen = false"
                    id="sidebar-close"
                    style="background: none; border: none; cursor: pointer; padding: 0.25rem; color: oklch(var(--bc) / 0.5); line-height: 1;">✕</button>
        </div>

        {{-- Stop list --}}
        <div style="flex: 1; overflow-y: auto; padding: 0.75rem 0.5rem;">

            @forelse ($this->stopsJson as $stop)
                <div id="stop-wrapper-{{ $stop['id'] }}"
                     x-on:change.capture="$event.target.type === 'checkbox' && $event.target.checked && panToStop({{ $stop['latitude'] }}, {{ $stop['longitude'] }}, {{ $stop['id'] }})">
                    <x-mary-collapse separator>
                        <x-slot:heading>
                            <div class="flex items-center gap-3">
                                <span
                                    class="flex-shrink-0 w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold bg-base-300 text-base-content/60">
                                    {{ $stop['trail_order'] ?? $loop->iteration }}
                                </span>
                                <span class="flex-1 text-sm font-semibold">{{ $stop['title'] }}</span>
                            </div>
                        </x-slot:heading>
                        <x-slot:content>

                            @if ($stop['description'])
                                <p class="text-sm text-base-content/65 leading-relaxed mt-1">{{ $stop['description'] }}</p>
                            @endif

                            {{-- Photos --}}
                            @if (count($stop['photo']) > 0)
                                <p class="media-section-title">Photos</p>
                                <div class="photo-grid">
                                    @foreach ($stop['photo'] as $photo)
                                        <img src="{{ $photo['url'] }}" alt="{{ $photo['name'] }}" loading="lazy"
                                             style="cursor: zoom-in;"
                                             @click="lightbox = {{ Js::from(['type' => 'photo', 'url' => $photo['url'], 'mime' => $photo['mime_type'], 'title' => $photo['name']]) }}">
                                    @endforeach
                                </div>
                            @endif

                            {{-- Audio --}}
                            @if (count($stop['audio']) > 0)
                                <p class="media-section-title">Audio</p>
                                @foreach ($stop['audio'] as $track)
                                    <div style="margin-bottom: 0.5rem;">
                                        <p style="font-size: 0.7rem; color: oklch(var(--bc) / 0.45); margin: 0 0 0.2rem;">{{ $track['name'] }}</p>
                                        <audio controls src="{{ $track['url'] }}"
                                               style="width: 100%; border-radius: 0.375rem;"></audio>
                                    </div>
                                @endforeach
                            @endif

                            {{-- Video --}}
                            @if (count($stop['video']) > 0)
                                <p class="media-section-title">Video</p>
                                @foreach ($stop['video'] as $clip)
                                    <div
                                        style="position: relative; cursor: pointer; background: #000; border-radius: 0.375rem; overflow: hidden; margin-bottom: 0.5rem;"
                                        @click="lightbox = {{ Js::from(['type' => 'video', 'url' => $clip['url'], 'mime' => $clip['mime_type'], 'title' => $clip['name']]) }}">
                                        <video src="{{ $clip['url'] }}#t=0.5" preload="metadata"
                                               style="width: 100%; opacity: 0.7; display: block;"></video>
                                        <div
                                            style="position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;">
                                            <div
                                                style="width: 2.5rem; height: 2.5rem; border-radius: 9999px; background: rgba(255,255,255,0.2); backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center;">
                                                <svg style="width: 1rem; height: 1rem; fill: white; margin-left: 2px;"
                                                     viewBox="0 0 24 24">
                                                    <path d="M8 5v14l11-7z"/>
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif

                        </x-slot:content>
                    </x-mary-collapse>
                </div>
            @empty
                <p style="padding: 2rem; text-align: center; color: oklch(var(--bc) / 0.35); font-size: 0.875rem;">No
                    published stops yet.</p>
            @endforelse

        </div>
    </div>

    {{-- Locate me button --}}
    <button id="locate-btn" title="Show my location">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3"/><circle cx="12" cy="12" r="9" opacity="0.3"/>
        </svg>
    </button>

    {{-- Mobile toggle button --}}
    <button id="sidebar-toggle" @click="sidebarOpen = true">
        <svg style="width:1rem;height:1rem;fill:currentColor;" viewBox="0 0 20 20"><path d="M3 5h14M3 10h14M3 15h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" fill="none"/></svg>
        Trail Stops
    </button>

    {{-- Map --}}
    <div id="map-container">
        <div id="leaflet-map" style="width: 100%; height: 100%;"></div>
    </div>

    {{-- Lightbox --}}
    <div x-show="lightbox"
         x-transition.opacity
         @click.self="lightbox = null"
         @keydown.escape.window="lightbox = null"
         style="display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(0,0,0,0.85); display: flex; align-items: center; justify-content: center; padding: 1rem;"
         :style="lightbox ? 'display: flex;' : 'display: none;'">
        <button @click="lightbox = null"
                style="position: fixed; top: 1rem; right: 1rem; z-index: 10000; background: rgba(0,0,0,0.6); border: 1px solid rgba(255,255,255,0.3); color: white; border-radius: 9999px; width: 2.5rem; height: 2.5rem; font-size: 1.1rem; cursor: pointer; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
            ✕
        </button>
        <div style="position: relative; max-width: 90vw; width: 100%;">
            <template x-if="lightbox?.type === 'photo'">
                <img :src="lightbox.url" :alt="lightbox.title"
                     style="width: 100%; max-height: 85vh; object-fit: contain; border-radius: 0.5rem; box-shadow: 0 25px 50px rgba(0,0,0,0.5);">
            </template>
            <template x-if="lightbox?.type === 'video'">
                <video controls autoplay
                       style="width: 100%; max-height: 85vh; border-radius: 0.5rem; box-shadow: 0 25px 50px rgba(0,0,0,0.5);">
                    <source :src="lightbox.url" :type="lightbox.mime">
                </video>
            </template>
            <p x-text="lightbox?.title"
               style="text-align: center; color: rgba(255,255,255,0.5); font-size: 0.875rem; margin-top: 0.75rem;"></p>
        </div>
    </div>
</div>
