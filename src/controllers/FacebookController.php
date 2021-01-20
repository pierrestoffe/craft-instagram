<?php
/**
 * Instagram plugin for Craft CMS 3.x
 *
 * Instagram integration for Craft CMS
 *
 * @link      https://pierrestoffe.be
 * @copyright Copyright (c) 2020 Pierre Stoffe
 */

namespace pierrestoffe\instagram\controllers;

use pierrestoffe\instagram\Instagram;
use pierrestoffe\instagram\models\Settings as SettingsModel;
use pierrestoffe\instagram\services\Token as TokenService;
use pierrestoffe\instagram\services\FacebookApi as FacebookApiService;

use Craft;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use yii\base\Exception;
use yii\web\Response;

/**
 * @author    Pierre Stoffe
 * @package   Instagram
 * @since     1.0.0
 */
class FacebookController extends Controller
{    
    // Public Properties
    // =========================================================================

    /**
     * @inheritdoc
     */
    public $allowAnonymous = true;

    // Public Methods
    // =========================================================================

    /**
     * Get a Facebook access token
     *
     * @return Response|null
     */
    public function actionGetAccessToken()
    {
        $facebookService = new FacebookApiService();
        $facebookClient = $facebookService->getFacebookClient();
        $facebookHelper = $facebookClient->getRedirectLoginHelper();
        
        // Get Facebook Login url
        $facebookVerifyControllerUrl = UrlHelper::url('instagram/facebook/verify-access-token');
        $loginUrl = $facebookHelper->getLoginUrl($facebookVerifyControllerUrl);
        
        // Redirect to Facebook Login url
        return $this->redirect($loginUrl);
    }
    
    /**
     * Verify a Facebook access token
     *
     * @return Response|bool
     */
    public function actionVerifyAccessToken()
    {
        $tokenService = new TokenService();
        $facebookService = new FacebookApiService();
        $facebookClient = $facebookService->getFacebookClient();
        $facebookHelper = $facebookClient->getRedirectLoginHelper();
        
        // Get access token
        try {
            $accessToken = $facebookHelper->getAccessToken();
        } catch (\Throwable $e) {
            $tokenService->setError('Failed getting Facebook access token', $e->getMessage());
            return false;
        }
        
        if (!isset($accessToken)) {
            $tokenService->setError('Failed getting Facebook access token');
            return false;
        }
        
        // Get access token information
        $oauth2Client = $facebookClient->getOauth2Client();
        $accessTokenInformation = $oauth2Client->debugToken($accessToken);
        $facebookUserId = $accessTokenInformation->getUserId();
        $facebookAccessToken = $accessToken->getValue();
        $facebookUsername = $facebookService->getUserNameFromId($facebookUserId, $facebookAccessToken);
        $facebookDateExpire = $accessToken->getExpiresAt();
        
        // Save record
        $saveRecord = $tokenService->saveRecord('facebook', $facebookUserId, $facebookUsername, $facebookAccessToken, $facebookDateExpire);
        
        // Redirect to plugin settings page
        $redirectUrl = UrlHelper::cpUrl('settings/plugins/instagram');
        return $this->redirect($redirectUrl);
    }
}
