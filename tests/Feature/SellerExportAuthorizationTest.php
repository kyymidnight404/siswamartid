<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SellerExportAuthorizationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_only_admin_can_download_seller_export(): void
    {
        $guestResponse = $this->get(route('admin.penjual.export'));
        $guestResponse->assertRedirect(route('login'));

        $seller = User::create([
            'name' => 'Seller Export Test',
            'email' => 'seller-export-' . uniqid() . '@example.test',
            'password' => bcrypt('password'),
            'role' => 'penjual',
            'is_active' => true,
        ]);
        $this->actingAs($seller)->get(route('admin.penjual.export'))->assertForbidden();

        $admin = User::create([
            'name' => 'Admin Export Test',
            'email' => 'admin-export-' . uniqid() . '@example.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);
        $this->actingAs($admin)->get(route('admin.penjual.export'))->assertDownload('rekapitulasi-penjual-siswamart-' . now()->format('Y-m-d') . '.xlsx');
    }
}
