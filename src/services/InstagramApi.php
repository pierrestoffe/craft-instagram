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
use pierrestoffe\instagram\services\FacebookApi as FacebookApiService;
use pierrestoffe\instagram\services\Token as TokenService;

use Craft;
use craft\base\Component;
use craft\config\GeneralConfig;
use craft\helpers\UrlHelper;
use Instagram\Instagram as InstagramClient;

/**
 * @author    Pierre Stoffe
 * @package   Instagram
 * @since     1.0.0
 */
class InstagramApi extends Component
{    
    // Public Methods
    // =========================================================================
    
    /**
     * Get a user's media feed using Instagram's API
     * 
     * @param string $userId
     * @param string $fields
     * @param string $accessToken
     *
     * @return object
     */
    public function getUserMedia($userId, $fields, $accessToken)
    {
        $instagramClient = $this->getInstagramClient($accessToken);
        $userMedia = $instagramClient->get_user_media($userId, $fields);
        
        if (!isset($userMedia->data)) {
            $this->setError('Failed getting Instagram user media');
            return [];
        }
        
        $userMediaData = $userMedia->data;
        $userMediaInformation = [];
        foreach ($userMediaData as $media) {
            $userMediaInformationTemp = [
                'image' => $media->thumbnail_url ?? $media->media_url ?? null,
                'url' => $media->permalink ?? null,
                'handle' => $media->username ?? null,
                'caption' => $media->caption ?? null,
                'type' => $media->media_type ?? null
            ];
            
            $userMediaInformation[] = $userMediaInformationTemp;
        }
        
        return $userMediaInformation;
    }
    
    /**
     * Get all saved access tokens
     *
     * @return TokenRecord[]
     */
    public function getSavedAccessTokens($username = null)
    {
        $tokenService = new TokenService();
        $records = $tokenService->getRecords('instagram', $username, false);
        
        if (empty($records)) {
            return false;
        }
        
        foreach ($records as $record) {
            $accessToken = $record->accessToken ?? null;
            $dateCurrent = new \DateTime;
            $dateExpire = new \DateTime($record->dateExpire);
            
            $dateDiffDays = $dateExpire->diff($dateCurrent)->format("%a");
            if ($dateDiffDays < 10) {
                $accessToken = $this->renewInstagramAccessToken($record);
            }
        }
        
        $records = $tokenService->getRecords('instagram', $username, false);

        return $records;
    }
    
    /**
     * Get an Instagram username from an Instagram user ID
     *
     * @param int $id
     * @param string $accessToken
     *
     * @return string
     */
    public function getUsernameFromId($id, $accessToken)
    {
        $tokenService = new TokenService();
        $instagramClient = $this->getInstagramClient();
        $instagramClient->set_access_token($accessToken);
        
        try {
            $instagramUserInformation = $instagramClient->get_user($id);
        } catch(\Exception $e) {
            $this->setError('Failed getting Instagram username from user ID', $e->getMessage());
            return null;
        }
        
        if (!isset($instagramUserInformation->username)) {
            $this->setError('Failed getting Instagram user name from user ID');
            return null;
        }
        
        $instagramUsername = $instagramUserInformation->username;
        
        return $instagramUsername;
    }

    /**
     * Renew an access token
     *
     * @param TokenRecord $record
     *
     * @return TokenRecord
     */
    public function renewInstagramAccessToken($record)
    {
        $tokenService = new TokenService();
        $accessToken = $record->accessToken;
        $instagramClient = $this->getInstagramClient($accessToken);
        $renewedAccessToken = $instagramClient->get_refresh_token();
        
        if (!isset($renewedAccessToken->access_token)) {
            $this->setError('Failed renewing Instagram access token');
            return null;
        }
        
        $instagramUserId = $record->userId;
        $instagramUsername = $record->username;
        $instagramAccessToken = $renewedAccessToken->access_token;
        $instagramDateExpiresIn = $renewedAccessToken->expires_in;
        $instagramDateExpire = new \DateTime;
        $instagramDateExpire->modify('+ ' . $instagramDateExpiresIn . 'seconds');
        
        // Save record
        $saveRecord = $tokenService->saveRecord('instagram', $instagramUserId, $instagramUsername, $instagramAccessToken, $instagramDateExpire);

        return $saveRecord;
    }
    
    /**
     * Initiate the Instagram Client
     *
     * @return Instagram
     */
    public function getInstagramClient($accessToken = null)
    {
        $settings = Instagram::$plugin->getSettings();
        $instagramAppId = $settings->instagramAppId;
        $instagramAppSecret = $settings->instagramAppSecret;
        $instagramVerifyControllerUrl = UrlHelper::url('instagram/instagram/verify-access-token');
        $instagramClient = new InstagramClient(
            $instagramAppId,
            $instagramAppSecret,
            $instagramVerifyControllerUrl,
        );
        $instagramClient->set_scope('user_profile,user_media');
        
        if (!empty($accessToken)) {
            $instagramClient->set_access_token($accessToken);
        }
        
        return $instagramClient;
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
}
