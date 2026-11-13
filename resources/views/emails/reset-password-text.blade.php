Reset Password SiswaMart

Halo{{ !empty($userName ?? $user?->name) ? ' ' . ($userName ?? $user?->name) : '' }},

Kami menerima permintaan untuk mengatur ulang password akun SiswaMart kamu. Jika kamu mengajukan permintaan ini, buka tautan di bawah untuk melanjutkan proses penggantian password:

{{ $url }}

Tautan ini berlaku selama {{ $expire ?? 60 }} menit, sesuai masa berlaku token yang dikonfigurasi pada aplikasi.

Terima kasih,
Tim SiswaMart

----------------------------------------------------------------------
Jika kamu tidak meminta pengaturan ulang password, abaikan email ini. Password akunmu tidak akan berubah tanpa tindakan lebih lanjut.
