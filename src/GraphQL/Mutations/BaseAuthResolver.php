<?php

namespace Zedu\IwsGraphqlLogin\GraphQL\Mutations;

use Illuminate\Http\Request;
use Zedu\IwsGraphqlLogin\Contracts\AuthModelFactory;
use Zedu\IwsGraphqlLogin\Exceptions\AuthenticationException;

/**
 * Class BaseAuthResolver.
 */
class BaseAuthResolver
{
    /**
     * @param array  $args
     * @param string $grantType
     *
     * @return mixed
     */
    public function buildCredentials(array $args = [], $grantType = 'password')
    {
        $args = collect($args);
        $credentials = $args->except('directive')->toArray();
        $credentials['client_id'] = $args->get('client_id', config('iws-graphql-login.client_id'));
        $credentials['client_secret'] = $args->get('client_secret', config('iws-graphql-login.client_secret'));
        $credentials['grant_type'] = $grantType;

        return $credentials;
    }

    /**
     * @param array $credentials
     *
     * @throws AuthenticationException
     *
     * @return mixed
     */
    public function makeRequest(array $credentials)
    {
        $request = Request::create('oauth/token', 'POST', $credentials, [], [], [
            'HTTP_Accept' => 'application/json',
        ]);
        $response = app()->handle($request);
        $decodedResponse = json_decode($response->getContent(), true);
        if ($response->getStatusCode() != 200) {
            throw new AuthenticationException(__('Authentication exception'), __('Incorrect username or password'));
        }

        return $decodedResponse;
    }

    protected function getAuthModelFactory(): AuthModelFactory
    {
        return app(AuthModelFactory::class);
    }
}