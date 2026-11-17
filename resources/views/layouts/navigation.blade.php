@php
    $isAdmin = auth()->user()?->role === 'admin';
    $dashboardRoute = $isAdmin ? route('admin.dashboard') : route('penjual.dashboard');
    $dashboardActive = $isAdmin ? request()->routeIs('admin.dashboard') : request()->routeIs('penjual.dashboard');
@endphp

<nav x-data="{ open: false }" aria-label="Navigasi akun" class="bg-white border-b border-[var(--sm-border)]">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ $dashboardRoute }}" aria-label="Kembali ke Dashboard SiswaMart" class="inline-flex min-h-[40px] items-center rounded-full focus-visible:outline-none">
                        <x-brand-logo static />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-6 sm:-my-px sm:ms-8 sm:flex sm:items-center">
                    <a href="{{ $dashboardRoute }}"
                       @if($dashboardActive) aria-current="page" @endif
                       class="inline-flex min-h-[40px] items-center rounded-full px-4 py-2 text-sm font-bold text-white transition hover:bg-white/15 focus-visible:outline-none">
                        {{ __('Dashboard') }}
                    </a>
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button type="button" class="inline-flex min-h-[40px] items-center gap-1.5 px-4 py-2 border-2 border-[var(--sm-border)] text-sm leading-4 font-bold rounded-full text-[var(--sm-ink)] bg-[var(--sm-surface)] hover:bg-[var(--sm-primary-soft)] shadow-[2px_2px_0px_0px_var(--sm-border)] focus:outline-none transition ease-in-out duration-150">
                            <span>{{ Auth::user()->name }}</span>

                            <svg class="fill-current h-4 w-4 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')" class="!text-[var(--sm-ink)] font-semibold">
                            {{ __('Profil') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    class="!text-[var(--sm-ink)] font-semibold"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Keluar') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button type="button"
                        @click="open = ! open"
                        aria-label="Buka menu navigasi"
                        aria-controls="account-mobile-menu"
                        :aria-expanded="open.toString()"
                        class="inline-flex min-h-[44px] min-w-[44px] items-center justify-center rounded-full border-2 border-[var(--sm-border)] bg-[var(--sm-surface)] p-2 text-[var(--sm-ink)] shadow-[2px_2px_0px_0px_var(--sm-border)] hover:bg-[var(--sm-primary-soft)] focus:outline-none transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div id="account-mobile-menu" :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden mx-3 mb-3 rounded-2xl border-2 border-[var(--sm-border)] bg-[var(--sm-surface)] p-2 shadow-[4px_4px_0px_0px_var(--sm-border)]">
        <div class="pt-1 pb-2 space-y-1">
            <x-responsive-nav-link :href="$dashboardRoute" :active="$dashboardActive" class="!text-[var(--sm-ink)] rounded-xl min-h-[44px] flex items-center">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-3 pb-1 border-t border-[var(--sm-border)]">
            <div class="px-3">
                <div class="font-bold text-base text-[var(--sm-ink)]">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-[var(--sm-text-muted)]">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')" class="!text-[var(--sm-ink)] rounded-xl min-h-[44px] flex items-center">
                    {{ __('Profil') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            class="!text-[var(--sm-ink)] rounded-xl min-h-[44px] flex items-center"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Keluar') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>

