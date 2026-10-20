<?php

namespace SpotifyMusicApi;

use SpotifyMusicApi\Auth\AuthenticatorInterface;
use SpotifyMusicApi\Clients\GuzzleHttpClient;
use SpotifyMusicApi\Clients\HttpClientInterface;
use SpotifyMusicApi\Endpoints\player;
use SpotifyMusicApi\Endpoints\personalization;

class SpotifyClient
{
    private HttpClientInterface $httpClient;
    private ?player $player = null;
    private ?personalization $personalization = null;

    /**
     * Pass either an Authenticator instance or a custom HttpClient implementation.
     */
    public function __construct(AuthenticatorInterface|HttpClientInterface $authOrClient)
    {
        if ($authOrClient instanceof HttpClientInterface) {
            $this->httpClient = $authOrClient;
        } else {
            $this->httpClient = new GuzzleHttpClient($authOrClient);
        }
    }

    /**
     * Access Spotify Player endpoints (e.g., currently playing).
     */
    public function player(): player
    {
        if ($this->player === null) {
            $this->player = new player($this->httpClient);
        }

        return $this->player;
    }

    /**
     * Access Spotify Personalization endpoints (e.g., top artists, top tracks).
     */
    public function personalization(): personalization
    {
        if ($this->personalization === null) {
            $this->personalization = new personalization($this->httpClient);
        }

        return $this->personalization;
    }

    /**
     * Get the configured HTTP client instance.
     */
    public function getHttpClient(): HttpClientInterface
    {
        return $this->httpClient;
    }
}