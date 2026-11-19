<?php

namespace Tests\Feature;

use App\Livewire\CartIcon;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Listing;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    private function createListing(int $stock = 10): Listing
    {
        $owner = User::factory()->create();

        $this->actingAs($owner);

        $shop = Shop::create([
            'name' => 'Test Shop',
            'name_ar' => 'متجر تجريبي',
            'slug' => 'test-shop-'.$owner->id,
            'user_id' => $owner->id,
            'currency' => 'AED',
            'is_active' => true,
            'is_completed' => true,
            'published_at' => now(),
        ]);

        $listing = Listing::create([
            'title' => 'Test Product',
            'title_ar' => 'منتج تجريبي',
            'slug' => 'test-product-'.$owner->id,
            'price' => '25.00',
            'currency' => 'AED',
            'quantity' => $stock,
            'image' => 'test-product.jpg',
            'price_condition' => 'fixed',
            'condition' => 'new',
            'shop_id' => $shop->id,
            'user_id' => $owner->id,
        ]);

        $listing->forceFill([
            'active' => true,
            'status' => 'published',
            'published_at' => now(),
            'expires_at' => null,
        ])->save();

        return $listing->refresh();
    }

    private function addCartItem(
        User $user,
        Listing $listing,
        int $quantity = 1
    ): CartItem {
        $cart = Cart::firstOrCreate([
            'user_id' => $user->id,
        ]);

        return $cart->items()->create([
            'listing_id' => $listing->id,
            'shop_id' => $listing->shop_id,
            'price' => $listing->price,
            'quantity' => $quantity,
        ]);
    }

    public function test_guest_cannot_access_cart(): void
    {
        auth()->logout();

        $this->get(route('cart.index'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_empty_cart(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('cart.index'))
            ->assertOk();
    }

    public function test_user_can_add_listing_to_cart(): void
    {
        $listing = $this->createListing();
        $buyer = User::factory()->create();

        $this->actingAs($buyer)
            ->post(route('cart.add', $listing));

        $this->assertDatabaseHas('cart_items', [
            'listing_id' => $listing->id,
            'quantity' => 1,
            'price' => '25.00',
        ]);
    }

    public function test_adding_existing_listing_increases_quantity(): void
    {
        $listing = $this->createListing(10);
        $buyer = User::factory()->create();

        $this->actingAs($buyer);

        $item = $this->addCartItem($buyer, $listing, 2);

        $this->post(route('cart.add', $listing), [
            'quantity' => 3,
        ]);

        $this->assertDatabaseHas('cart_items', [
            'id' => $item->id,
            'quantity' => 5,
        ]);
    }

    public function test_new_cart_item_cannot_exceed_available_stock(): void
    {
        $listing = $this->createListing(3);
        $buyer = User::factory()->create();

        $response = $this->actingAs($buyer)
            ->from(route('cart.index'))
            ->post(route('cart.add', $listing), [
                'quantity' => 4,
            ]);

        $response->assertRedirect(route('cart.index'));

        $response->assertSessionHasErrors('quantity');

        $this->assertDatabaseMissing('cart_items', [
            'listing_id' => $listing->id,
        ]);
    }

    public function test_new_cart_item_rejects_zero_quantity(): void
    {
        $listing = $this->createListing();
        $buyer = User::factory()->create();

        $response = $this->actingAs($buyer)
            ->from(route('cart.index'))
            ->post(route('cart.add', $listing), [
                'quantity' => 0,
            ]);

        $response->assertSessionHasErrors('quantity');

        $this->assertDatabaseMissing('cart_items', [
            'listing_id' => $listing->id,
        ]);
    }

    public function test_user_cannot_increase_quantity_beyond_stock(): void
    {
        $listing = $this->createListing(3);
        $buyer = User::factory()->create();

        $this->actingAs($buyer);

        $item = $this->addCartItem($buyer, $listing, 3);

        $this->post(route('cart.increase', $item));

        $this->assertDatabaseHas('cart_items', [
            'id' => $item->id,
            'quantity' => 3,
        ]);
    }

    public function test_user_can_decrease_quantity_but_not_below_one(): void
    {
        $listing = $this->createListing();
        $buyer = User::factory()->create();

        $this->actingAs($buyer);

        $item = $this->addCartItem($buyer, $listing, 2);

        $this->post(route('cart.decrease', $item));

        $this->assertDatabaseHas('cart_items', [
            'id' => $item->id,
            'quantity' => 1,
        ]);

        $this->post(route('cart.decrease', $item));

        $this->assertDatabaseHas('cart_items', [
            'id' => $item->id,
            'quantity' => 1,
        ]);
    }

    public function test_user_can_remove_own_cart_item(): void
    {
        $listing = $this->createListing();
        $buyer = User::factory()->create();

        $this->actingAs($buyer);

        $item = $this->addCartItem($buyer, $listing);

        $this->delete(route('cart.destroy', $item))
            ->assertRedirect();

        $this->assertDatabaseMissing('cart_items', [
            'id' => $item->id,
        ]);
    }

    public function test_user_cannot_modify_another_users_cart_item(): void
    {
        $listing = $this->createListing();

        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $item = $this->addCartItem($owner, $listing, 2);

        $this->actingAs($otherUser);

        $this->post(route('cart.increase', $item))
            ->assertForbidden();

        $this->post(route('cart.decrease', $item))
            ->assertForbidden();

        $this->delete(route('cart.destroy', $item))
            ->assertForbidden();

        $this->assertDatabaseHas('cart_items', [
            'id' => $item->id,
            'quantity' => 2,
        ]);
    }

    public function test_cart_page_displays_correct_total(): void
    {
        $listing = $this->createListing();
        $buyer = User::factory()->create();

        $this->actingAs($buyer);

        $this->addCartItem($buyer, $listing, 3);

        $this->get(route('cart.index'))
            ->assertOk()
            ->assertSee('75.00')
            ->assertSee('AED');
    }

    public function test_cart_icon_displays_total_quantity(): void
    {
        $listing = $this->createListing();
        $buyer = User::factory()->create();

        $this->actingAs($buyer);

        $this->addCartItem($buyer, $listing, 3);

        Livewire::test(CartIcon::class)
            ->assertSet('count', 3);
    }
}
