<?php

namespace SpotifyMusicApi\Endpoints;

use SpotifyMusicApi\Models\currentlyPlaying;

class player extends AbstractEndpoint
{
    /**
     * Get the user's currently playing track.
     *
     * @param string $market Optional ISO 3166-1 alpha-2 country code
     * @return currentlyPlaying|null Returns null if no track is playing
     */
    public function getCurrentlyPlaying(string $market = ''): ?currentlyPlaying
    {
        $params = [];
        if (!empty($market)) {
            $params['market'] = $market;
        }

        $response = $this->client->get('me/player/currently-playing', $params);

        if (empty($response) || empty($response['item'])) {
            return null;
        }

        return new currentlyPlaying($response);
    }
}
