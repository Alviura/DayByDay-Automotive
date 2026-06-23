<x-app-layout title="Notifications">

    @push('styles')
        <x-module.page-index-styles />
        <style>
            .nt-page { max-width: 52rem; }
            .nt-item { display: flex; gap: .85rem; padding: 1rem 1.1rem; border-bottom: 1px solid #f3f4f6; transition: background .15s; }
            .nt-item:hover { background: #fafafa; }
            .nt-item--unread { background: #fffbf7; }
            .nt-icon {
                width: 2.35rem; height: 2.35rem; border-radius: .65rem; flex-shrink: 0;
                display: flex; align-items: center; justify-content: center; font-size: .85rem;
            }
            .nt-icon--unread { background: #fff0eb; color: #ea580c; }
            .nt-icon--read { background: #f3f4f6; color: #9ca3af; }
            .nt-title { font-size: .85rem; font-weight: 700; color: #111827; }
            .nt-message { margin-top: .2rem; font-size: .8rem; color: #6b7280; line-height: 1.45; }
            .nt-meta { margin-top: .35rem; font-size: .68rem; color: #9ca3af; display: flex; flex-wrap: wrap; gap: .5rem; }
            .nt-actions { margin-left: auto; flex-shrink: 0; display: flex; flex-direction: column; align-items: flex-end; gap: .35rem; }
        </style>
    @endpush

    <div class="mi-page nt-page space-y-5">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-bell"></i></div>
                <div>
                    <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">Notification Center</h1>
                    <p class="mt-0.5 text-sm text-gray-500">System activity across approvals, transfers, procurement, and inventory.</p>
                </div>
            </div>
            @if ($unreadCount > 0)
                <form action="{{ route('notifications.read-all') }}" method="POST">
                    @csrf
                    <button type="submit" class="mi-btn mi-btn-secondary text-sm">
                        <i class="fas fa-check-double"></i> Mark all read
                    </button>
                </form>
            @endif
        </div>

        <div class="mi-card overflow-hidden">
            @forelse ($notifications as $note)
                @php
                    $data = $note->data ?? [];
                    $icon = $data['icon'] ?? 'fa-circle-info';
                    $url = $data['url'] ?? null;
                @endphp
                <div class="nt-item {{ $note->read_at ? '' : 'nt-item--unread' }}">
                    <span class="nt-icon {{ $note->read_at ? 'nt-icon--read' : 'nt-icon--unread' }}">
                        <i class="fas {{ $icon }}"></i>
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="nt-title">{{ $data['title'] ?? 'Notification' }}</p>
                        <p class="nt-message">{{ $data['message'] ?? '' }}</p>
                        <div class="nt-meta">
                            @if (! empty($data['module']))
                                <span>{{ $data['module'] }}</span>
                            @endif
                            @if (! empty($data['reference']))
                                <span class="font-mono">{{ $data['reference'] }}</span>
                            @endif
                            <span>{{ $note->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                    <div class="nt-actions">
                        @if ($url)
                            @if ($note->read_at)
                                <a href="{{ $url }}" class="text-xs font-semibold text-orange-600 hover:underline">Open</a>
                            @else
                                <form action="{{ route('notifications.read', $note->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="text-xs font-semibold text-orange-600 hover:underline">Open</button>
                                </form>
                            @endif
                        @endif
                        @unless ($note->read_at)
                            <span class="rounded-full bg-orange-100 px-2 py-0.5 text-[.62rem] font-bold uppercase tracking-wide text-orange-700">New</span>
                        @endunless
                    </div>
                </div>
            @empty
                <div class="px-6 py-14 text-center text-gray-400">
                    <i class="far fa-bell-slash mb-3 block text-3xl text-gray-200"></i>
                    <p class="text-sm font-medium text-gray-500">No notifications yet</p>
                    <p class="mt-1 text-xs">Activity from approvals, transfers, and procurement will appear here.</p>
                </div>
            @endforelse
        </div>

        @if ($notifications->hasPages())
            <div>{{ $notifications->links() }}</div>
        @endif
    </div>
</x-app-layout>
