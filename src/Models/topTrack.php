<?php

namespace SpotifyMusicApi\Models;

class topTrack
{
    public string $id;
    public string $name;
    public array $artists;
    public string $albumName;
    public string $albumArtUrl;
    public int $durationMs;
    public int $popularity;
    public string $spotifyUrl;
    public array $rawData;

    public function __construct(array $data)
    {
        $this->rawData = $data;
        $this->id = $data['id'] ?? '';
        $this->name = $data['name'] ?? '';
        $this->albumName = $data['album']['name'] ?? '';
        $this->albumArtUrl = $data['album']['images'][0]['url'] ?? '';
        $this->durationMs = $data['duration_ms'] ?? 0;
        $this->popularity = $data['popularity'] ?? 0;
        $this->spotifyUrl = $data['external_urls']['spotify'] ?? '';

        $this->artists = array_map(
            fn($artist) => $artist['name'],
            $data['artists'] ?? []
        );
    }

    /**
     * Get artist names joined as a comma-separated string.
     */
    public function getArtistString(): string
    {
        return implode(', ', $this->artists);
    }
}
