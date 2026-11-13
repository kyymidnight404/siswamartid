@php
    $displayName = str_replace("'", '’', $user->name);
    $displayStoreName = isset($storeName) && $storeName ? str_replace("'", '’', $storeName) : null;
@endphp

<footer class="dashboard-sidebar-footer">
    <div class="dashboard-sidebar-profile">
        <span class="dashboard-sidebar-avatar" aria-hidden="true">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
        <span class="dashboard-sidebar-profile-copy">
            <span class="dashboard-sidebar-name" title="{{ $displayName }}">{{ $displayName }}</span>
            <span class="dashboard-sidebar-subtitle" title="{{ $displayStoreName ?? $role }}">{{ $displayStoreName ?? $role }}</span>
        </span>
    </div>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="sidebar-logout-button">
            <x-public.home-icon name="logout" :size="18" aria-hidden="true" />
            Keluar
        </button>
    </form>
</footer>
