# SiswaMart Mobile — Documentation Index

Dokumentasi pengembangan aplikasi mobile SiswaMart untuk Android dan iOS.

| Dokumen | Isi |
|---|---|
| [PRD_MOBILE.md](PRD_MOBILE.md) | Tujuan produk, target pengguna, scope MVP, kebutuhan fungsional, UX, acceptance criteria, dan roadmap |
| [MOBILE_APP_ARCHITECTURE.md](MOBILE_APP_ARCHITECTURE.md) | Arsitektur Flutter, struktur folder, lapisan aplikasi, integrasi API, cache, deep link, testing, dan release |
| [API_SPECIFICATION.md](API_SPECIFICATION.md) | Kontrak endpoint yang sudah ada, perilaku aktual, gap API, aturan visibilitas produk, dan test checklist |

## Urutan membaca

1. Baca PRD mobile untuk memahami ruang lingkup MVP.
2. Baca API specification sebelum membuat data source atau DTO Flutter.
3. Baca mobile architecture sebelum membuat struktur project dan implementasi fitur.
4. Cocokkan perubahan backend terhadap API specification dan test checklist.

## Aturan penting

- Laravel existing tetap menjadi source of truth.
- Gunakan production API canonical domain https://siswamart.app/api/v1.
- Jangan membuat database baru untuk katalog mobile.
- Jangan mengasumsikan endpoint banner atau home aggregator sudah tersedia.
- Jangan menggunakan endpoint terproteksi untuk fitur katalog publik.
- Sinkronkan docs/06-api/openapi.yaml dengan route dan controller aktual sebelum menggunakannya untuk code generation.

**Status:** dokumen perencanaan dan spesifikasi. Implementasi mobile belum dimulai oleh dokumen ini.
