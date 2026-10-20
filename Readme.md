# Spotify Music API PHP SDK

A very simple and easy-to-use PHP package to connect to the Spotify API. This package allows you to easily get a user's **Currently Playing** song, **Top Artists**, and **Top Tracks** without dealing with complex API requests.

---

## 🛠️ Requirements
* **PHP 8.0** or higher
* **Composer** installed on your computer
* A **Spotify Developer Account**

---

## 🚀 Step 1: Install the Package

Run this command in your terminal (inside your project folder) to install the package:

```bash
composer require oluwatimilehin/spotify-music-api
```

---

## 🔑 Step 2: Create a Spotify App (Important!)

Before you write any code, you need to get your API keys from Spotify.

1. Go to the [Spotify Developer Dashboard](https://developer.spotify.com/dashboard) and log in.
2. Click **Create App**. Give it a name and description.
3. **Redirect URI (CRITICAL RULE):** You must enter a Redirect URI. 
   * ⚠️ **DO NOT USE `localhost`**. Spotify's security system can sometimes block or fail to recognize the word `localhost`.
   * ✅ **USE YOUR IP ADDRESS INSTEAD**. 
   * For this guide, put exactly this in the Redirect URI box: `http://127.0.0.1:8000/get-token.php`
4. Go to **Settings** and copy your **Client ID** and **Client Secret**. Keep these safe!

---

## 🎟️ Step 3: Get Your Refresh Token (One-Time Setup)

To read your personal Spotify data, you need a **Refresh Token**. You only need to do this step **once**. 

1. Create a new file in your project folder called `get-token.php`.
2. Paste this code into it and replace your Client ID and Client Secret:

```php
<?php
// get-token.php

$clientId     = 'PUT_YOUR_CLIENT_ID_HERE';
$clientSecret = 'PUT_YOUR_CLIENT_SECRET_HERE';
$redirectUri  = 'http://127.0.0.1:8000/get-token.php'; // Must match your Spotify dashboard exactly!

// Part 2: Spotify sends the user back here with a "code"
if (isset($_GET['code'])) {
    $ch = curl_init('https://accounts.spotify.com/api/token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'grant_type'   => 'authorization_code',
        'code'         => $_GET['code'],
        'redirect_uri' => $redirectUri,
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Basic ' . base64_encode($clientId . ':' . $clientSecret)
    ]);
    
    $response = json_decode(curl_exec($ch), true);
    
    echo "<h2>Success! Here is your Refresh Token:</h2>";
    echo "<textarea rows='5' cols='60'>" . $response['refresh_token'] . "</textarea>";
    echo "<p>Copy this token and paste it into your api.php file. You can delete this file now!</p>";
    exit;
}

// Part 1: Show a login link
$scopes = 'user-read-currently-playing user-read-playback-state user-top-read';
$loginUrl = "https://accounts.spotify.com/authorize?client_id={$clientId}&response_type=code&redirect_uri={$redirectUri}&scope={$scopes}";

echo "<h2>Step 1: Connect your Spotify Account</h2>";
echo "<a href='{$loginUrl}' style='padding: 10px 20px; background: #1DB954; color: white; text-decoration: none; border-radius: 50px;'>Log in with Spotify</a>";
```

3. Open your terminal in your project folder and start your server:
   ```bash
   php -S 127.0.0.1:8000
   ```
4. Open your browser and go to `http://127.0.0.1:8000/get-token.php`.
5. Click the green button, log in to Spotify, and agree to the permissions.
6. **Copy the Refresh Token** that appears on the screen.

---

## ⚙️ Step 4: Create an API Endpoint (`api.php`)

To pass data safely to a frontend webpage, we need a PHP file that gets the data from Spotify using our new package and outputs it as JSON data. 

Create a file named `api.php` in your project folder:

```php
<?php
// api.php

require_once __DIR__ . '/vendor/autoload.php';

use SpotifyMusicApi\Auth\TokenAuthentication;
use SpotifyMusicApi\SpotifyClient;

// Tell the browser this file returns JSON data
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// 1. Put your Spotify keys here
$clientId     = 'YOUR_SPOTIFY_CLIENT_ID';
$clientSecret = 'YOUR_SPOTIFY_CLIENT_SECRET';
$refreshToken = 'PASTE_THE_REFRESH_TOKEN_YOU_JUST_COPIED_HERE';

try {
    // 2. Start the Authentication
    $auth = new TokenAuthentication($clientId, $clientSecret, $refreshToken);

    // 3. Start the Spotify Client
    $spotify = new SpotifyClient($auth);

    // 4. Get the data
    $nowPlaying = $spotify->player()->getCurrentlyPlaying();
    $topArtists = $spotify->personalization()->getTopArtists('medium_term', 5);

    // 5. Send data back to our HTML as JSON
    echo json_encode([
        'status' => 'success',
        'now_playing' => $nowPlaying,
        'top_artists' => $topArtists
    ]);

} catch (\Exception $e) {
    // If there is an error, send the error message
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
```

---

## 🎨 Step 5: Displaying the Data (Frontend)

Now that we have `api.php` sending JSON data, you can use basic HTML, CSS, and JavaScript to display it nicely. 

Create a file named `index.html` and paste the following code into it. Then, visit `http://127.0.0.1:8000/index.html` in your browser to see your live Spotify stats!

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Spotify Stats</title>
    
    <style>
        body {
            background-color: #121212; 
            color: #FFFFFF; 
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .container {
            max-width: 600px;
            width: 100%;
        }

        h1 { color: #1DB954; text-align: center; }

        .card {
            background-color: #181818;
            border-radius: 10px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
        }

        .card img {
            width: 100px;
            height: 100px;
            border-radius: 5px;
            object-fit: cover;
        }

        .status {
            background-color: #1DB954;
            color: black;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }

        .artist-item {
            background-color: #181818;
            margin-bottom: 10px;
            padding: 15px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>My Spotify Stats</h1>
        <p id="loading" style="text-align:center; color:#1DB954;">Connecting to Spotify...</p>

        <!-- Now Playing Box -->
        <div id="now-playing-container" style="display: none;">
            <h2>Now Playing</h2>
            <div class="card">
                <img id="album-art" src="" alt="Album Art">
                <div>
                    <span id="play-status" class="status">Playing</span>
                    <h3 id="track-name" style="margin: 5px 0;">Song Name</h3>
                    <p id="artist-name" style="margin: 0; color: #B3B3B3;">Artist Name</p>
                </div>
            </div>
        </div>

        <!-- Top Artists Box -->
        <div id="top-artists-container" style="display: none;">
            <h2>My Top Artists</h2>
            <div id="artist-list"></div>
        </div>
    </div>

    <script>
        // Go fetch the data from our PHP file as soon as the page loads
        document.addEventListener("DOMContentLoaded", async function() {
            try {
                // Call the api.php file
                const response = await fetch('api.php');
                const data = await response.json();

                // Hide loading text
                document.getElementById('loading').style.display = 'none';

                if (data.status === 'error') {
                    alert("Error: " + data.message);
                    return;
                }

                // 1. Show Now Playing Data
                if (data.now_playing) {
                    document.getElementById('track-name').textContent = data.now_playing.trackName;
                    
                    // Join multiple artists with a comma
                    document.getElementById('artist-name').textContent = Array.isArray(data.now_playing.artists) 
                        ? data.now_playing.artists.join(', ') 
                        : 'Unknown Artist';
                        
                    document.getElementById('album-art').src = data.now_playing.albumArtUrl;
                    
                    const statusBadge = document.getElementById('play-status');
                    statusBadge.textContent = data.now_playing.isPlaying ? "Playing 🟢" : "Paused ⏸️";
                    statusBadge.style.backgroundColor = data.now_playing.isPlaying ? "#1DB954" : "#ffcc00";

                    document.getElementById('now-playing-container').style.display = 'block';
                }

                // 2. Show Top Artists Data
                if (data.top_artists && data.top_artists.length > 0) {
                    const artistList = document.getElementById('artist-list');
                    
                    data.top_artists.forEach((artist, index) => {
                        const genres = Array.isArray(artist.genres) ? artist.genres.slice(0, 2).join(', ') : '';
                        
                        artistList.innerHTML += `
                            <div class="artist-item">
                                <strong>#${index + 1} ${artist.name}</strong>
                                <span style="color: #B3B3B3; font-size: 14px;">${genres}</span>
                            </div>
                        `;
                    });

                    document.getElementById('top-artists-container').style.display = 'block';
                }

            } catch (error) {
                document.getElementById('loading').textContent = "Failed to load data. Make sure api.php is set up correctly.";
                document.getElementById('loading').style.color = "red";
            }
        });
    </script>
</body>
</html>
```