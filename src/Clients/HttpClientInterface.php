<?php

namespace SpotifyMusicApi\Clients;

interface HttpClientInterface
{
    /**
     * Send a GET request to the Spotify API.
     *
     * @param string $endpoint Relative API endpoint (e.g., 'me/player/currently-playing')
     * @param array $query Optional query parameters
     * @return array Decoded JSON response
     */
    public function get(string $endpoint, array $query = []): array;

    /**
     * Send a POST request to the Spotify API.
     *
     * @param string $endpoint Relative API endpoint
     * @param array $body Request body array
     * @return array Decoded JSON response
     */
    public function post(string $endpoint, array $body = []): array;
}