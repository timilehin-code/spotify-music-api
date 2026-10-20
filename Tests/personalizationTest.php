<?php

namespace SpotifyMusicApi\Tests;

use PHPUnit\Framework\TestCase;
use SpotifyMusicApi\Clients\HttpClientInterface;
use SpotifyMusicApi\Endpoints\personalization;
use SpotifyMusicApi\Models\topArtist;
use SpotifyMusicApi\Models\topTrack;

class PersonalizationTest extends TestCase
{
    public function testGetTopArtistsReturnsArrayOfTopArtistModels(): void
    {
        $mockResponse = [
            'items' => [
                [
                    'id' => 'artist1',
                    'name' => 'Eminem',
                    'genres' => ['detroit hip hop', 'hip hop'],
                    'popularity' => 90,
                    'followers' => ['total' => 80000000],
                    'images' => [['url' => 'https://example.com/artist.jpg']],
                    'external_urls' => ['spotify' => 'https://open.spotify.com/artist/1']
                ]
            ]
        ];

        $clientMock = $this->createMock(HttpClientInterface::class);
        $clientMock->expects($this->once())
            ->method('get')
            ->with('me/top/artists', [
                'time_range' => 'medium_term',
                'limit' => 5,
                'offset' => 0
            ])
            ->willReturn($mockResponse);

        $endpoint = new personalization($clientMock);
        $artists = $endpoint->getTopArtists(limit: 5);

        $this->assertCount(1, $artists);
        $this->assertInstanceOf(topArtist::class, $artists[0]);
        $this->assertEquals('Eminem', $artists[0]->name);
        $this->assertEquals('Detroit Hip Hop, Hip Hop', $artists[0]->getGenreString());
    }

    public function testGetTopTracksAndOnRepeatReturnArrayOfTopTrackModels(): void
    {
        $mockResponse = [
            'items' => [
                [
                    'id' => 'track1',
                    'name' => 'Venom',
                    'duration_ms' => 269000,
                    'popularity' => 85,
                    'album' => [
                        'name' => 'Kamikaze',
                        'images' => [['url' => 'https://example.com/album.jpg']]
                    ],
                    'artists' => [['name' => 'Eminem']],
                    'external_urls' => ['spotify' => 'https://open.spotify.com/track/1']
                ]
            ]
        ];

        $clientMock = $this->createMock(HttpClientInterface::class);
        $clientMock->expects($this->once())
            ->method('get')
            ->with('me/top/tracks', [
                'time_range' => 'short_term',
                'limit' => 5,
                'offset' => 0
            ])
            ->willReturn($mockResponse);

        $endpoint = new personalization($clientMock);
        $onRepeat = $endpoint->getOnRepeat(limit: 5);

        $this->assertCount(1, $onRepeat);
        $this->assertInstanceOf(topTrack::class, $onRepeat[0]);
        $this->assertEquals('Venom', $onRepeat[0]->name);
        $this->assertEquals('Eminem', $onRepeat[0]->getArtistString());
        $this->assertEquals('Kamikaze', $onRepeat[0]->albumName);
    }
}
