<?php
/**
 * Instagram plugin for Craft CMS 3.x
 *
 * Instagram integration for Craft CMS
 *
 * @link      https://pierrestoffe.be
 * @copyright Copyright (c) 2020 Pierre Stoffe
 */

namespace pierrestoffe\instagram\models;

use craft\base\Model;

/**
 * @author    Pierre Stoffe
 * @package   Instagram
 * @since     1.0.0
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $facebookAppId = '';

    /**
     * @var string
     */
    public $facebookAppSecret = '';

    /**
     * @var string
     */
    public $instagramAppId = '';

    /**
     * @var string
     */
    public $instagramAppSecret = '';

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['facebookAppId', 'facebookAppSecret', 'instagramAppId', 'instagramAppSecret'], 'string']
        ];
    }
}
