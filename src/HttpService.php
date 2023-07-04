<?php

namespace Etdte\SvcHttp;

use Illuminate\Support\Facades\Http;

abstract class HttpService
{
    /**
     * Configuration object
     *
     * @var \Etdte\SvcHttp\ProvidesConfiguration|null
     */
    protected ProvidesConfiguration | null $configuration;

    /**
     * HttpService constructor.
     *
     * @param \Etdte\SvcHttp\ProvidesConfiguration|null $configuration
     *
     */
    public function __construct(ProvidesConfiguration | null $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * Make request to http service
     *
     * @param string $method
     * @param string $path
     * @param array  $attributes
     *
     * @throws \Etdte\SvcHttp\ConfigurationException
     *
     * @return array|null
     */
    private function request(string $method = 'GET', string $path = '/', array $attributes = []): ?array
    {
        if ($this->hasConfiguration() && ! $this->configuration->settled()) {
            ConfigurationException::notSettled();
        }

        /** @var \Illuminate\Http\Client\Response $response */
        $response = Http::withToken($this->configuration->token(), $this->configuration->tokenType ?? 'Bearer')
            ->{$method}($path, $attributes)
            ->throw();

        return $response->json() ?? ['result' => $response->body()];
    }

    /**
     * Alias for GET calls
     *
     * @param string $path
     * @param array  $attributes
     *
     * @throws \Etdte\SvcHttp\ConfigurationException
     *
     * @return array|null
     */
    protected function get(string $path, array $attributes = []): ?array
    {
        return $this->request('get', $path, $attributes);
    }

    /**
     * Alias for POST calls
     *
     * @param string $path
     * @param array  $attributes
     *
     * @throws \Etdte\SvcHttp\ConfigurationException
     *
     * @return array|null
     */
    protected function post(string $path, array $attributes = []): ?array
    {
        return $this->request('post', $path, $attributes);
    }

    /**
     * Alias for PUT/PATCH calls
     *
     * @param string $path
     * @param array  $attributes
     *
     * @throws \Etdte\SvcHttp\ConfigurationException
     *
     * @return array|null
     */
    protected function put(string $path, array $attributes = []): ?array
    {
        return $this->request('put', $path, $attributes);
    }

    /**
     * Alias for DELETE calls
     *
     * @param string $path
     *
     * @throws \Etdte\SvcHttp\ConfigurationException
     *
     * @return array|null
     */
    protected function delete(string $path): ?array
    {
        return $this->request('delete', $path);
    }

    /**
     * Determines when service has configuration
     *
     * @return bool
     */
    protected function hasConfiguration(): bool
    {
        return $this->configuration !== null;
    }
}
