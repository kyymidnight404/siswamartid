<?php

namespace Database\Seeders;

use App\Models\Kategori;
use App\Models\Penjual;
use App\Models\Produk;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProdukSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Membuat produk dummy jajanan siswa untuk SiswaMart.
     * Menggunakan updateOrCreate berdasarkan slug agar aman dijalankan ulang.
     */
    public function run(): void
    {
        // Ambil ID kategori berdasarkan slug
        $katMakanan = Kategori::where('slug', 'makanan')->value('id');
        $katMinuman = Kategori::where('slug', 'minuman')->value('id');
        $katSnack   = Kategori::where('slug', 'snack')->value('id');
        $katDessert = Kategori::where('slug', 'dessert')->value('id');

        // Ambil penjual berdasarkan email user-nya
        $rina = Penjual::whereHas('user', fn ($q) => $q->where('email', 'rina@siswamart.test'))->first();
        $budi = Penjual::whereHas('user', fn ($q) => $q->where('email', 'budi@siswamart.test'))->first();
        $siti = Penjual::whereHas('user', fn ($q) => $q->where('email', 'siti@siswamart.test'))->first();

        $produkList = [
            // ── Dapur Rina (Makanan) ──────────────────────────────────────────
            [
                'penjual_id'    => $rina?->id,
                'kategori_id'   => $katMakanan,
                'nama'          => 'Nasi Goreng Spesial',
                'deskripsi'     => 'Nasi goreng homemade dengan telur, ayam suwir, dan sambal spesial. Porsi besar, harga terjangkau!',
                'harga'         => 12000,
                'stok'          => 'tersedia',
                'label_promosi' => 'Best Seller',
                'status'        => 'aktif',
            ],
            [
                'penjual_id'    => $rina?->id,
                'kategori_id'   => $katMakanan,
                'nama'          => 'Mie Ayam Kuah',
                'deskripsi'     => 'Mie lembut dengan topping ayam berbumbu dan kuah kaldu hangat. Cocok untuk makan siang.',
                'harga'         => 10000,
                'stok'          => 'tersedia',
                'label_promosi' => null,
                'status'        => 'aktif',
            ],
            [
                'penjual_id'    => $rina?->id,
                'kategori_id'   => $katMakanan,
                'nama'          => 'Lontong Sayur',
                'deskripsi'     => 'Lontong dengan sayur labu, tempe, dan sambal kacang. Menu sarapan favorit!',
                'harga'         => 8000,
                'stok'          => 'po',
                'label_promosi' => 'Pre-Order',
                'status'        => 'aktif',
            ],

            // ── Snack Budi (Snack & Minuman) ──────────────────────────────────
            [
                'penjual_id'    => $budi?->id,
                'kategori_id'   => $katSnack,
                'nama'          => 'Keripik Tempe Balado',
                'deskripsi'     => 'Keripik tempe renyah dengan bumbu balado pedas manis. Cocok buat teman belajar.',
                'harga'         => 5000,
                'stok'          => 'tersedia',
                'label_promosi' => 'Baru',
                'status'        => 'aktif',
            ],
            [
                'penjual_id'    => $budi?->id,
                'kategori_id'   => $katSnack,
                'nama'          => 'Pisang Coklat Crispy',
                'deskripsi'     => 'Pisang goreng dibalut adonan crispy, disiram coklat leleh. Enak dan mengenyangkan!',
                'harga'         => 6000,
                'stok'          => 'tersedia',
                'label_promosi' => null,
                'status'        => 'aktif',
            ],
            [
                'penjual_id'    => $budi?->id,
                'kategori_id'   => $katMinuman,
                'nama'          => 'Es Teh Manis Jumbo',
                'deskripsi'     => 'Es teh manis segar ukuran jumbo. Cocok di hari panas, harga bersahabat.',
                'harga'         => 4000,
                'stok'          => 'tersedia',
                'label_promosi' => null,
                'status'        => 'aktif',
            ],
            [
                'penjual_id'    => $budi?->id,
                'kategori_id'   => $katMinuman,
                'nama'          => 'Jus Alpukat Susu',
                'deskripsi'     => 'Jus alpukat creamy dicampur susu kental manis. Menyegarkan dan mengenyangkan.',
                'harga'         => 9000,
                'stok'          => 'tersedia',
                'label_promosi' => 'Favorit',
                'status'        => 'aktif',
            ],

            // ── Sweet Siti (Dessert & Minuman) ────────────────────────────────
            [
                'penjual_id'    => $siti?->id,
                'kategori_id'   => $katDessert,
                'nama'          => 'Puding Coklat Oreo',
                'deskripsi'     => 'Puding coklat lembut dengan topping krim dan remahan Oreo. Dessert wajib coba!',
                'harga'         => 7000,
                'stok'          => 'tersedia',
                'label_promosi' => 'Best Seller',
                'status'        => 'aktif',
            ],
            [
                'penjual_id'    => $siti?->id,
                'kategori_id'   => $katDessert,
                'nama'          => 'Es Krim Homemade',
                'deskripsi'     => 'Es krim buatan sendiri rasa vanilla, coklat, dan stroberi. Tanpa bahan pengawet!',
                'harga'         => 8000,
                'stok'          => 'tersedia',
                'label_promosi' => 'Tanpa Pengawet',
                'status'        => 'aktif',
            ],
            [
                'penjual_id'    => $siti?->id,
                'kategori_id'   => $katMinuman,
                'nama'          => 'Boba Matcha Susu',
                'deskripsi'     => 'Minuman boba matcha susu dengan pearl kenyal. Ala kafe, harga kantong pelajar.',
                'harga'         => 11000,
                'stok'          => 'tersedia',
                'label_promosi' => 'Hits',
                'status'        => 'aktif',
            ],
        ];

        $count = 0;
        foreach ($produkList as $data) {
            // Skip jika penjual_id atau kategori_id null (seeder penjual/kategori belum jalan)
            if (! $data['penjual_id'] || ! $data['kategori_id']) {
                continue;
            }

            $slug = Str::slug($data['nama']);

            Produk::updateOrCreate(
                ['slug' => $slug],
                array_merge($data, ['slug' => $slug])
            );

            $count++;
        }

        // Pastikan setiap penjual memiliki minimal 10 produk
        $allSellers = Penjual::all();
        $fallbackItems = [
            ['nama' => 'Roti Bakar Coklat Keju', 'slug_base' => 'roti-bakar-coklat-keju', 'kategori_id' => $katSnack, 'harga' => 8000, 'deskripsi' => 'Roti bakar empuk isi coklat lumer dan parutan keju gurih.'],
            ['nama' => 'Kentang Goreng Krispi', 'slug_base' => 'kentang-goreng-krispi', 'kategori_id' => $katSnack, 'harga' => 7000, 'deskripsi' => 'French fries renyah bertabur bumbu BBQ atau keju manis.'],
            ['nama' => 'Es Coklat Klasik', 'slug_base' => 'es-coklat-klasik', 'kategori_id' => $katMinuman, 'harga' => 8000, 'deskripsi' => 'Minuman coklat kental dingin disajikan dengan roti celup.'],
            ['nama' => 'Dimsum Ayam Kukus', 'slug_base' => 'dimsum-ayam-kukus', 'kategori_id' => $katSnack, 'harga' => 10000, 'deskripsi' => 'Dimsum ayam kenyal lembut isi 4 biji disajikan dengan saus chili oil.'],
            ['nama' => 'Cireng Krispi Bumbu Rujak', 'slug_base' => 'cireng-krispi-bumbu-rujak', 'kategori_id' => $katSnack, 'harga' => 6000, 'deskripsi' => 'Cireng goreng renyah kenyal dicocol saus rujak pedas manis.'],
            ['nama' => 'Nasi Uduk Komplit', 'slug_base' => 'nasi-uduk-komplit', 'kategori_id' => $katMakanan ?? $katSnack, 'harga' => 12000, 'deskripsi' => 'Nasi uduk harum gurih dengan orek tempe, bihun, dan telur dadar.'],
            ['nama' => 'Piscok Lumer Keju', 'slug_base' => 'piscok-lumer-keju', 'kategori_id' => $katSnack, 'harga' => 6000, 'deskripsi' => 'Pisang berbalut kulit lumpia renyah isi cokelat dan keju leleh.'],
            ['nama' => 'Susu Segar Strawberry', 'slug_base' => 'susu-segar-strawberry', 'kategori_id' => $katMinuman, 'harga' => 7000, 'deskripsi' => 'Susu sapi murni dingin dipadukan sirup strawberry segar.'],
            ['nama' => 'Pangsit Goreng Mayonais', 'slug_base' => 'pangsit-goreng-mayonais', 'kategori_id' => $katSnack, 'harga' => 8000, 'deskripsi' => 'Pangsit goreng isi olahan ayam krispi dicocol saus keju mayo.'],
            ['nama' => 'Kopi Cold Brew Aren', 'slug_base' => 'kopi-cold-brew-aren', 'kategori_id' => $katMinuman, 'harga' => 11000, 'deskripsi' => 'Kopi cold brew rendaman 12 jam dipadukan gula aren segar.'],
            ['nama' => 'Pancake Madu Mentega', 'slug_base' => 'pancake-madu-mentega', 'kategori_id' => $katDessert ?? $katSnack, 'harga' => 9000, 'deskripsi' => 'Pancake lembut bertumpuk dengan siraman madu asli dan mentega wangi.'],
            ['nama' => 'Bakso Aci Kuah Pedas', 'slug_base' => 'bakso-aci-kuah-pedas', 'kategori_id' => $katSnack, 'harga' => 10000, 'deskripsi' => 'Bakso aci kenyal dengan cuanki, sukro cikur, dan kuah pedas gurih jeruk limau.'],
        ];

        foreach ($allSellers as $seller) {
            $itemIndex = 0;
            while ($seller->produk()->count() < 10 && $itemIndex < count($fallbackItems)) {
                $item = $fallbackItems[$itemIndex++];
                $slug = $item['slug_base'] . '-' . $seller->id;
                if (! Produk::where('slug', $slug)->exists()) {
                    Produk::create([
                        'penjual_id' => $seller->id,
                        'kategori_id' => $item['kategori_id'],
                        'nama' => $item['nama'],
                        'slug' => $slug,
                        'deskripsi' => $item['deskripsi'],
                        'harga' => $item['harga'],
                        'stok' => 'tersedia',
                        'label_promosi' => null,
                        'status' => 'aktif',
                        'views' => rand(10, 50),
                    ]);
                    $count++;
                }
            }
        }

        $this->command->info("✅ {$count} produk berhasil dibuat. Setiap penjual memiliki minimal 10 produk.");
    }
}
