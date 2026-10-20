<?php

namespace SpotifyMusicApi\Exceptions;

use Throwable;

class RateLimitException extends SpotifyApiException
{
    private int $retryAfter;

    public function __construct(string $message, int $retryAfter = 0, int $code = 429, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->retryAfter = $retryAfter;
    }

    /**
     * Get the number of seconds Spotify requests you wait before retrying.
     */
    public function getRetryAfter(): int
    {
        return $this->retryAfter;
    }
}