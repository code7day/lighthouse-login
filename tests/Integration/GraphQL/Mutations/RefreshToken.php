<?php

namespace Zedu\IwsGraphqlLogin\Tests\Integration\GraphQL\Mutations;

use Zedu\IwsGraphqlLogin\Tests\TestCase;
use Zedu\IwsGraphqlLogin\Tests\User;

class RefreshToken extends TestCase
{
    public function test_it_refresh_a_token()
    {
        $this->createClient();
        factory(User::class)->create();
        $response = $this->postGraphQL([
            'query' => 'mutation {
                login(input: {
                    username: "edu@example.com",
                    password: "12345678"
                }) {
                    access_token
                    refresh_token
                }
            }',
        ]);
        $responseBody = json_decode($response->getContent(), true);
        $responseRefreshed = $this->postGraphQL([
            'query' => 'mutation {
                refreshToken(input: {
                    refresh_token: "' . $responseBody['data']['login']['refresh_token'] . '"
                }) {
                    access_token
                    refresh_token
                }
            }',
        ]);
        $responseBodyRefreshed = json_decode($responseRefreshed->getContent(), true);
        $this->assertNotEquals($responseBody['data']['login']['access_token'], $responseBodyRefreshed['data']['refreshToken']['access_token']);
    }
}
