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
namespace D1m\Wesms\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class sendSMSForQuoteRemind
 * @package D1m\Wesms\Observer
 */
class sendSMSForQuoteRemind implements ObserverInterface
{
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /*
     * D1m\Wesms\Model\LogFactory
     */
    private $_logFactory;

    /**
     * @var \D1m\Wesms\Helper\Sms
     */
    protected $_smsHelper;

    /**
     * sendSMSForOrderRefund constructor.
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \D1m\Wesms\Model\LogFactory $logFactory
     * @param \D1m\Wesms\Helper\Sms $smsHelper
     */
    public function __construct(
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \D1m\Wesms\Model\LogFactory $logFactory,
        \D1m\Wesms\Helper\Sms $smsHelper
    ) {
        $this->_customerFactory = $customerFactory;
        $this->_logFactory = $logFactory;
        $this->_smsHelper = $smsHelper;
    }

    /**
     * @param $quote \Magento\Quote\Model\Quote
     * @return mixed
     */
    private function getCustomerMobile($quote)
    {
        $mobile = '';
        if($customerId = $quote->getCustomerId()){
            /* @var $customer \Magento\Customer\Model\Customer */
            $customer = $this->_customerFactory->create();
            $customer->getResource()->load($customer, $customerId);
            $mobile = $customer->getData('mobile');

        }
        return $mobile;
    }

    /**
     * send order refund SMS
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /* @var $quote \Magento\Quote\Model\Quote */
        $quote = $observer->getData('quote');
        $mobile = $this->getCustomerMobile($quote);
        //if this is mobile, send SMS
        if ($this->_smsHelper->validateChinaMobile($mobile)) {
            //create sms log object
            $smsLog = $this->_logFactory->create();
            $smsLog->setData('number', $mobile)
                ->setData('action_type', \D1m\Wesms\Helper\Sms::SMS_ACTION_QUOTE_REMIND);
            $re = $this->_smsHelper->sendSms($smsLog);
            if (empty($re['status'])) {
                throw new \Exception(__('发送失败.')->__toString());
            }
        } else {
            throw new \Exception(__('手机号有误.')->__toString());
        }
        return $this;
    }
}
