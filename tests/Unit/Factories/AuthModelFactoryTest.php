<?php

namespace Zedu\IwsGraphqlLogin\Tests\Unit\Factories;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Zedu\IwsGraphqlLogin\Contracts\AuthModelFactory;
use Zedu\IwsGraphqlLogin\Tests\TestCase;
use Zedu\IwsGraphqlLogin\Tests\User;

class AuthModelFactoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var AuthModelFactory
     */
    private $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = $this->app->make(AuthModelFactory::class);
    }

    /**
     * @test
     */
    public function canMakeAuthModel(): void
    {
        $email = 'edu@example.com';
        $model = $this->factory->make(['email' => $email]);

        self::assertInstanceOf(User::class, $model);
        self::assertEquals($email, $model->email);
    }

    /**
     * @test
     */
    public function canCreateAuthModel(): void
    {
        $model = $this->factory->create([
            'name'     => 'Edu Flores',
            'email'    => 'edu@example.com',
            'password' => Hash::make('12345678'),
        ]);

        self::assertInstanceOf(User::class, $model);
        self::assertDatabaseCount($model->getTable(), 1);
    }

    /**
     * @test
     */
    public function canGetAuthModelClass(): void
    {
        self::assertEquals($this->factory->getClass(), User::class);
    }
}
