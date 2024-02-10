<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RedirectTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_redirect_store_valid_url()
    {
        $api_url = 'http://localhost:8000/api';

        $url = 'https://google.com';

        $response = $this->post($api_url . '/redirects', ['url' => $url]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('redirects', ['url' => $url]);
    }

    public function test_redirect_store_invalid_dns_url()
    {
        $api_url = 'http://localhost:8000/api';

        $url = 'https://google.com.teste.123';

        $response = $this->post($api_url . '/redirects', ['url' => $url]);

        $response->assertStatus(400);
        $this->assertDatabaseMissing('redirects', ['url' => $url]);
    }

    public function test_redirect_store_invalid_url()
    {
        $api_url = 'http://localhost:8000/api';

        $url = 'https://url-inexistente-provavelmente';

        $response = $this->post($api_url . '/redirects', ['url' => $url]);

        $response->assertStatus(400);
        $this->assertDatabaseMissing('redirects', ['url' => $url]);
    }

    public function test_redirect_store_application_url()
    {
        $api_url = 'http://localhost:8000/api';

        $url = 'https://localhost';

        $response = $this->post($api_url . '/redirects', ['url' => $url]);

        $response->assertStatus(400);
        $this->assertDatabaseMissing('redirects', ['url' => $url]);
    }

    public function test_redirect_store_http_url()
    {
        $api_url = 'http://localhost:8000/api';

        $url = 'http://google.com';

        $response = $this->post($api_url . '/redirects', ['url' => $url]);

        $response->assertStatus(400);
        $this->assertDatabaseMissing('redirects', ['url' => $url]);
    }

    public function test_redirect_store_status_url()
    {
        $api_url = 'http://localhost:8000/api';

        $url = 'http://ASDADSADSADSsdasdasdASDSADASGAFASXZVZ.com';

        $response = $this->post($api_url . '/redirects', ['url' => $url]);

        $response->assertStatus(400);
        $this->assertDatabaseMissing('redirects', ['url' => $url]);
    }

    public function test_redirect_store_empty_query_param_url()
    {
        $api_url = 'http://localhost:8000/api';

        $url = 'https://google.com?teste=';

        $response = $this->post($api_url . '/redirects', ['url' => $url]);

        $response->assertStatus(400);
        $this->assertDatabaseMissing('redirects', ['url' => $url]);
    }
}
