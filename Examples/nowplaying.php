<?php

require_once __DIR__ . '/../vendor/autoload.php';

use SpotifyMusicApi\Auth\TokenAuthentication;
use SpotifyMusicApi\SpotifyClient;

// Replace with your Spotify Developer App credentials and user refresh token
$clientId     = getenv('SPOTIFY_CLIENT_ID')     ?: 'YOUR_SPOTIFY_CLIENT_ID';
$clientSecret = getenv('SPOTIFY_CLIENT_SECRET') ?: 'YOUR_SPOTIFY_CLIENT_SECRET';
$refreshToken = getenv('SPOTIFY_REFRESH_TOKEN') ?: 'YOUR_SPOTIFY_REFRESH_TOKEN';

try {
    // 1. Initialize Token Authentication
    $auth = new TokenAuthentication(
        clientId: $clientId,
        clientSecret: $clientSecret,
        refreshToken: $refreshToken
    );

    // 2. Instantiate Main Client
    $spotify = new SpotifyClient($auth);

    // 3. Fetch Currently Playing Track
    $nowPlaying = $spotify->player()->getCurrentlyPlaying();

    // 4. Handle Case When User Is Not Playing Music
    if ($nowPlaying === null) {
        echo "No track is currently playing on Spotify.\n";
        exit;
    }

    // 5. Output Metadata
    echo "=== Spotify Now Playing ===\n";
    echo "Status:      " . ($nowPlaying->isPlaying ? "Playing" : "Paused") . "\n";
    echo "Track:       " . $nowPlaying->trackName . "\n";
    echo "Artist(s):   " . $nowPlaying->getArtistString() . "\n";
    echo "Album:        " . $nowPlaying->albumName . "\n";
    echo "Album Art:   " . $nowPlaying->albumArtUrl . "\n";
    echo "Progress:    " . round($nowPlaying->progressMs / 1000) . "s / " . round($nowPlaying->durationMs / 1000) . "s\n";
    echo "Spotify URL: " . $nowPlaying->spotifyUrl . "\n";
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
