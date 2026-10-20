<?php

namespace SpotifyMusicApi\Clients;

use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\ClientInterface as GuzzleInterface;
use SpotifyMusicApi\Auth\AuthenticatorInterface;
use RuntimeException;

class GuzzleHttpClient implements HttpClientInterface
{
    private AuthenticatorInterface $authenticator;
    private GuzzleInterface $client;
    private string $baseUrl = 'https://api.spotify.com/v1/';

    public function __construct(
        AuthenticatorInterface $authenticator,
        ?GuzzleInterface $client = null
    ) {
        $this->authenticator = $authenticator;
        $this->client = $client ?? new Guzzle();
    }

    /**
     * Executes a GET request against Spotify API.
     */
    public function get(string $endpoint, array $query = []): array
    {
        return $this->request('GET', $endpoint, ['query' => $query]);
    }

    /**
     * Executes a POST request against Spotify API.
     */
    public function post(string $endpoint, array $body = []): array
    {
        return $this->request('POST', $endpoint, ['json' => $body]);
    }

    /**
     * Formats headers with Bearer token, issues HTTP call, and handles API response states.
     */
    private function request(string $method, string $endpoint, array $options = []): array
    {
        $token = $this->authenticator->getAccessToken();

        $options['headers'] = array_merge(
            $options['headers'] ?? [],
            [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ]
        );

        $url = $this->baseUrl . ltrim($endpoint, '/');

        try {
            $response = $this->client->request($method, $url, $options);
            $statusCode = $response->getStatusCode();

            // Spotify returns HTTP 204 No Content when no track is currently playing
            if ($statusCode === 204) {
                return [];
            }

            $contents = $response->getBody()->getContents();
            return json_decode($contents, true) ?? [];
        } catch (\Throwable $e) {
            throw new RuntimeException("Spotify API Request Failed [{$method} {$endpoint}]: " . $e->getMessage(), 0, $e);
        }
    }
}
