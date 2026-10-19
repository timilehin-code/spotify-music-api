<?php

namespace SpotifyMusicApi\Auth;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;
use RuntimeException;

class TokenAuthentication implements AuthenticatorInterface
{
    private string $clientId;
    private string $clientSecret;
    private string $refreshToken;
    private ?string $accessToken;
    private ?int $expiresAt;
    private ClientInterface $httpClient;

    public function __construct(
        string $clientId,
        string $clientSecret,
        string $refreshToken,
        ?string $accessToken = null,
        ?int $expiresAt = null,
        ?ClientInterface $httpClient = null
    ) {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->refreshToken = $refreshToken;
        $this->accessToken = $accessToken;
        $this->expiresAt = $expiresAt;
        $this->httpClient = $httpClient ?? new GuzzleClient();
    }

    /**
     * Retrieves the active access token, refreshing it automatically if expired or missing.
     * 
     *  @return  string
     */
    public function getAccessToken(): string
    {
        if ($this->isAccessTokenExpired()) {
            return $this->refreshAccessToken();
        }

        return $this->accessToken;
    }

    /**
     * Exchanges the refresh token for a new access token at Spotify's token endpoint.
     * 
     * 
     * @return  string
     */
    public function refreshAccessToken(): string
    {
        $credentials = base64_encode("{$this->clientId}:{$this->clientSecret}");

        try {
            $response = $this->httpClient->request('POST', 'https://accounts.spotify.com/api/token', [
                'headers' => [
                    'Authorization' => 'Basic ' . $credentials,
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'form_params' => [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $this->refreshToken,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if (!isset($data['access_token'])) {
                throw new RuntimeException('Failed to retrieve access token from Spotify response.');
            }

            $this->accessToken = $data['access_token'];
            // Buffer expiration time by 60 seconds to prevent edge-case failures
            $this->expiresAt = time() + ($data['expires_in'] ?? 3600) - 60;

            // Spotify may occasionally return a new refresh token; store it if present
            if (isset($data['refresh_token'])) {
                $this->refreshToken = $data['refresh_token'];
            }

            return $this->accessToken;
        } catch (\Throwable $e) {
            throw new RuntimeException("Spotify Token Refresh Error: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Checks if the current access token is missing or past its expiration timestamp.
     * 
     *  @return bool
     */
    private function isAccessTokenExpired(): bool
    {
        if ($this->accessToken === null || $this->expiresAt === null) {
            return true;
        }

        return time() >= $this->expiresAt;
    }

    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    public function getExpiresAt(): ?int
    {
        return $this->expiresAt;
    }
}
