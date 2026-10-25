@extends('layouts.public')

@section('title', 'Halaman Tidak Ditemukan | SiswaMart')

@section('content')
<main class="max-w-4xl mx-auto px-4 sm:px-6 py-16 sm:py-24">
    <x-public.empty-state title="404 — Halaman tidak ditemukan" icon="circle-x">
        <p>Halaman yang kamu cari mungkin sudah dipindahkan atau alamatnya kurang tepat.</p>
        <a href="{{ route('public.home') }}" class="btn-primary mt-6 inline-flex items-center justify-center px-7 py-3">Ke beranda</a>
    </x-public.empty-state>
</main>
@endsection
