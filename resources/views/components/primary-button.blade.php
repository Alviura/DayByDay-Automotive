<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2.5 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest shadow-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 active:brightness-90 transition ease-in-out duration-150']) }}
        style="background:linear-gradient(135deg,#f97316,#ea580c);box-shadow:0 4px 14px rgba(249,115,22,.35)"
        onmouseover="this.style.boxShadow='0 6px 20px rgba(249,115,22,.5)'"
        onmouseout="this.style.boxShadow='0 4px 14px rgba(249,115,22,.35)'">
    {{ $slot }}
</button>
