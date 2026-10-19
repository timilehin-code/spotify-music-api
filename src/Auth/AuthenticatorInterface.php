 <?php

    namespace SpotifyMusicApi\Auth;

    interface AuthenticatorInterface
    {
        /**
         * Get a valid Spotify access token (auto-refreshes if expired).
         * 
         * @return  string
         */
        public function getAccessToken(): string;

        /**
         * Force-refresh the access token using the stored refresh token.
         * 
         *  @return  string
         */
        public function refreshAccessToken(): string;
    }
