@extends('layouts.admin')
@section('title', 'Tambah Penjual')
@section('page-title', 'Penjual')
@section('content')
<x-ui.page-header title="Tambah penjual" :breadcrumbs="[['label' => 'Penjual', 'url' => route('admin.penjual.index')], ['label' => 'Tambah penjual']]" />
<form method="POST" action="{{ route('admin.penjual.store') }}" enctype="multipart/form-data" class="admin-seller-form">
    @csrf
    <div class="admin-seller-form__profile">
        <x-ui.card><x-ui.avatar-upload name="foto_toko" initials="S" /><div class="admin-seller-form__identity"><strong>{{ old('name', 'Penjual baru') }}</strong><span>{{ old('nama_toko', 'Toko baru') }}</span></div>@error('foto_toko')<p class="ui-error" role="alert">{{ $message }}</p>@enderror</x-ui.card>
    </div>
    <div class="admin-seller-form__details">
        <x-ui.card><h2>Akun penjual</h2><div class="ui-form-grid">
            <x-ui.field label="Nama lengkap" name="name"><x-ui.input id="name" name="name" value="{{ old('name') }}" required /></x-ui.field>
            <x-ui.field label="Email untuk masuk" name="email"><x-ui.input id="email" type="email" name="email" value="{{ old('email') }}" required /></x-ui.field>
            <x-ui.field label="NISN" name="nisn"><x-ui.input id="nisn" name="nisn" value="{{ old('nisn') }}" required inputmode="numeric" maxlength="10" /></x-ui.field>
            <x-ui.field label="Password" name="password" help="Minimal 8 karakter."><x-ui.input id="password" type="password" name="password" required minlength="8" /></x-ui.field>
            <x-ui.field class="ui-form-grid__full" label="Alamat penjual" name="alamat"><x-ui.textarea id="alamat" name="alamat" rows="3" required>{{ old('alamat') }}</x-ui.textarea></x-ui.field>
        </div></x-ui.card>
        <x-ui.card><h2>Data toko</h2><div class="ui-form-grid">
            <x-ui.field label="Nama toko" name="nama_toko"><x-ui.input id="nama_toko" name="nama_toko" value="{{ old('nama_toko') }}" required /></x-ui.field>
            <x-ui.field label="Nomor WhatsApp" name="nomor_whatsapp"><x-ui.input id="nomor_whatsapp" name="nomor_whatsapp" value="{{ old('nomor_whatsapp') }}" required placeholder="Contoh: 08123456789" /></x-ui.field>
            <x-ui.field class="ui-form-grid__full" label="Lokasi / kelas" name="lokasi_kelas"><x-ui.input id="lokasi_kelas" name="lokasi_kelas" value="{{ old('lokasi_kelas') }}" required placeholder="Contoh: Kantin No. 4 atau Kelas XII-A" /></x-ui.field>
        </div><x-ui.form-actions cancel-url="{{ route('admin.penjual.index') }}" submit-label="Buat akun penjual" /></x-ui.card>
    </div>
</form>
@endsection
