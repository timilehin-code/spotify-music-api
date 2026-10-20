<?php

namespace SpotifyMusicApi\Models;

class topArtist
{
    public string $id;
    public string $name;
    public array $genres;
    public int $popularity;
    public int $followers;
    public string $imageUrl;
    public string $spotifyUrl;
    public array $rawData;

    public function __construct(array $data)
    {
        $this->rawData = $data;
        $this->id = $data['id'] ?? '';
        $this->name = $data['name'] ?? '';
        $this->genres = $data['genres'] ?? [];
        $this->popularity = $data['popularity'] ?? 0;
        $this->followers = $data['followers']['total'] ?? 0;
        $this->imageUrl = $data['images'][0]['url'] ?? '';
        $this->spotifyUrl = $data['external_urls']['spotify'] ?? '';
    }

    /**
     * Get genres formatted as a capitalized, comma-separated string.
     */
    public function getGenreString(): string
    {
        return implode(', ', array_map('ucwords', $this->genres));
    }
}
