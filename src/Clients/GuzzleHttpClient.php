<?php

namespace SpotifyMusicApi\Clients;

use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\ClientInterface as GuzzleInterface;
use GuzzleHttp\Exception\BadResponseException;
use SpotifyMusicApi\Auth\AuthenticatorInterface;
use SpotifyMusicApi\Exceptions\AuthenticationException;
use SpotifyMusicApi\Exceptions\RateLimitException;
use SpotifyMusicApi\Exceptions\SpotifyApiException;

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

    public function get(string $endpoint, array $query = []): array
    {
        return $this->request('GET', $endpoint, ['query' => $query]);
    }

    public function post(string $endpoint, array $body = []): array
    {
        return $this->request('POST', $endpoint, ['json' => $body]);
    }

    private function request(string $method, string $endpoint, array $options = []): array
    {
        $token = $this->authenticator->getAccessToken();

        $options['headers'] = array_merge(
            $options['headers'] ?? [],
            [
                'Authorization' => 'Bearer ' . $token,
                'Accept'        => 'application/json',
            ]
        );

        $url = $this->baseUrl . ltrim($endpoint, '/');

        try {
            $response = $this->client->request($method, $url, $options);
            $statusCode = $response->getStatusCode();

            if ($statusCode === 204) {
                return [];
            }

            return json_decode($response->getBody()->getContents(), true) ?? [];

        } catch (BadResponseException $e) {
            $response = $e->getResponse();
            $statusCode = $response ? $response->getStatusCode() : 0;

            if ($statusCode === 401) {
                throw new AuthenticationException("Spotify Unauthorized (401): Check token permissions.", 401, $e);
            }

            if ($statusCode === 429) {
                $retryAfter = (int) ($response->getHeaderLine('Retry-After') ?: 0);
                throw new RateLimitException("Spotify Rate Limit Exceeded (429).", $retryAfter, 429, $e);
            }

            throw new SpotifyApiException("Spotify API Error [{$statusCode}]: " . $e->getMessage(), $statusCode, $e);

        } catch (\Throwable $e) {
            throw new SpotifyApiException("Spotify Request Error: " . $e->getMessage(), 0, $e);
        }
    }
}