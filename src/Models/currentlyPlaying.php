<?php

namespace SpotifyMusicApi\Models;

class currentlyPlaying
{
    public bool $isPlaying;
    public string $trackName;
    public array $artists;
    public string $albumName;
    public string $albumArtUrl;
    public int $progressMs;
    public int $durationMs;
    public string $spotifyUrl;
    public array $rawData;

    public function __construct(array $data)
    {
        $this->rawData = $data;
        $this->isPlaying = $data['is_playing'] ?? false;

        $item = $data['item'] ?? null;

        if ($item) {
            $this->trackName = $item['name'] ?? '';
            $this->albumName = $item['album']['name'] ?? '';
            $this->albumArtUrl = $item['album']['images'][0]['url'] ?? '';
            $this->progressMs = $data['progress_ms'] ?? 0;
            $this->durationMs = $item['duration_ms'] ?? 0;
            $this->spotifyUrl = $item['external_urls']['spotify'] ?? '';

            $this->artists = array_map(
                fn($artist) => $artist['name'],
                $item['artists'] ?? []
            );
        } else {
            $this->trackName = '';
            $this->albumName = '';
            $this->albumArtUrl = '';
            $this->progressMs = 0;
            $this->durationMs = 0;
            $this->spotifyUrl = '';
            $this->artists = [];
        }
    }

    /**
     * Get artist names joined as a comma-separated string.
     */
    public function getArtistString(): string
    {
        return implode(', ', $this->artists);
    }
}
