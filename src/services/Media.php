<?php
/**
 * Instagram plugin for Craft CMS 3.x
 *
 * Instagram integration for Craft CMS
 *
 * @link      https://pierrestoffe.be
 * @copyright Copyright (c) 2020 Pierre Stoffe
 */

namespace pierrestoffe\instagram\services;

use pierrestoffe\instagram\Instagram;
use pierrestoffe\instagram\services\InstagramApi as InstagramApiService;
use pierrestoffe\instagram\services\FacebookApi as FacebookApiService;

use Craft;
use craft\base\Component;
use craft\helpers\UrlHelper;
use GuzzleHttp\Client as GuzzleClient;

/**
 * @author    Pierre Stoffe
 * @package   Instagram
 * @since     1.0.0
 */
class Media extends Component
{    
    // Public Methods
    // =========================================================================

    /**
     * Get recent Instagram media from the user that we have the information for.
     *
     * @return array
     */
    public function getMediaFromUser($username)
    {
        $allMedia = [];
        $accessToken = Instagram::$plugin->instagram->getSavedAccessTokens($username);
        $userId = $accessToken[0]->userId ?? null;
        $accessToken = $accessToken[0]->accessToken ?? null;
        
        if (empty($userId) || empty($accessToken)) {
            return $allMedia;
        }
        
        // Check first for this media information in the cache
        $cachedMedia = Craft::$app->getCache()->get('instagram-user-' . $username);
        if ($cachedMedia !== false) {
            return $cachedMedia;
        }
        
        // Otherwise make a new request to Instagram's API
        $instagramClient = new InstagramApiService();
        $fields = 'id,caption,media_url,thumbnail_url,permalink,media_type,username';
        $allMedia = $instagramClient->getUserMedia($userId, $fields, $accessToken);
        
        // Save media information in cache
        Craft::$app->getCache()->set('instagram-user' . $username, $allMedia, 3600);

        return $allMedia;
    }

    /**
     * Get Instagram media
     *
     * @param array $urls
     *
     * @return array
     */
    public function getMediaFromUrls($urls)
    {
        $allMedia = [];
        $accessToken = Instagram::$plugin->facebook->getSavedAccessTokens();
        $accessToken = $accessToken[0]->accessToken ?? null;
        
        if (empty($accessToken)) {
            return $allMedia;
        }

        foreach ($urls as $url) {
            preg_match('/(?:.*)?(instagram\.com\/p\/([\w|_-]*))(?:\/)?(?:.*)?/', $url, $matches);
            if (count($matches) < 2) {
                $this->setError('The Instagram URL is not valid (' . $url . ')');
                continue;
            }
            
            // Check first for this media information in the cache
            $cachedMedia = Craft::$app->getCache()->get('instagram-id-' . $matches[2]);
            if ($cachedMedia !== false) {
                $allMedia[] = $cachedMedia;
                continue;
            }
            
            // Otherwise make a new request to Facebook's API
            $facebookApiService = new FacebookApiService();
            $mediaInformation = $facebookApiService->getOembedMedia($url, $accessToken);
            
            // Save media information in cache
            Craft::$app->getCache()->set('instagram-id' . $matches[2], $mediaInformation, 3600);
            
            $allMedia[] = $mediaInformation;
        }

        return $allMedia;
    }
    
    /**
     * Register errors in session and log
     *
     * @param string $message
     */
    public function setError($shortMessage, $errorMessage = null)
    {
        $translatedMessage = Craft::t(
            'instagram',
            $shortMessage,
        );
        
        Craft::$app->getSession()->setError($translatedMessage);
        Craft::error(
            $translatedMessage . ': ' . $errorMessage,
            __METHOD__
        );
    }
    
    // Protected Methods
    // =========================================================================
    
    // Parse the JSON that is returned by the Instagram API
    protected function parseInstagramApi($data) {
        $media = [];
        
        $mediaUrl = $data->thumbnail_url ?? $data->media_url ?? null;
        $caption = $data->caption ?? null;
        $type = $data->media_type ?? null;
        $permalink = $data->permalink ?? null;
        
        if (empty($mediaUrl) || empty($permalink)) {
            return $media;
        }
        
        $media = [
            'image' => $mediaUrl,
            'url' => $permalink,
            'caption' => $caption,
            'type' => strtolower($type)
        ];
        
        return $media;
    }
}
