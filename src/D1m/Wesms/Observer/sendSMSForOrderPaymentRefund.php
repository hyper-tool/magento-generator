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
 * Class sendSMSForOrderPaymentRefund
 * Send payment refund when refund payment
 * @package D1m\Wesms\Observer
 */
class sendSMSForOrderPaymentRefund implements ObserverInterface
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
     * @param $order
     * @return mixed
     */
    private function getCustomerMobile($order)
    {
        if($customerMobile = $order->getCustomerMobile()) {
            return $customerMobile;
        }
        if($customerId = $order->getCustomerId()){
            /* @var $customer \Magento\Customer\Model\Customer */
            $customer = $this->_customerFactory->create();
            $customer->getResource()->load($customer, $customerId);
            $mobile = $customer->getData('mobile');
            //if the customer register with email
            if(empty($mobile)) {
                $mobile = $order->getShippingAddress()->getData('telephone');
            }
        } else {
            $mobile = $order->getShippingAddress()->getData('telephone');
        }
        return $mobile;
    }

    /**
     * Send order payment refund SMS notify
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $payment = $observer->getEvent()->getPayment();
        $order = $payment->getOrder();
        $mobile = $this->getCustomerMobile($order);
        //if this is mobile, send SMS
        if ($this->_smsHelper->validateChinaMobile($mobile)) {
            //create sms log object
            $smsLog = $this->_logFactory->create();
            $smsLog->setData('number', $mobile)
                ->setData('action_type', \D1m\Wesms\Helper\Sms::SMS_ACTION_REFUND);
            $this->_smsHelper->sendSms($smsLog, $order);
        }

        return $this;
    }
}
