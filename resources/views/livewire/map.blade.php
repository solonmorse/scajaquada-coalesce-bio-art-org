<?php

use App\Models\Stop;
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
                'type' => $stop->type->value,
                'type_label' => $stop->type->label(),
                'type_color' => $stop->type->color(),
                'latitude' => (float) $stop->latitude,
                'longitude' => (float) $stop->longitude,
                'trail_order' => $stop->trail_order,
                'photo' => $stop->getMedia('photo')->map(fn ($m) => [
                    'url' => $m->getUrl(),
                    'mime_type' => $m->mime_type,
                    'name' => $m->name,
                ])->toBase()->merge(
                    $stop->assets->where('type', 'photo')->map(fn ($a) => [
                        'url' => $a->getFirstMediaUrl('file'),
                        'mime_type' => $a->getFirstMedia('file')?->mime_type,
                        'name' => $a->title,
                    ])
                )->values()->toArray(),
                'audio' => $stop->getMedia('audio')->map(fn ($m) => [
                    'url' => $m->getUrl(),
                    'mime_type' => $m->mime_type,
                    'name' => $m->name,
                ])->toBase()->merge(
                    $stop->assets->where('type', 'audio')->map(fn ($a) => [
                        'url' => $a->getFirstMediaUrl('file'),
                        'mime_type' => $a->getFirstMedia('file')?->mime_type,
                        'name' => $a->title,
                    ])
                )->values()->toArray(),
                'video' => $stop->getMedia('video')->map(fn ($m) => [
                    'url' => $m->getUrl(),
                    'mime_type' => $m->mime_type,
                    'name' => $m->name,
                ])->toBase()->merge(
                    $stop->assets->where('type', 'video')->map(fn ($a) => [
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

    {{-- Sidebar --}}
    <div id="sidebar">
        <div style="padding: 1rem; border-bottom: 1px solid oklch(var(--b3)); background: oklch(var(--b2));">
            <h1 style="margin: 0; font-size: 1.1rem; font-weight: 700;">Stops</h1>
            <p style="margin: 0.25rem 0 0; font-size: 0.8rem; color: oklch(var(--bc) / 0.6);">Explore stops along the trail</p>
        </div>

        {{-- Accordion stop list --}}
        <div style="flex: 1;">
            <template x-if="stops.length === 0">
                <p style="padding: 1.5rem; text-align: center; color: oklch(var(--bc) / 0.4); font-size: 0.9rem;">No published stops yet.</p>
            </template>
            <template x-for="stop in stops" :key="stop.id">
                <div style="border-bottom: 1px solid oklch(var(--b3));">
                    {{-- Accordion header --}}
                    <button @click="selectStop(stop)"
                            style="width: 100%; text-align: left; padding: 0.75rem 1rem; background: none; border: none; cursor: pointer; display: flex; align-items: flex-start; justify-content: space-between; gap: 0.5rem; transition: background 0.15s;"
                            :style="openStopId === stop.id ? 'background: oklch(var(--b2));' : ''"
                            @mouseenter="$el.style.background = 'oklch(var(--b2))'"
                            @mouseleave="$el.style.background = openStopId === stop.id ? 'oklch(var(--b2))' : ''">
                        <div>
                            <div style="font-weight: 600; font-size: 0.9rem;" x-text="stop.title"></div>
                            
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.4rem; flex-shrink: 0;">
                            <span class="type-badge" :class="'badge-' + stop.type_color" x-text="stop.type_label"></span>
                            <span style="font-size: 0.7rem; color: oklch(var(--bc) / 0.4); transition: transform 0.2s;"
                                  :style="openStopId === stop.id ? 'transform: rotate(180deg)' : ''">▼</span>
                        </div>
                    </button>

                    {{-- Accordion body --}}
                    <div x-show="openStopId === stop.id"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="detail-panel"
                         style="border-top: 1px solid oklch(var(--b3));">

                        <p style="font-size: 0.875rem; color: oklch(var(--bc) / 0.7); line-height: 1.5; margin: 0 0 0.75rem;"
                           x-text="stop.description || 'No description available.'"></p>

                        <template x-if="stop.photo.length > 0">
                            <div>
                                <p class="media-section-title">Photos</p>
                                <div class="photo-grid">
                                    <template x-for="photo in stop.photo" :key="photo.url">
                                        <img :src="photo.url" :alt="photo.name" loading="lazy"
                                             style="cursor: zoom-in;"
                                             @click="lightbox = { type: 'photo', url: photo.url, mime: photo.mime_type, title: photo.name }">
                                    </template>
                                </div>
                            </div>
                        </template>

                        <template x-if="stop.audio.length > 0">
                            <div>
                                <p class="media-section-title">Audio</p>
                                <template x-for="track in stop.audio" :key="track.url">
                                    <div>
                                        <p style="font-size: 0.75rem; color: oklch(var(--bc) / 0.5); margin: 0.2rem 0;" x-text="track.name"></p>
                                        <audio controls :src="track.url" :type="track.mime_type"></audio>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <template x-if="stop.video.length > 0">
                            <div>
                                <p class="media-section-title">Video</p>
                                <template x-for="clip in stop.video" :key="clip.url">
                                    <div style="position: relative; cursor: pointer; background: #000; border-radius: 0.375rem; overflow: hidden; margin-bottom: 0.5rem;"
                                         @click="lightbox = { type: 'video', url: clip.url, mime: clip.mime_type, title: clip.name }">
                                        <video :src="clip.url + '#t=0.5'" preload="metadata" style="width: 100%; opacity: 0.7; display: block;"></video>
                                        <div style="position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;">
                                            <div style="width: 3rem; height: 3rem; border-radius: 9999px; background: rgba(255,255,255,0.2); backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center;">
                                                <svg style="width: 1.25rem; height: 1.25rem; fill: white; margin-left: 3px;" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>
    </div>

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
                <img :src="lightbox.url" :alt="lightbox.title" style="width: 100%; max-height: 85vh; object-fit: contain; border-radius: 0.5rem; box-shadow: 0 25px 50px rgba(0,0,0,0.5);">
            </template>
            <template x-if="lightbox?.type === 'video'">
                <video controls autoplay style="width: 100%; max-height: 85vh; border-radius: 0.5rem; box-shadow: 0 25px 50px rgba(0,0,0,0.5);">
                    <source :src="lightbox.url" :type="lightbox.mime">
                </video>
            </template>
            <p x-text="lightbox?.title" style="text-align: center; color: rgba(255,255,255,0.5); font-size: 0.875rem; margin-top: 0.75rem;"></p>
        </div>
    </div>
</div>
