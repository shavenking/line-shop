<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_application_returns_a_successful_response()
    {
        $response = $this->get('/');

        $response->assertOk();
    }

    public function test_home_contains_liff_sdk_script()
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('https://static.line-scdn.net/liff/edge/2/sdk.js', false);
    }
}
