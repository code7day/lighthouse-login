<?php

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Facades\Hash;
use Zedu\IwsGraphqlLogin\Tests\Admin;

app(Factory::class)->define(Admin::class, function (Faker $faker) {
    static $password;

    if (!$password) {
        $password = Hash::make('12345678');
    }

    return [
        'name'     => 'Edu Flores',
        'email'    => 'edu@example.com',
        'password' => $password,
    ];
});
