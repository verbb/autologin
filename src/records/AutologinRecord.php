<?php
/**
 * Autologin plugin for Craft CMS 3.x
 *
 * Automatically login based on whitelisted IP or a key
 *
 * @link      https://superbig.co
 * @copyright Copyright (c) 2017 Superbig
 */

namespace superbig\autologin\records;

use superbig\autologin\Autologin;

use Craft;
use craft\db\ActiveRecord;

/**
 * @author    Superbig
 * @package   Autologin
 * @since     1.0.0
 */
class AutologinRecord extends ActiveRecord
{
    // Public Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%autologin_autologinrecord}}';
    }
}
