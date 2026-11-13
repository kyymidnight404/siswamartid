@props([
    'pdfUrl',
    'xlsxUrl',
    'label' => 'Unduh Laporan',
    'align' => 'right',
])

<div
    x-data="{
        open: false,
        activeItem: -1,
        downloading: null,
        items: [],
        init() {
            this.$nextTick(() => {
                this.items = Array.from(this.$refs.menuContainer?.querySelectorAll('[role=\'menuitem\']') || []);
            });
        },
        toggle() {
            this.open = !this.open;
            if (this.open) {
                this.activeItem = -1;
                this.$nextTick(() => {
                    this.items = Array.from(this.$refs.menuContainer?.querySelectorAll('[role=\'menuitem\']') || []);
                    if (this.items.length > 0) {
                        this.activeItem = 0;
                        this.items[0]?.focus();
                    }
                });
            }
        },
        close(focusButton = true) {
            this.open = false;
            this.activeItem = -1;
            if (focusButton) {
                this.$refs.triggerButton?.focus();
            }
        },
        focusNext() {
            if (this.items.length === 0) return;
            this.activeItem = (this.activeItem + 1) % this.items.length;
            this.items[this.activeItem]?.focus();
        },
        focusPrev() {
            if (this.items.length === 0) return;
            this.activeItem = (this.activeItem - 1 + this.items.length) % this.items.length;
            this.items[this.activeItem]?.focus();
        },
        triggerDownload(format) {
            this.downloading = format;
            setTimeout(() => {
                this.downloading = null;
                this.close(true);
            }, 3500);
        }
    }"
    @keydown.escape.stop="close(true)"
    @click.outside="close(false)"
    class="relative inline-block text-left download-menu"
>
    {{-- Trigger Button --}}
    <button
        x-ref="triggerButton"
        type="button"
        @click="toggle()"
        :aria-expanded="open.toString()"
        aria-haspopup="menu"
        class="download-menu__button inline-flex items-center justify-between gap-3 font-extrabold cursor-pointer transition select-none text-sm"
        style="
            background-color: var(--sm-primary);
            color: var(--sm-on-primary, #ffffff);
            border: 3px solid var(--sm-ink);
            box-shadow: 0 4px 0 var(--sm-ink);
            border-radius: 9999px;
            min-height: 46px;
            padding: 0 18px;
        "
    >
        <span class="inline-flex items-center gap-2">
            <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
            </svg>
            <span>{{ $label }}</span>
        </span>
        <svg
            class="w-[16px] h-[16px] flex-shrink-0 transition-transform duration-150"
            :class="{ 'rotate-180': open }"
            fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"
        >
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
        </svg>
    </button>

    {{-- Dropdown Menu --}}
    <div
        x-ref="menuContainer"
        x-show="open"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 translate-y-1 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-1 scale-95"
        @keydown.down.prevent="focusNext()"
        @keydown.up.prevent="focusPrev()"
        @keydown.tab="close(false)"
        role="menu"
        aria-orientation="vertical"
        class="download-menu__dropdown absolute z-50 mt-2 p-2 bg-white text-[var(--sm-ink)] focus:outline-none"
        :class="{ 'right-0': '{{ $align }}' === 'right', 'left-0': '{{ $align }}' !== 'right' }"
        style="
            border: 3px solid var(--sm-ink);
            box-shadow: 0 5px 0 var(--sm-ink);
            border-radius: 16px;
            width: min(290px, calc(100vw - 2rem));
            display: none;
        "
        :style="{ display: open ? 'block' : 'none' }"
    >
        <div class="flex flex-col gap-1.5" role="none">
            {{-- Item 1: PDF --}}
            <a
                href="{{ $pdfUrl }}"
                @click="triggerDownload('pdf')"
                role="menuitem"
                tabindex="-1"
                class="download-menu__item flex items-center gap-3.5 p-3 rounded-xl transition focus:outline-none"
                style="min-height: 56px;"
            >
                <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" style="background-color: var(--sm-primary-soft); border: 2px solid var(--sm-ink); color: var(--sm-ink);">
                    <template x-if="downloading === 'pdf'">
                        <svg class="animate-spin w-5 h-5 text-[var(--sm-primary)]" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </template>
                    <template x-if="downloading !== 'pdf'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                    </template>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between">
                        <span class="font-black text-sm text-[var(--sm-ink)]">PDF</span>
                        <span x-show="downloading === 'pdf'" class="text-[11px] font-bold text-[var(--sm-primary)] animate-pulse">Menyiapkan...</span>
                    </div>
                    <p class="text-xs text-[var(--sm-text-muted)] font-medium mt-0.5 leading-snug">Untuk dibaca dan dicetak</p>
                </div>
            </a>

            {{-- Item 2: Sheet Excel --}}
            <a
                href="{{ $xlsxUrl }}"
                @click="triggerDownload('xlsx')"
                role="menuitem"
                tabindex="-1"
                class="download-menu__item flex items-center gap-3.5 p-3 rounded-xl transition focus:outline-none"
                style="min-height: 56px;"
            >
                <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" style="background-color: var(--sm-primary-soft); border: 2px solid var(--sm-ink); color: var(--sm-ink);">
                    <template x-if="downloading === 'xlsx'">
                        <svg class="animate-spin w-5 h-5 text-[var(--sm-primary)]" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </template>
                    <template x-if="downloading !== 'xlsx'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 01-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-7.5A1.125 1.125 0 0112 18.375m9.75-12.75c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125m19.5 0v1.5c0 .621-.504 1.125-1.125 1.125M2.25 5.625v1.5c0 .621.504 1.125 1.125 1.125m0 0h17.25m-17.25 0h7.5c.621 0 1.125.504 1.125 1.125M3.375 8.25c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125m17.25-3.75h-7.5c-.621 0-1.125.504-1.125 1.125m8.625-1.125c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h17.25" />
                        </svg>
                    </template>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between">
                        <span class="font-black text-sm text-[var(--sm-ink)]">Sheet Excel</span>
                        <span x-show="downloading === 'xlsx'" class="text-[11px] font-bold text-[var(--sm-primary)] animate-pulse">Menyiapkan...</span>
                    </div>
                    <p class="text-xs text-[var(--sm-text-muted)] font-medium mt-0.5 leading-snug">Unduh laporan untuk diolah (.xlsx)</p>
                </div>
            </a>
        </div>
    </div>
</div>

<style>
    .download-menu__button:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 0 var(--sm-ink) !important;
    }
    .download-menu__button:active {
        transform: translateY(2px);
        box-shadow: 0 2px 0 var(--sm-ink) !important;
    }
    .download-menu__button:focus-visible {
        outline: 3px solid var(--sm-primary);
        outline-offset: 3px;
    }
    .download-menu__item:hover,
    .download-menu__item:focus {
        background-color: var(--sm-primary-soft);
        outline: none;
    }
    @media (prefers-reduced-motion: reduce) {
        .download-menu__button,
        .download-menu__dropdown,
        .download-menu__item {
            transition: none !important;
            transform: none !important;
            animation: none !important;
        }
    }
</style>
