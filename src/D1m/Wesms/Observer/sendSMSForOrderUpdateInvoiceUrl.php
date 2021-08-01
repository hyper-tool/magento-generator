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
 * Class sendSMSForOrderUpdateInvoiceUrl
 * @package D1m\Wesms\Observer
 */
class sendSMSForOrderUpdateInvoiceUrl implements ObserverInterface
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
     * @var \Magento\Framework\Stdlib\DateTime\Timezone
     */
    protected $timezone;
    /**
     * @var \D1m\Shipment\Helper\Data
     */
    protected $urlHelper;

    /**
     * sendSMSForOrderUpdateInvoiceUrl constructor.
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \D1m\Wesms\Model\LogFactory $logFactory
     * @param \Magento\Framework\Stdlib\DateTime\Timezone $timezone
     * @param \D1m\Wesms\Helper\Sms $smsHelper
     * @param \D1m\Shipment\Helper\Data $urlHelper
     */
    public function __construct(
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \D1m\Wesms\Model\LogFactory $logFactory,
        \Magento\Framework\Stdlib\DateTime\Timezone $timezone,
        \D1m\Wesms\Helper\Sms $smsHelper,
        \D1m\Shipment\Helper\Data $urlHelper
    ) {
        $this->_customerFactory = $customerFactory;
        $this->_logFactory = $logFactory;
        $this->_smsHelper = $smsHelper;
        $this->timezone = $timezone;
        $this->urlHelper = $urlHelper;
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
     * send order update invoice url SMS
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /* @var $order \Magento\Sales\Model\Order */
        $order = $observer->getData('order');
        $mobile = $this->getCustomerMobile($order);
        $updatedAtPrc = $this->timezone->date($order->getUpdatedAt(), null)->format('Y-m-d H:i:s');
        $order->setData('updated_at_prc',$updatedAtPrc);
        // 发票生成短链接
        $order->setData('invice_link',$this->urlHelper->getValetOrderSmsUrl($order->getIncrementId()));
        //if this is mobile, send SMS
        if ($this->_smsHelper->validateChinaMobile($mobile)) {
            //create sms log object
            $smsLog = $this->_logFactory->create();
            $smsLog->setData('number', $mobile)
                ->setData('action_type', \D1m\Wesms\Helper\Sms::SMS_ACTION_INVOICE_URL_UPDATE);
            $this->_smsHelper->sendSms($smsLog, $order);
        }
        return $this;
    }
}
