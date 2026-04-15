<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.3+
- filament/filament (FILAMENT) - v5
- laravel/fortify (FORTIFY) - v1
- laravel/framework (LARAVEL) - v13
- livewire/flux (FLUXUI_FREE) - v2
- livewire/livewire (LIVEWIRE) - v4
- livewire/volt (VOLT) - v1
- laravel/boost (BOOST) - v2
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- pestphp/pest (PEST) - v4
- robsontenorio/mary (MARYUI) - v2
- spatie/laravel-medialibrary - v11
- spatie/laravel-permission - v7
- tailwindcss (TAILWINDCSS) - v4
- daisyui - v5

## Project Purpose

Audio/video/photo resource repository for bio art. The public interface uses MaryUI (DaisyUI-based) Volt components. The admin panel uses Filament 5 with Spatie Media Library for managing media assets.

## Architecture

- **Admin panel**: Filament 5 at `/admin` — manages all media resources, users, roles/permissions
- **Public interface**: MaryUI + Volt components in `resources/views/livewire/` — browsing resources
- **Media handling**: `spatie/laravel-medialibrary` — all media models should use `HasMedia` + `InteractsWithMedia`
- **Roles/permissions**: `spatie/laravel-permission` + `althinect/filament-spatie-roles-permissions`

## Skills Activation

- `fluxui-development` — Flux UI Free components
- `livewire-development` — Livewire 4 components
- `volt-development` — Volt single-file components (used for all public pages)
- `pest-testing` — Pest 4 testing
- `tailwindcss-development` — Tailwind CSS v4 styling

## Conventions

- Public pages are Volt components in `resources/views/livewire/`
- Filament resources in `app/Filament/Resources/`
- All media models implement `HasMedia` + `InteractsWithMedia` from Spatie
- Follow existing code conventions when creating or editing files

## Frontend Bundling

- `npm run build` or `composer run dev` to rebuild assets
- Vite inputs: `app.css`, `app.js`, `filament/admin/theme.css`, `public/ui/index.css`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion in `__construct()`.
- Always use explicit return type declarations.

=== laravel/core rules ===

# Laravel

- Use `php artisan make:` commands to scaffold new files.
- Prefer Eloquent and relationships over raw queries.
- Use Form Request classes for validation.
- Use named routes with `route()` helper.

=== pint/core rules ===

# Pint

- Run `vendor/bin/pint --dirty --format agent` after modifying PHP files.

</laravel-boost-guidelines>
