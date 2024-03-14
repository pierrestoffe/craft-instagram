<?php
/**
 * Instagram plugin for Craft CMS 3.x
 *
 * Instagram integration for Craft CMS
 *
 * @link      https://pierrestoffe.be
 * @copyright Copyright (c) 2020 Pierre Stoffe
 */

namespace pierrestoffe\instagram\variables;

use pierrestoffe\instagram\Instagram;

/**
 * @author    Pierre Stoffe
 * @package   Instagram
 * @since     1.0.0
 */
class InstagramVariable
{
    // Public Methods
    // =========================================================================

    public function getMediaFromUser($username)
    {
        $recentMediaFromUser = Instagram::$plugin->media->getMediaFromUser($username);

        return $recentMediaFromUser;
    }

    public function getMediaFromUrls($urls)
    {
        $mediaFromUrls = Instagram::$plugin->media->getMediaFromUrls($urls);

        return $mediaFromUrls;
    }

    public function getHtmlFromUrls($urls)
    {
        $htmlFromUrls = Instagram::$plugin->media->getHtmlFromUrls($urls);

        return $htmlFromUrls;
    }

    public function getSavedInstagramAccessToken($username)
    {
        $accessTokens = Instagram::$plugin->instagram->getSavedAccessTokens($username);
        $accessToken = $accessTokens[0] ?? null;

        return $accessToken;
    }

    public function getSavedInstagramAccessTokens()
    {
        $accessTokens = Instagram::$plugin->instagram->getSavedAccessTokens();

        return $accessTokens;
    }

    public function getSavedFacebookAccessTokens()
    {
        $accessTokens = Instagram::$plugin->facebook->getSavedAccessTokens();

        return $accessTokens;
    }
}
