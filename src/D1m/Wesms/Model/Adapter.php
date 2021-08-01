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
namespace D1m\Wesms\Model;

/**
 * Class Adapter
 * @package D1m\Wesms\Model
 */
class Adapter {

    /**
     * wemediacn type code
     */
    const ADAPTER_SMS_WE = 'Wesms';

    /**
     * ke heng
     */
    const ADAPTER_SMS_KEHENG = 'KeHeng';

    /**
     * yunxin
     */
    const ADAPTER_SMS_YUNXIN = 'YunXin';

    /**
     * ali sms
     */
    const ADAPTER_SMS_ALI= 'AliSms';

    /**
     * 高兆
     */
    const ADAPTER_SMS_GZ= 'ShgmNets';

    /**
     * @var \D1m\Wesms\Model\Adapter\WesmsFactory
     */
    protected $_wesmsFactory;

    /**
     * @var \D1m\Wesms\Model\Adapter\keHengFactory
     */
    protected $_keHengFactory;

    /**
     * @var \D1m\Wesms\Model\Adapter\YunXinFactory
     */
    protected $_yunXinFactory;

    /**
     * @var \D1m\Wesms\Model\Adapter\AliSmsFactory
     */
    protected $_aliSmsFactory;

    /**
     * @var \D1m\Wesms\Model\Adapter\ShgmNetsFactory
     */
    protected $_gzSmsFactory;

    /**
     * Adapter constructor.
     * @param Adapter\WesmsFactory $wesmsFactory
     * @param Adapter\KeHengFactory $keHengFactory
     * @param Adapter\YunXinFactory $yunXinFactory
     * @param Adapter\AliSmsFactory $aliSmsFactory
     * @param Adapter\ShgmNetsFactory $gzSmsFactory
     */
    public function __construct(
        \D1m\Wesms\Model\Adapter\WesmsFactory $wesmsFactory,
        \D1m\Wesms\Model\Adapter\KeHengFactory $keHengFactory,
        \D1m\Wesms\Model\Adapter\YunXinFactory $yunXinFactory,
        \D1m\Wesms\Model\Adapter\AliSmsFactory $aliSmsFactory,
        \D1m\Wesms\Model\Adapter\ShgmNetsFactory $gzSmsFactory
    ){
        $this->_wesmsFactory = $wesmsFactory;
        $this->_keHengFactory = $keHengFactory;
        $this->_yunXinFactory = $yunXinFactory;
        $this->_aliSmsFactory = $aliSmsFactory;
        $this->_gzSmsFactory = $gzSmsFactory;
    }

    /**
     * Retrieve option array
     * @return array
     */
    static public function getOptionArray()
    {
        return array(
            self::ADAPTER_SMS_WE => 'Wesms',
            self::ADAPTER_SMS_KEHENG => 'KeHeng',
            self::ADAPTER_SMS_YUNXIN => 'YunXin',
            self::ADAPTER_SMS_ALI=> 'AliSms',
            self::ADAPTER_SMS_GZ=> 'ShgmNets',
        );
    }

    /**
     * Get SMS method By Tag
     * @param null $tag
     * @return null
     */
    static public function getSMSMethodCodeByNumber($tag = null)
    {
        $smsMethods = array(
            \D1m\Wesms\Model\System\Config\Source\Methods::SMS_PLATFORM_FOR_WE => self::ADAPTER_SMS_WE,
            \D1m\Wesms\Model\System\Config\Source\Methods::SMS_PLATFORM_FOR_KEHENG => self::ADAPTER_SMS_KEHENG,
            \D1m\Wesms\Model\System\Config\Source\Methods::SMS_PLATFORM_FOR_YUNXIN => self::ADAPTER_SMS_YUNXIN,
            \D1m\Wesms\Model\System\Config\Source\Methods::SMS_PLATFORM_FOR_ALI => self::ADAPTER_SMS_ALI,
            \D1m\Wesms\Model\System\Config\Source\Methods::SMS_PLATFORM_FOR_SHGMNETS => self::ADAPTER_SMS_GZ,
        );

        if (!is_null($tag)) {
            if (isset($smsMethods[$tag])) {
                return $smsMethods[$tag];
            }
            return null;
        }
    }

    /**
     * @param $adapter
     * @return Adapter\KeHeng|Adapter\Wesms|Adapter\YunXin
     * @throws \Exception
     */
    public function factory($adapter)
    {
        switch( $adapter ) {
            case self::ADAPTER_SMS_WE:
                return $this->_wesmsFactory->create();
                break;
            case self::ADAPTER_SMS_KEHENG:
                return $this->_keHengFactory->create();
                break;
            case self::ADAPTER_SMS_YUNXIN:
                return $this->_yunXinFactory->create();
                break;
            case self::ADAPTER_SMS_ALI:
                return $this->_aliSmsFactory->create();
                break;
            case self::ADAPTER_SMS_GZ:
                return $this->_gzSmsFactory->create();
                break;
            default:
                throw new \Exception('Invalid adapter selected.');
                break;
        }
    }
}