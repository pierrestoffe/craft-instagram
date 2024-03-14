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
use pierrestoffe\instagram\services\Token as TokenService;
use pierrestoffe\instagram\services\InstagramApi as InstagramApiService;

use Craft;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use yii\web\Response;

/**
 * @author    Pierre Stoffe
 * @package   Instagram
 * @since     1.0.0
 */
class InstagramController extends Controller
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
     * Get an Instagram access token
     *
     * @return Response|null
     */
    public function actionGetAccessToken()
    {
        $instagramService = new InstagramApiService();
        $instagramClient = $instagramService->getInstagramClient();

        // Get Instagram Login url
        $loginUrl = $instagramClient->get_authorize_url();

        // Redirect to Instagram Login url
        return $this->redirect($loginUrl);
    }

    /**
     * Verify an Instagram access token
     *
     * @return Response|bool
     */
    public function actionVerifyAccessToken()
    {
        $tokenService = new TokenService();
        $instagramService = new InstagramApiService();
        $instagramClient = $instagramService->getInstagramClient();

        // Get access token
        try {
            $code = Craft::$app->getRequest()->getParam('code');
            $accessToken = $instagramClient->get_access_token($code);
        } catch (\Throwable $e) {
            $tokenService->setError('Failed getting Instagram access token', $e->getMessage());
            return false;
        }

        if (!isset($accessToken->access_token)) {
            $tokenService->setError('Failed getting Instagram access token');
            return false;
        }

        // Get long-lived access token
        try {
            $instagramAccessToken = $accessToken->access_token;
            $instagramUserId = $accessToken->user_id;
            $instagramClient->set_access_token($instagramAccessToken);
            $accessToken = $instagramClient->get_long_lived_token();
        } catch (\Throwable $e) {
            $tokenService->setError('Failed getting Instagram access token', $e->getMessage());
            return false;
        }

        if (!isset($accessToken->access_token)) {
            $tokenService->setError('Failed getting Instagram long-lived access token');
            return false;
        }

        // Get access token information
        $instagramAccessToken = $accessToken->access_token;
        $instagramUser = $instagramClient->get_user($instagramUserId);
        $instagramUsername = $instagramService->getUsernameFromId($instagramUserId, $instagramAccessToken);
        $instagramDateExpiresIn = $accessToken->expires_in;
        $instagramDateExpire = new \DateTime;
        $instagramDateExpire->modify('+ ' . $instagramDateExpiresIn . 'seconds');

        // Save record
        $saveRecord = $tokenService->saveRecord('instagram', $instagramUserId, $instagramUsername, $instagramAccessToken, $instagramDateExpire);

        // Redirect to plugin settings page
        $redirectUrl = UrlHelper::cpUrl('settings/plugins/instagram');
        return $this->redirect($redirectUrl);
    }

    public function actionGetImageUrl($mediaId)
    {
        $url = 'https://www.instagram.com/p/' . $mediaId;
        $mediaFromUrls = Instagram::$plugin->media->getMediaFromUrls([$url]);

        if (empty($mediaFromUrls)) {
            return $this->asRaw('');
        }

        $imageUrl = $mediaFromUrls[0]['image'] ?? null;

        return $this->redirect($imageUrl);
    }
}
