<x-app-layout>
    <x-slot name="header">
        <h1 class="font-black text-2xl text-[var(--sm-ink)] leading-tight">
            {{ __('Profil Akun') }}
        </h1>
    </x-slot>

    <div class="py-8 sm:py-10">
        <div class="max-w-7xl mx-auto space-y-6">
            <div class="p-5 sm:p-8 bg-[var(--sm-surface)] border-2 border-[var(--sm-border)] rounded-[24px] shadow-[4px_4px_0px_0px_var(--sm-border)]">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-5 sm:p-8 bg-[var(--sm-surface)] border-2 border-[var(--sm-border)] rounded-[24px] shadow-[4px_4px_0px_0px_var(--sm-border)]">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-5 sm:p-8 bg-[var(--sm-surface)] border-2 border-[var(--sm-border)] rounded-[24px] shadow-[4px_4px_0px_0px_var(--sm-border)]">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

