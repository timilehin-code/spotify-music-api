<?php

namespace SpotifyMusicApi\Tests;

use PHPUnit\Framework\TestCase;
use SpotifyMusicApi\Clients\HttpClientInterface;
use SpotifyMusicApi\Endpoints\player;
use SpotifyMusicApi\Models\currentlyPlaying;

class PlayerTest extends TestCase
{
    public function testGetCurrentlyPlayingReturnsModelOnValidData(): void
    {
        // Mock payload matching Spotify API currently-playing response
        $mockResponse = [
            'is_playing' => true,
            'progress_ms' => 120000,
            'item' => [
                'name' => 'Mock Song',
                'duration_ms' => 200000,
                'album' => [
                    'name' => 'Mock Album',
                    'images' => [
                        ['url' => 'https://example.com/cover.jpg']
                    ]
                ],
                'artists' => [
                    ['name' => 'Artist One'],
                    ['name' => 'Artist Two']
                ],
                'external_urls' => [
                    'spotify' => 'https://open.spotify.com/track/12345'
                ]
            ]
        ];

        // Mock HTTP Client
        $clientMock = $this->createMock(HttpClientInterface::class);
        $clientMock->expects($this->once())
            ->method('get')
            ->with('me/player/currently-playing', [])
            ->willReturn($mockResponse);

        $player = new player($clientMock);
        $result = $player->getCurrentlyPlaying();

        $this->assertInstanceOf(currentlyPlaying::class, $result);
        $this->assertTrue($result->isPlaying);
        $this->assertEquals('Mock Song', $result->trackName);
        $this->assertEquals('Artist One, Artist Two', $result->getArtistString());
        $this->assertEquals('Mock Album', $result->albumName);
    }

    public function testGetCurrentlyPlayingReturnsNullWhenNothingIsPlaying(): void
    {
        $clientMock = $this->createMock(HttpClientInterface::class);
        $clientMock->expects($this->once())
            ->method('get')
            ->with('me/player/currently-playing', [])
            ->willReturn([]);

        $player = new player($clientMock);
        $result = $player->getCurrentlyPlaying();

        $this->assertNull($result);
    }
}
