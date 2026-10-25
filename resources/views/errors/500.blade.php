@extends('layouts.public')

@section('title', 'Terjadi Gangguan | SiswaMart')

@section('content')
<main class="max-w-4xl mx-auto px-4 sm:px-6 py-16 sm:py-24">
    <x-public.empty-state title="Ada gangguan sementara" icon="alert-triangle">
        <p>Silakan coba lagi beberapa saat lagi atau kembali ke beranda.</p>
        <a href="{{ route('public.home') }}" class="btn-primary mt-6 inline-flex items-center justify-center px-7 py-3">Ke beranda</a>
    </x-public.empty-state>
</main>
@endsection

