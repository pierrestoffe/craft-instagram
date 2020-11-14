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
use pierrestoffe\instagram\services\Token as TokenService;

use Craft;
use craft\base\Component;
use craft\config\GeneralConfig;
use craft\helpers\UrlHelper;
use Facebook\Facebook as FacebookClient;

/**
 * @author    Pierre Stoffe
 * @package   Instagram
 * @since     1.0.0
 */
class FacebookApi extends Component
{    
    // Public Methods
    // =========================================================================
    
    /**
     * Get a media information using the Oembed Facebook API endpoint
     * 
     * @param string $url
     * @param string $accessToken
     *
     * @return object
     */
    public function getOembedMedia($url, $accessToken)
    {
        $facebookClient = $this->getFacebookClient();
        
        try {
            $response = $facebookClient->get('/instagram_oembed/?url=' . $url, $accessToken);
            $decodedResponse = (object)$response->getDecodedBody();
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            $this->setError('Failed getting Instagram Oembed information', $e->getMessage());
            return [];
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            $this->setError('Failed getting Instagram Oembed information', $e->getMessage());
            return [];
        }
        
        if (!isset($decodedResponse)) {
            $this->setError('Failed getting Instagram Oembed information', $e->getMessage());
            return [];
        }
        
        $oembedMediaInformation = [
            'image' => $decodedResponse->thumbnail_url ?? null,
            'url' => $url,
            'handle' => $decodedResponse->author_name ?? null,
        ];
        
        return $oembedMediaInformation;
    }
    
    /**
     * Get all saved access tokens
     *
     * @return TokenRecord[]
     */
    public function getSavedAccessTokens()
    {
        $tokenService = new TokenService();
        $records = $tokenService->getRecords('facebook', null, false);
        
        if (empty($records)) {
            return false;
        }
        
        foreach ($records as $record) {
            $accessToken = $record->accessToken ?? null;
            $dateCurrent = new \DateTime;
            $dateExpire = new \DateTime($record->dateExpire);
            
            $dateDiffDays = $dateExpire->diff($dateCurrent)->format("%a");
            if ($dateDiffDays < 10) {
                $accessToken = $this->renewFacebookAccessToken($record);
                $records = $tokenService->getRecords('facebook', null, false);
            }
        }

        return $records;
    }
    
    /**
     * Get a Facebook username from a Facebook user ID
     *
     * @param int $id
     * @param string $accessToken
     *
     * @return string
     */
    public function getUserNameFromId($id, $accessToken)
    {
        $tokenService = new TokenService();
        $facebookClient = $this->getFacebookClient();
        
        try {
            $response = $facebookClient->get('/' . $id . '/?fields=name', $accessToken);
            $facebookUserInformation = $response->getGraphUser() ?? null;
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            $this->setError('Failed getting Facebook username from user ID', $e->getMessage());
            return null;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            $this->setError('Failed getting Facebook username from user ID', $e->getMessage());
            return null;
        }
        
        if (!isset($facebookUserInformation)) {
            $this->setError('Failed getting Facebook user name from user ID');
            return null;
        }
        
        $facebookUserName = $facebookUserInformation->getField('name');
        
        return $facebookUserName;
    }

    /**
     * Renew an access token
     *
     * @param TokenRecord $record
     *
     * @return TokenRecord
     */
    public function renewFacebookAccessToken($record)
    {
        $tokenService = new TokenService();
        $facebookClient = $this->getFacebookClient();
        $oauth2Client = $facebookClient->getOauth2Client();
        $accessToken = $record->accessToken;
        
        try {
            $renewedAccessToken = $oauth2Client->getLongLivedAccessToken($accessToken);
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            $this->setError('Failed renewing Facebook access token', $e->getMessage());
            return null;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            $this->setError('Failed renewing Facebook access token', $e->getMessage());
            return null;
        }
        
        if (!isset($renewedAccessToken)) {
            $this->setError('Failed renewing Facebook access token');
            return null;
        }
        
        $facebookUserId = $record->userId;
        $facebookUsername = $record->username;
        $facebookAccessToken = $renewedAccessToken->getValue();
        $facebookAccessToken = $renewedAccessToken->getExpiresAt();
        
        // Save record
        $saveRecord = $tokenService->saveRecord('facebook', $facebookUserId, $facebookUsername, $facebookAccessToken, $facebookDateExpire);

        return $saveRecord;
    }
    
    /**
     * Initiate the Facebook Client
     *
     * @return Facebook
     */
    public function getFacebookClient()
    {
        $settings = Instagram::$plugin->getSettings();
        $facebookAppId = $settings->facebookAppId;
        $facebookAppSecret = $settings->facebookAppSecret;
        $facebookClient = new FacebookClient([
            'app_id' => $facebookAppId,
            'app_secret' => $facebookAppSecret,
            'default_graph_version' => 'v8.0'
        ]);
        
        return $facebookClient;
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
