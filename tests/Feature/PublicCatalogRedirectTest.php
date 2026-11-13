<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicCatalogRedirectTest extends TestCase
{
    public function test_legacy_products_listing_redirects_to_categories(): void
    {
        $this->get('/produk')
            ->assertStatus(301)
            ->assertRedirect(route('public.kategori.index'));
    }
}
