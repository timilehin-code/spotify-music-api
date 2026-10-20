<?php

namespace SpotifyMusicApi\Endpoints;

use SpotifyMusicApi\Clients\HttpClientInterface;

abstract class AbstractEndpoint
{
    protected HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }
}
