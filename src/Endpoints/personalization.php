<?php

namespace SpotifyMusicApi\Endpoints;

use SpotifyMusicApi\Models\topArtist;
use SpotifyMusicApi\Models\topTrack;

class personalization extends AbstractEndpoint
{
    /**
     * Get user's top artists mapped to topArtist DTO objects.
     *
     * @param string $timeRange 'short_term' (~4 weeks), 'medium_term' (~6 months), 'long_term' (years)
     * @param int $limit Max items to return (1 to 50)
     * @param int $offset Index offset
     * @return topArtist[]
     */
    public function getTopArtists(string $timeRange = 'medium_term', int $limit = 20, int $offset = 0): array
    {
        $response = $this->getTopItems('artists', $timeRange, $limit, $offset);

        if (empty($response['items'])) {
            return [];
        }

        return array_map(
            fn(array $item) => new topArtist($item),
            $response['items']
        );
    }

    /**
     * Get user's top tracks mapped to topTrack DTO objects.
     *
     * @param string $timeRange 'short_term' (~4 weeks), 'medium_term' (~6 months), 'long_term' (years)
     * @param int $limit Max items to return (1 to 50)
     * @param int $offset Index offset
     * @return topTrack[]
     */
    public function getTopTracks(string $timeRange = 'medium_term', int $limit = 20, int $offset = 0): array
    {
        $response = $this->getTopItems('tracks', $timeRange, $limit, $offset);

        if (empty($response['items'])) {
            return [];
        }

        return array_map(
            fn(array $item) => new topTrack($item),
            $response['items']
        );
    }

    /**
     * Get user's "On Repeat" tracks (short-term top tracks over the last ~4 weeks).
     *
     * @param int $limit Max items to return (1 to 50)
     * @return topTrack[]
     */
    public function getOnRepeat(int $limit = 20): array
    {
        return $this->getTopTracks(timeRange: 'short_term', limit: $limit);
    }

    /**
     * Internal query helper for GET /v1/me/top/{type}
     */
    private function getTopItems(string $type, string $timeRange, int $limit, int $offset): array
    {
        $params = [
            'time_range' => $timeRange,
            'limit'      => min(max($limit, 1), 50),
            'offset'     => max($offset, 0),
        ];

        return $this->client->get("me/top/{$type}", $params);
    }
}
