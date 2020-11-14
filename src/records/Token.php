<?php
/**
 * Instagram plugin for Craft CMS 3.x
 *
 * Instagram integration for Craft CMS
 *
 * @link      https://pierrestoffe.be
 * @copyright Copyright (c) 2020 Pierre Stoffe
 */

namespace pierrestoffe\instagram\records;

use pierrestoffe\instagram\Instagram;

use Craft;
use craft\db\ActiveRecord;

/**
 * @author    Pierre Stoffe
 * @package   Instagram
 * @since     1.0.0
 */
class Token extends ActiveRecord
{
    // Public Static Methods
    // =========================================================================

    public static function tableName()
    {
        return '{{%instagram_tokens}}';
    }
}
