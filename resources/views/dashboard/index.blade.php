<x-app-layout title="Dashboard">

    @push('styles')
        <x-module.page-index-styles />
        @include('dashboard.partials.page-styles')
    @endpush

    @php
        $roleLabels = [
            'admin' => 'Administrator',
            'shop_manager' => 'Shop Manager',
            'attendant' => 'Shop Attendant',
            'warehouse' => 'Warehouse Manager',
        ];
    @endphp

    <div class="mi-page db-page space-y-5">

        @include('dashboard.partials.hero-card', [
            'greeting' => $greeting,
            'subtitle' => $subtitle,
            'roleLabel' => $roleLabels[$role] ?? 'User',
            'highlights' => $heroHighlights ?? [],
        ])

        @include('dashboard.partials.' . match ($role) {
            'admin' => 'admin',
            'shop_manager' => 'shop-manager',
            'attendant' => 'shop-attendant',
            'warehouse' => 'warehouse',
            default => 'admin',
        })

    </div>
</x-app-layout>
