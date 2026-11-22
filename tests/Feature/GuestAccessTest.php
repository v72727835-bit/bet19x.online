<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GuestAccessTest extends TestCase
{
    private function fakeCatalogue(): void
    {
        Cache::forget('astech_games');
        Http::fake(['*' => Http::response(['status' => 'success', 'content' => ['games' => [
            ['gameId' => 'g1', 'title' => 'Aviator', 'provider' => 'spribe', 'category' => 'crash', 'currencies' => ['INR']],
            ['gameId' => 'g2', 'title' => 'Slot X', 'provider' => 'pp', 'category' => 'slots', 'currencies' => ['INR']],
        ]]])]);
    }

    public function test_guest_can_browse_game_list_and_meta(): void
    {
        $this->fakeCatalogue();

        $this->getJson('/games/list')->assertOk()->assertJsonPath('total', 2);
        $this->getJson('/games/meta')->assertOk()->assertJsonPath('categories.crash', 1);
    }

    public function test_meta_includes_provider_names_and_logos(): void
    {
        $this->fakeCatalogue();

        $this->getJson('/games/meta')
            ->assertOk()
            ->assertJsonPath('providers.spribe', 1)
            ->assertJsonPath('provider_info.spribe.name', 'Spribe')
            ->assertJsonPath('provider_info.pp.name', 'PragmaticPlay-EU');
        $this->assertNotNull($this->getJson('/games/meta')->json('provider_info.spribe.logo'));
    }

    public function test_games_can_be_listed_for_one_provider(): void
    {
        $this->fakeCatalogue();
        $this->getJson('/games/list?provider=spribe')->assertOk()->assertJsonPath('total', 1)->assertJsonPath('data.0.title', 'Aviator');
    }

    public function test_guest_cannot_launch_a_game(): void
    {
        $this->fakeCatalogue();
        $this->get('/games/launch/g1')->assertRedirect(route('login'));
    }

    public function test_guest_cannot_use_wallet_pages_or_deposit(): void
    {
        $this->get('/wallet/deposit')->assertRedirect(route('login'));
        $this->get('/wallet/withdraw')->assertRedirect(route('login'));
        $this->post('/wallet/deposit', ['amount' => 500, 'payment_method' => 'upi'])->assertRedirect(route('login'));
        $this->post('/wallet/withdraw', ['amount' => 500, 'bank_details' => 'x'])->assertRedirect(route('login'));
    }

    public function test_games_page_redirects_to_dashboard(): void
    {
        $this->get('/games?provider=pp')->assertRedirect('/dashboard?provider=pp#ag-root');
    }
}
