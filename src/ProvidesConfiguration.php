<?php

namespace Etdte\SvcHttp;

interface ProvidesConfiguration
{
    /**
     * Determine is configuration settled
     *
     * @return bool
     */
    public function settled(): bool;

    /**
     * Get base URL for requests
     *
     * @param string|null $path
     *
     * @return string
     */
    public function baseUrl(string $path = null): string;

    /**
     * Get URL for most common requests
     *
     * @param string|null $path
     *
     * @return string
     */
    public function url(string $path = null): string;

    /**
     * Get bearer token for requests
     *
     * @return string|null
     */
    public function token(): ?string;
}
