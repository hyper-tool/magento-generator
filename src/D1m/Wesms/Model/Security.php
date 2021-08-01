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
 * Class Security
 * Security for sending sms by frontend request
 * @package D1m\Wesms\Model
 */
class Security extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var \D1m\Wesms\Model\LogFactory
     */
    protected $_smsLogFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_dateTime;

    /**
     * @var \D1m\Wesms\Helper\Data
     */
    protected $_weSmsHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * Security constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param LogFactory $smsLogFactory
     * @param \D1m\Wesms\Helper\Data $weSmsHelper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \D1m\Wesms\Model\LogFactory $smsLogFactory,
        \D1m\Wesms\Helper\Data $weSmsHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {

        $this->_dateTime = $dateTime;
        $this->_smsLogFactory = $smsLogFactory;
        $this->_weSmsHelper = $weSmsHelper;
        $this->_scopeConfig = $scopeConfig;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);

    }

    /**
     * The security check
     * @param $mobile
     * @return array
     */
    public function checkSecurity($mobile){
        //Send the time interval between the time interval of send
        $result = $this->limitByNumberInterval($mobile);
        if ($result !== true && strlen($result))
        {
            return array('error'=>true,'message'=>$result);
        }

        //The same number of constraints
        $result = $this->limitByNumber($mobile);
        if ($result !== true && strlen($result))
        {
            return array('error'=>true,'message'=>$result);
        }

        //The same IP restrictions
        $result = $this->limitNumberByIp();
        if ($result !== true && strlen($result))
        {
            return array('error'=>true,'message'=>$result);
        }

        return array();
    }

    /**
     * Message delivery time interval restriction
     * @param $mobileNumber
     * @return bool|\Magento\Framework\Phrase
     */
    public function limitByNumberInterval($mobileNumber)
    {
        if ($this->isActive() &&  intval($this->getSecurityConfig('number_interval')) > 0)
        {

            $sms = $this->_smsLogFactory->create()->getCollection()
                    ->addFieldToFilter('number', $mobileNumber)
                    ->setOrder('created_time', 'desc')
                    ->getFirstItem();

            if ($sms && $sms->getId() && ($this->_dateTime->gmtTimestamp() - strtotime($sms->getCreatedTime())) < intval($this->getSecurityConfig('number_interval')))
            {
                return __('The request of the mobile phone number interval are too short')->__toString();
            }
        }

        return true;
    }

    /**
     * Limit times by the same ip
     * @return bool|\Magento\Framework\Phrase
     */
    public function limitNumberByIp()
    {
        if ($this->isActive() &&  intval($this->getSecurityConfig('ip_limit')) > 0) {

            $ip = $this->_weSmsHelper->getClientIpAddress();

            $gmtDay         = $this->_dateTime->date('Y-m-d');
            $currentDay     = $this->_dateTime->gmtDate('Y-m-d H:i:s',$gmtDay);
            $tomorrowDay    = $this->_dateTime->gmtDate('Y-m-d H:i:s',strtotime('+1 days'.$gmtDay));

            $messageCollection = $this->_smsLogFactory->create()->getCollection()
                ->addFieldToFilter('ip',$ip)
                ->addFieldToFilter('created_time',array('gteq'=>$currentDay))
                ->addFieldToFilter('created_time',array('lteq'=>$tomorrowDay));

            if ($messageCollection->getSize() >= $this->getSecurityConfig('ip_limit'))
            {
                return __('IP send SMS request too much')->__toString();
            }
        }

        return true;
    }

    /**
     * @param $mobile
     * @param null $checkTime
     * @return bool
     */
    public function arrivalNoticeCheckTimeLimit($mobile)
    {
        $maxCheckTime = intval($this->getSecurityConfig('arrival_notice_captcha_limit'));

        if ($this->isActive() &&  $maxCheckTime > 0)
        {
            $gmtDay         = $this->_dateTime->date('Y-m-d');
            $currentDay     = $this->_dateTime->gmtDate('Y-m-d H:i:s',$gmtDay);
            $tomorrowDay    = $this->_dateTime->gmtDate('Y-m-d H:i:s',strtotime('+1 days'.$gmtDay));

            $messageCollection = $this->_smsLogFactory->create()->getCollection()
                ->addFieldToFilter('status',1)
                ->addFieldToFilter('action_type',\D1m\Wesms\Helper\Sms::SMS_ACTION_ARRIVAL_NOTICE_REQUEST)
                ->addFieldToFilter('number',$mobile)
                ->addFieldToFilter('created_time',array('gteq'=>$currentDay))
                ->addFieldToFilter('created_time',array('lteq'=>$tomorrowDay));

            if ($messageCollection->getSize() >= $maxCheckTime)
            {
                return false;
            }
        }

        return true;
    }

    /***
     * Each day, the same phone number send sms times restrictions
     * @param $mobileNumber
     * @return bool|int
     */
    public function limitByNumber($mobileNumber)
    {
        if ($this->isActive() &&  intval($this->getSecurityConfig('number_limit')) > 0)
        {
            $gmtDay         = $this->_dateTime->date('Y-m-d');
            $currentDay     = $this->_dateTime->gmtDate('Y-m-d H:i:s',$gmtDay);
            $tomorrowDay    = $this->_dateTime->gmtDate('Y-m-d H:i:s',strtotime('+1 days'.$gmtDay));

            $messageCollection = $this->_smsLogFactory->create()->getCollection()
                ->addFieldToFilter('status',1)
                ->addFieldToFilter('number',$mobileNumber)
                ->addFieldToFilter('created_time',array('gteq'=>$currentDay))
                ->addFieldToFilter('created_time',array('lteq'=>$tomorrowDay));

            if ($messageCollection->getSize() >= $this->getSecurityConfig('number_limit'))
            {
                return __('Mobile phone number to send request too much')->__toString();
            }
        }

        return true;
    }

    /**
     * Whether to activate the security configuration
     * @return mixed
     */
    public function isActive()
    {
        return $this->_getStoreConfig('sms_setting/sms_security/enable');
    }

    /**
     * Get SMS Security config
     * @param $configField
     * @return mixed
     */
    public function getSecurityConfig($configField)
    {
        return $this->_getStoreConfig('sms_setting/sms_security/' . $configField);
    }

    /**
     * Get config data
     * @param $path
     * @return mixed
     */
    protected function _getStoreConfig($path){
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}