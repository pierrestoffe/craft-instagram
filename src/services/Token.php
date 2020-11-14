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
use pierrestoffe\instagram\records\Token as TokenRecord;

use Craft;
use craft\base\Component;
use craft\config\GeneralConfig;
use craft\helpers\UrlHelper;
use Facebook\Facebook;
use GuzzleHttp\Client as GuzzleClient;

/**
 * @author    Pierre Stoffe
 * @package   Instagram
 * @since     1.0.0
 */
class Token extends Component
{    
    // Public Methods
    // =========================================================================

    /**
     * Get the information saved in the database.
     *
     * @return TokenRecord
     */
    public function getRecords($type, $username = null, $single = false)
    {
        $tokenRecord = new TokenRecord;
        $records = $tokenRecord->find()->where(['type' => $type]);
        if (!empty($username)) {
            $records = $records->andWhere(['username' => $username]);
        }
        
        if ($single) {
            $records = $records->one();
        } else {
            $records = $records->all();
        }

        return $records;
    }

    /**
     * Save a record with the information in the database.
     *
     * @param string $type
     * @param string $userId
     * @param string $username
     * @param string $accessToken
     * @param \DateTime $dateExpire
     *
     * @return TokenRecord[]
     */
    public function saveRecord($type, $userId, $username, $accessToken, $dateExpire)
    {
        $record = new TokenRecord;
        $recordExisting = $this->getRecords($type, $username, true);

        if (!empty($recordExisting)) {
            $record = $recordExisting;
        }

        $record->type = $type;
        $record->userId = $userId;
        $record->username = $username;
        $record->accessToken = $accessToken;
        $record->dateExpire = $dateExpire->format('Y-m-d H:i:s');

        $saved = false;
        if (!empty($recordExisting)) {
            $saved = $record->update();
        } else {
            $saved = $record->save();
        }

        return $record;
    }
}
