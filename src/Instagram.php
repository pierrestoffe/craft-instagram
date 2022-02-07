<?php
/**
 * Instagram plugin for Craft CMS 3.x
 *
 * Instagram integration for Craft CMS
 *
 * @link      https://pierrestoffe.be
 * @copyright Copyright (c) 2020 Pierre Stoffe
 */

namespace pierrestoffe\instagram;

use pierrestoffe\instagram\models\Settings as SettingsModel;
use pierrestoffe\instagram\models\Token as TokenModel;
use pierrestoffe\instagram\services\Media as MediaService;
use pierrestoffe\instagram\services\Settings as SettingsService;
use pierrestoffe\instagram\services\InstagramApi as InstagramService;
use pierrestoffe\instagram\services\FacebookApi as FacebookService;
use pierrestoffe\instagram\services\Token as TokenService;
use pierrestoffe\instagram\variables\InstagramVariable;

use Craft;
use craft\base\Plugin;
use craft\events\RegisterUrlRulesEvent;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\web\UrlManager;
use craft\web\twig\variables\CraftVariable;

use yii\base\Event;

/**
 * Class Instagram
 *
 * @author    Pierre Stoffe
 * @package   Instagram
 * @since     1.0.0
 *
 * @property  MediaService $mediaService
 * @property  SettingsService $settingsService
 * @property  InstagramService $instagramService
 * @property  FacebookService $facebookService
 * @property  TokenService $tokenService
 */
class Instagram extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var Instagram
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $schemaVersion = '1.0.0';

    /**
     * @var bool
     */
    public $hasCpSettings = true;

    /**
     * @var bool
     */
    public $hasCpSection = false;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        $this->_registerComponents();

        // Event::on(
        //     UrlManager::class,
        //     UrlManager::EVENT_REGISTER_SITE_URL_RULES,
        //     function (RegisterUrlRulesEvent $event) {
        //         $event->rules['GET instagram/access-token/validate-instagram'] = 'instagram/access-token/';
        //     }
        // );

        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['instagram/instagram/verify-access-token'] = 'instagram/instagram/verify-access-token';
            }
        );

        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['instagram/get-image-url/<mediaId:[\w|_-]*>'] = 'instagram/instagram/get-image-url';
            }
        );

        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('instagram', InstagramVariable::class);
            }
        );

        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                }
            }
        );

        Craft::info(
            Craft::t(
                'instagram',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }



    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function createSettingsModel()
    {
        $settingsModel = new SettingsModel();

        return $settingsModel;
    }

    /**
     * @inheritdoc
     */
    protected function settingsHtml(): string
    {
        return Craft::$app->view->renderTemplate(
            'instagram/settings',
            [
                'settings' => $this->getSettings()
            ]
        );
    }

    // Private Methods
    // =========================================================================

    /**
     * Registers the components
     */
    private function _registerComponents()
    {
        $this->setComponents([
            'media' => MediaService::class,
            'settings' => SettingsService::class,
            'instagram' => InstagramService::class,
            'facebook' => FacebookService::class,
            'token' => TokenService::class,
        ]);
    }

}
