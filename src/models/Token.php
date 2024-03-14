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
class Token extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $type = '';

    /**
     * @var string
     */
    public $userId = '';

    /**
     * @var string
     */
    public $username = '';

    /**
     * @var string
     */
    public $accessToken = '';

    /**
     * @var DateTime
     */
    public $dateExpire = '';

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'userId', 'username', 'accessToken'], 'string'],
            [['dateExpire'], 'date'],
            [['type', 'userId', 'username', 'accessToken', 'dateExpire'], 'required'],
        ];
    }
}
