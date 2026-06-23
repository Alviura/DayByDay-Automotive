@props([
    'notifications',
    'unreadCount' => 0,
])

<div class="relative" x-data="{ open: false }">
    <button @click="open = !open"
            type="button"
            class="relative flex h-9 w-9 items-center justify-center rounded-lg text-white/65 transition hover:bg-white/10 hover:text-white">
        <i class="far fa-bell text-[1.05rem]"></i>
        @if ($unreadCount > 0)
            <span class="absolute right-1.5 top-1.5 flex h-4 min-w-4 items-center justify-center rounded-full px-0.5 text-[.5rem] font-bold text-white ring-2"
                  style="background:var(--accent);ring-color:#18181b">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>
    <div x-cloak x-show="open" @click.outside="open = false"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         class="absolute right-0 z-50 mt-2 w-80 overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-black/[.08]"
         style="transform-origin:top right">
        <div class="flex items-center justify-between border-b border-zinc-100 px-4 py-3">
            <p class="text-sm font-bold text-zinc-900">Notifications</p>
            <span class="text-[.68rem] font-semibold" style="color:var(--accent)">{{ $unreadCount }} new</span>
        </div>
        <div class="max-h-72 divide-y divide-zinc-50 overflow-y-auto">
            @forelse ($notifications as $note)
                @php
                    $data = $note->data ?? [];
                    $icon = $data['icon'] ?? 'fa-circle-info';
                    $title = $data['title'] ?? 'Notification';
                    $message = $data['message'] ?? '';
                    $url = $data['url'] ?? route('notifications.index');
                @endphp
                <form action="{{ route('notifications.read', $note->id) }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="flex w-full items-start gap-3 px-4 py-3 text-left transition hover:bg-stone-50">
                        <span class="mt-0.5 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg {{ $note->read_at ? 'bg-zinc-100 text-zinc-400' : '' }}"
                              style="{{ ! $note->read_at ? 'background:rgba(249,115,22,.12);color:var(--accent)' : '' }}">
                            <i class="fas {{ $icon }} text-xs"></i>
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-semibold text-zinc-800">{{ $title }}</p>
                            <p class="mt-0.5 text-xs leading-snug text-zinc-500">{{ \Illuminate\Support\Str::limit($message, 72) }}</p>
                            <p class="mt-0.5 text-[.6rem] text-zinc-400">{{ $note->created_at->diffForHumans() }}</p>
                        </div>
                        @unless ($note->read_at)
                            <span class="mt-2 h-2 w-2 flex-shrink-0 rounded-full" style="background:var(--accent)"></span>
                        @endunless
                    </button>
                </form>
            @empty
                <div class="px-4 py-8 text-center text-zinc-400">
                    <i class="far fa-bell-slash mb-2 block text-2xl text-zinc-200"></i>
                    <p class="text-xs">You're all caught up</p>
                </div>
            @endforelse
        </div>
        <div class="flex items-center justify-between border-t border-zinc-100 bg-zinc-50/80 px-4 py-2.5">
            <a href="{{ route('notifications.index') }}" class="text-[.68rem] font-semibold text-orange-600 hover:underline">
                View all
            </a>
            @if ($unreadCount > 0)
                <form action="{{ route('notifications.read-all') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-[.68rem] font-semibold text-zinc-500 hover:text-zinc-800">
                        Mark all read
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>
