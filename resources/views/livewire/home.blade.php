<?php

use function Livewire\Volt\{state};

state([]);

?>

<div class="min-h-screen bg-base-100">

    {{-- Navigation --}}
    <x-mary-nav sticky class="bg-base-200 border-b border-base-300">
        <x-slot:brand>
            <span class="font-bold text-lg">Jesse Kregel Trail</span>
        </x-slot:brand>
        <x-slot:actions>
            <x-mary-button label="Resources" link="{{ route('resources.index') }}" class="btn-ghost btn-sm" />
            <x-mary-button label="Map" link="{{ route('map') }}" class="btn-ghost btn-sm" />
            @auth
                <x-mary-button label="Admin" link="/admin" class="btn-ghost btn-sm" />
            @endauth
            @guest
                <x-mary-button label="Sign In" link="/admin/login" class="btn-primary btn-sm" />
            @endguest
        </x-slot:actions>
    </x-mary-nav>

    {{-- Hero --}}
    <section class="max-w-5xl mx-auto px-6 pt-20 pb-16 text-center">
        <h1 class="text-5xl font-bold tracking-tight mb-4">Jesse Kregel Trail</h1>
        <x-mary-button label="Browse Resources" link="{{ route('resources.index') }}" class="btn-primary btn-lg" />
        <x-mary-button label="Explore the Map" link="{{ route('map') }}" class="btn-outline btn-lg" />
    </section>

    {{-- Category cards --}}
    <section class="max-w-5xl mx-auto px-6 pb-20">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <x-mary-card title="Audio" class="text-center">
                <x-slot:figure>
                    <div class="py-8 flex justify-center">
                        <x-phosphor-headphones-duotone class="w-16 h-16 text-primary" />
                    </div>
                </x-slot:figure>
                <p class="text-base-content/60">Sound recordings, compositions, and sonic explorations.</p>
            </x-mary-card>
            <x-mary-card title="Video" class="text-center">
                <x-slot:figure>
                    <div class="py-8 flex justify-center">
                        <x-phosphor-video-camera-duotone class="w-16 h-16 text-primary" />
                    </div>
                </x-slot:figure>
                <p class="text-base-content/60">Films, documentation, and moving image works.</p>
            </x-mary-card>
            <x-mary-card title="Photo" class="text-center">
                <x-slot:figure>
                    <div class="py-8 flex justify-center">
                        <x-phosphor-camera-duotone class="w-16 h-16 text-primary" />
                    </div>
                </x-slot:figure>
                <p class="text-base-content/60">Photography, still images, and visual documentation.</p>
            </x-mary-card>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="border-t border-base-300 bg-base-200">
        <div class="max-w-5xl mx-auto px-6 py-6 flex items-center justify-between text-sm text-base-content/50">
        </div>
    </footer>

</div>
