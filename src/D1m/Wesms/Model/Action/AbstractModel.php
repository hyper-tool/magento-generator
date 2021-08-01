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
namespace D1m\Wesms\Model\Action;

/**
 * Class AbstractModel
 * SMS type action model abstract
 * @package D1m\Wesms\Model\Action
 */
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ObjectManager;

abstract class AbstractModel extends \Magento\Framework\DataObject
{
    //default sms code lifetime 300s
    const DEFAULT_LIFETIME = 300;

    /**
     * @var SMS Instance
     */
    protected $_sms;

    /**
     * @var Entity Instance
     */
    protected $_entity;

    /**
     * @var \D1m\Wesms\Helper\Data
     */
    protected $_weSmsHelper;

    /**
     * @var
     */
    protected $_mobile;

    /**
     * lifetime for sms code
     *
     * @var null
     */
    protected $_lifetime = null;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\Timezone
     */
    protected $timezone;

    /**
     * @var StoreManagerInterface
     * @since 100.1.0
     */
    protected $storeManager;

    /**
     * AbstractModel constructor.
     * @param \D1m\Wesms\Helper\Data $weSmsHelper
     * @param \Magento\Framework\Stdlib\DateTime\Timezone $timezone
     * @param array $data
     */
    public function __construct(
        \D1m\Wesms\Helper\Data $weSmsHelper,
        \Magento\Framework\Stdlib\DateTime\Timezone $timezone,
        array $data = []
    )
    {
        $this->timezone = $timezone;
        $this->_weSmsHelper = $weSmsHelper;
        $this->storeManager = ObjectManager::getInstance()->get(StoreManagerInterface::class);

        parent::__construct($data);
    }

    /**
     * Set mobile
     * @param String $mobile
     * @return $this
     */
    public function setMobile($mobile)
    {
        $this->_mobile = $mobile;
        return $this;
    }

    /**
     * Get format
     * @return String
     */
    public function getMobile()
    {
        return $this->_mobile;
    }

    /**
     * Set SMS object
     * @param $sms
     * @return $this
     */
    public function setSms($sms)
    {
        $this->_sms = $sms;
        return $this;
    }

    /**
     * Get SMS object
     * @return SMS
     */
    public function getSms()
    {
        return $this->_sms;
    }

    /**
     * Set entity model
     * @param $entity
     * @return $this
     */
    public function setEntity($entity)
    {
        $this->_entity = $entity;
        return $this;
    }

    /**
     * Get entity model
     * @return mixed
     */
    public function getEntity()
    {
        return $this->_entity;
    }

    /**
     * Enable Send SMS
     * @return bool
     */
    protected function _enableSendSMS()
    {
        return true;
    }

    /**
     * Get SMS Template
     */
    protected function _getSMSTemplate(){}

    /**
     * Get SMS Content
     */
    public function getSMSContent(){}

    /**
     * return sms code lifetime
     *
     * @return mixed|null
     */
    protected function getLifetime()
    {
        if (is_null($this->_lifetime)) {
            $this->_lifetime =  (int) $this->_getStoreConfig('sms_setting/sms_security/sms_code_lifetime');
            if($this->_lifetime <= 0 ) {
                return self::DEFAULT_LIFETIME;
            }
        }
        return $this->_lifetime;
    }

    /**
     * Get config data
     * @param $path
     * @return mixed
     */
    protected function _getStoreConfig($path, $store_id = null){
        $defualt_store_id = $this->storeManager->getStore()->getId();
        if ($store_id) {
            $defualt_store_id = $store_id;
        }
        return $this->_weSmsHelper->getConfigValue($path, $defualt_store_id);
    }
}