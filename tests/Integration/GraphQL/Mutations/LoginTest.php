<?php

namespace Zedu\IwsGraphqlLogin\Tests\Integration\GraphQL\Mutations;

use Zedu\IwsGraphqlLogin\Tests\Admin;
use Zedu\IwsGraphqlLogin\Tests\TestCase;
use Zedu\IwsGraphqlLogin\Tests\User;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;

class LoginTest extends TestCase
{
    use MakesGraphQLRequests;

    public function dataProvider(): array
    {
        return [
            'default'                    => [
                User::class,
                [
                    'username' => 'edu@example.com',
                    'password' => '12345678',
                ],
            ],
            'findForPassport' => [
                Admin::class,
                [
                    'username' => 'Edu Flores',
                    'password' => '12345678',
                ],
                true,

            ],
        ];
    }

    /**
     * @dataProvider dataProvider
     */
    public function test_it_gets_access_token(string $modelClass, array $credentials, bool $hasFindForPassportMethod = false)
    {
        $this->app['config']->set('auth.providers.users.model', $modelClass);

        $this->createClient();

        factory($modelClass)->create();

        if ($hasFindForPassportMethod) {
            self::assertTrue(method_exists($modelClass, 'findForPassport'));
        }

        $response = $this->graphQL(
            /** @lang GraphQL */
            '
            mutation Login($input: LoginInput) {
                login(input: $input) {
                    access_token
                    refresh_token
                    user {
                        id
                        name
                        email
                    }
                }
            }
        ',
            [
                'input' => $credentials,
            ]
        );

        $response->assertJsonStructure([
            'data' => [
                'login' => [
                    'access_token',
                    'refresh_token',
                    'user' => [
                        'id',
                        'name',
                        'email',
                    ],
                ],
            ],
        ]);
    }
}