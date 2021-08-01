<?php
/**
 * 2012-2017 D1m Group
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0) that is available
 * through the world-wide-web at this URL: http://www.opensource.org/licenses/OSL-3.0
 * If you are unable to obtain it through the world-wide-web, please send an email
 * to sales@d1m.cn so we can send you a copy immediately.
 *
 * @author D1m Group
 * @copyright 2012-2017 D1m Group
 * @license http://www.opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */
namespace D1m\Wesms\Model\System\Config\Source;

/**
 * Class Methods
 * @package D1m\Wesms\Model\System\Config\Source
 */
class Methods
{
    /**
     *  define  SMS PLATFORM
     */
    const SMS_PLATFORM_FOR_WE = 1;

    /**
     *  define  SMS PLATFORM
     */
    const SMS_PLATFORM_FOR_KEHENG = 2;

    /**
     *  define  SMS PLATFORM
     */
    const SMS_PLATFORM_FOR_YUNXIN = 3;

    /**
     *  define  SMS PLATFORM
     */
    const SMS_PLATFORM_FOR_ALI = 4;

    /**
     *  define  SMS 广东高兆
     */
    const SMS_PLATFORM_FOR_SHGMNETS = 5;

    /**
     * Retrieve option array
     * @return array
     */
    static public function getOptionArray()
    {
        return array(
            self::SMS_PLATFORM_FOR_WE   => 'WE SMS',
            self::SMS_PLATFORM_FOR_KEHENG   => 'Ke Heng',
            self::SMS_PLATFORM_FOR_YUNXIN   => 'Yun Xin',
            self::SMS_PLATFORM_FOR_ALI   => 'Ali SMS',
            self::SMS_PLATFORM_FOR_SHGMNETS   => 'ShgmNets SMS',
        );
    }

    /**
     * Get option data
     * @return array
     */
    public function toOptionArray()
    {
          return self::getAllOptions();
    }

    /**
     * Retrieve option array with empty value
     * @return array
     */
    static public function getAllOption()
    {
        $options = self::getOptionArray();
        array_unshift($options, array('value'=>'', 'label'=>''));
        return $options;
    }

    /**
     * Retrieve option array with empty value
     * @return array
     */
    static public  function getAllOptions()
    {
        $res = array(
            array(
                'value' => '',
                'label' => __('-- Please Select --')
            )
        );
        foreach (self::getOptionArray() as $index => $value) {
            $res[] = array(
                'value' => $index,
                'label' => $value
            );
        }
        return $res;
    }
}