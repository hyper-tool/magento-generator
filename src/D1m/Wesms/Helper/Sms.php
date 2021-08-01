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

namespace D1m\Wesms\Helper;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ObjectManager;
/**
 * Class Sms
 * Sms send helper class
 * @package D1m\Wesms\Helper
 */
class Sms extends \Magento\Framework\App\Helper\AbstractHelper
{
    /***
     *  define Send SMS Message Action
     */
    const SMS_ACTION_DEFAULT                  = 10;
    const SMS_ACTION_REGISTER                 = 1;
    const SMS_ACTION_FORGETPWD                = 2;
    const SMS_ACTION_PAY                      = 3;
    const SMS_ACTION_SHIP                     = 4;
    const SMS_ACTION_REFUND                   = 5;
    const SMS_ACTION_NEWS                     = 6;
    const SMS_ACTION_COMPLETE                 = 7;
    const SMS_ACTION_CANCELED                 = 8;
    const SMS_ACTION_REMIND                   = 9;
    const SMS_ACTION_RMA_APPROVED             = 11;
    const SMS_ACTION_RMA_AUTHORIZED           = 12;
    const SMS_ACTION_RMA_RECEIVED             = 13;
    const SMS_ACTION_PROCESSING               = 14;
    const SMS_ACTION_SHIPPED                  = 15;
    const SMS_ACTION_REFUNDED                 = 16;
    const SMS_ACTION_PENDING_PAYMENT          = 17;
    const SMS_ACTION_EDIT_MOBILE              = 18;
    const SMS_ACTION_REGISTRATION_WELCOME     = 19;
    const SMS_ACTION_LOGIN_BY_MOBILE          = 20;
    const SMS_ACTION_FORGOTPASSWORD_BY_MOBILE = 21;
    const SMS_ACTION_ARRIVAL_NOTICE           = 22;
    const SMS_ACTION_ARRIVAL_NOTICE_REQUEST   = 23;
    const SMS_ACTION_INVOICE                  = 24;
    const SMS_ACTION_BIND                     = 25;
    const SMS_ACTION_REGISTER_SUCCESS         = 26;
    const SMS_ACTION_INVOICE_URL              = 27;
    const SMS_ACTION_INVOICE_URL_UPDATE       = 28;
    const SMS_ACTION_PICKUP_REMIND            = 29;
    const SMS_ACTION_ORDER_RECEIVING          = 30;
    const SMS_ACTION_ORDER_PICK_UP_REMINDER   = 31;
    const SMS_ACTION_QUOTE_REMIND   = 32;

    /***
     * @var array
     */
    static protected $_actionModelClasses = array();

    /**
     *  can send or not
     * @var
     */
    protected $_canSend;

    /**
     * @var \D1m\Wesms\Model\Adapter
     */
    protected $_adapter;

    /***
     * message tips
     *
     * @var array
     */
    private $messages = array();

    /**
     * @var \D1m\Wesms\Helper\Data
     */
    protected $_dataHelper;

    /**
     * Sms constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param Data $dataHelper
     * @param \D1m\Wesms\Model\AdapterFactory $adapterFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \D1m\Wesms\Helper\Data $dataHelper,
        \D1m\Wesms\Model\AdapterFactory $adapterFactory
    )
    {
        parent::__construct($context);

        $this->_adapter    = $adapterFactory->create();
        $this->_dataHelper = $dataHelper;

        //sms types
        self::$_actionModelClasses = self::$_actionModelClasses + array(
                self::SMS_ACTION_REGISTER                 => '\D1m\Wesms\Model\Action\Register',
                self::SMS_ACTION_REGISTER_SUCCESS         => '\D1m\Wesms\Model\Action\CustomerCreateSuccess',
                self::SMS_ACTION_INVOICE                  => '\D1m\Wesms\Model\Action\Invoice',
                self::SMS_ACTION_INVOICE_URL              => '\D1m\Wesms\Model\Action\InvoiceUrl',
                self::SMS_ACTION_INVOICE_URL_UPDATE       => '\D1m\Wesms\Model\Action\InvoiceUrlUpdate',
                self::SMS_ACTION_PICKUP_REMIND            => '\D1m\Wesms\Model\Action\PickupRemind',
                self::SMS_ACTION_ARRIVAL_NOTICE           => '\D1m\Wesms\Model\Action\ArrivalNotice',
                self::SMS_ACTION_ARRIVAL_NOTICE_REQUEST   => '\D1m\Wesms\Model\Action\ArrivalNoticeRequest',
                self::SMS_ACTION_FORGETPWD                => '\D1m\Wesms\Model\Action\ForgetPwd',
                self::SMS_ACTION_PAY                      => '\D1m\Wesms\Model\Action\Pay',
                self::SMS_ACTION_SHIP                     => '\D1m\Wesms\Model\Action\Ship',
                self::SMS_ACTION_REFUND                   => '\D1m\Wesms\Model\Action\Refund',
                self::SMS_ACTION_NEWS                     => '\D1m\Wesms\Model\Action\News',
                self::SMS_ACTION_COMPLETE                 => '\D1m\Wesms\Model\Action\Complete',
                self::SMS_ACTION_CANCELED                 => '\D1m\Wesms\Model\Action\Canceled',
                self::SMS_ACTION_REMIND                   => '\D1m\Wesms\Model\Action\Remind',
                self::SMS_ACTION_RMA_APPROVED             => '\D1m\Wesms\Model\Action\Rma\Approved',
                self::SMS_ACTION_RMA_AUTHORIZED           => '\D1m\Wesms\Model\Action\Rma\Authorized',
                self::SMS_ACTION_RMA_RECEIVED             => '\D1m\Wesms\Model\Action\Rma\Received',
                self::SMS_ACTION_PROCESSING               => '\D1m\Wesms\Model\Action\Processing',
                self::SMS_ACTION_SHIPPED                  => '\D1m\Wesms\Model\Action\Shipped',
                self::SMS_ACTION_REFUNDED                 => '\D1m\Wesms\Model\Action\Refunded',
                self::SMS_ACTION_PENDING_PAYMENT          => '\D1m\Wesms\Model\Action\PendingPayment',
                self::SMS_ACTION_EDIT_MOBILE              => '\D1m\Wesms\Model\Action\EditMobile',
                self::SMS_ACTION_REGISTRATION_WELCOME     => '\D1m\Wesms\Model\Action\RegistrationWelcome',
                self::SMS_ACTION_LOGIN_BY_MOBILE          => '\D1m\Wesms\Model\Action\LoginByMobile',
                self::SMS_ACTION_BIND                     => '\D1m\Wesms\Model\Action\Bind',
                self::SMS_ACTION_FORGOTPASSWORD_BY_MOBILE => '\D1m\Wesms\Model\Action\ForgotpasswordByMobile',
                self::SMS_ACTION_ORDER_RECEIVING          => '\D1m\Wesms\Model\Action\OrderReceiving',
                self::SMS_ACTION_ORDER_PICK_UP_REMINDER   => '\D1m\Wesms\Model\Action\PickupReminder',
                self::SMS_ACTION_QUOTE_REMIND                 => '\D1m\Wesms\Model\Action\QuoteRemind',
            );

    }

    /**
     * Adds error to the list of message
     * @param $message
     */
    protected function setMessage($message)
    {
        $this->messages[] = $message;
    }

    /**
     * Returns the last error message
     */
    public function getMessage()
    {
        return end($this->messages);
    }

    /**
     * Returns all the message concatenated with the $newline string
     *
     * @param string $newline
     * @return string
     */
    public function getMessages($newline = "\n")
    {
        return implode($newline, $this->messages);
    }

    /**
     * whether can send types
     * @param null $type
     * @return array|null
     */
    static public function canSendTypes($type = null)
    {
        $types = array(
            self::SMS_ACTION_REGISTER                 => 'register',
            self::SMS_ACTION_INVOICE                  => 'invoice',
            self::SMS_ACTION_FORGETPWD                => 'forget_password',
            self::SMS_ACTION_EDIT_MOBILE              => 'edit_mobile',
            self::SMS_ACTION_LOGIN_BY_MOBILE          => 'login_by_mobile',
            self::SMS_ACTION_BIND                     => 'bind',
            self::SMS_ACTION_ARRIVAL_NOTICE           => 'arrival_notice',
            self::SMS_ACTION_ARRIVAL_NOTICE_REQUEST   => 'arrival_notice_request',
            self::SMS_ACTION_FORGOTPASSWORD_BY_MOBILE => 'forgotpassword_by_mobile'
        );

        if (!is_null($type)) {
            if (isset($types[$type])) {
                return $types[$type];
            }
            return null;
        }

        return $types;
    }

    /**
     * Get action type class
     * @param null $type
     * @return array|mixed|null
     */
    static public function getActionTypeLabel($type = null)
    {
        $actionLabels = array(
            self::SMS_ACTION_REGISTER                 => __('SMS for register'),
            self::SMS_ACTION_REGISTER_SUCCESS         => __('SMS for register success'),
            self::SMS_ACTION_INVOICE                  => __('SMS for invoice'),
            self::SMS_ACTION_INVOICE_URL              => __('SMS for invoice url'),
            self::SMS_ACTION_INVOICE_URL_UPDATE       => __('SMS for invoice url update'),
            self::SMS_ACTION_INVOICE_URL_UPDATE       => __('SMS for pickup remind'),
            self::SMS_ACTION_FORGETPWD                => __('SMS for forgot password'),
            self::SMS_ACTION_PAY                      => __('SMS for order payment notify'),
            self::SMS_ACTION_SHIP                     => __('SMS for order shipping'),
            self::SMS_ACTION_REFUND                   => __('SMS for order return'),
            self::SMS_ACTION_DEFAULT                  => __('SMS in default'),
            self::SMS_ACTION_RMA_APPROVED             => __('SMS for rma approved'),
            self::SMS_ACTION_RMA_AUTHORIZED           => __('SMS for rma authorized'),
            self::SMS_ACTION_RMA_RECEIVED             => __('SMS for rma received'),
            self::SMS_ACTION_PROCESSING               => __('SMS for order processing'),
            self::SMS_ACTION_SHIPPED                  => __('SMS for order shipped'),
            self::SMS_ACTION_REFUNDED                 => __('SMS for order refunded'),
            self::SMS_ACTION_PENDING_PAYMENT          => __('SMS for order pending payment'),
            self::SMS_ACTION_EDIT_MOBILE              => __('SMS for user edit mobile'),
            self::SMS_ACTION_ARRIVAL_NOTICE           => __('SMS for user arrival notice'),
            self::SMS_ACTION_ARRIVAL_NOTICE_REQUEST   => __('SMS for user arrival notice request'),
            self::SMS_ACTION_REGISTRATION_WELCOME     => __('SMS for user registration'),
            self::SMS_ACTION_LOGIN_BY_MOBILE          => __('SMS for user login by mobile'),
            self::SMS_ACTION_BIND                     => __('SMS for user bind'),
            self::SMS_ACTION_FORGOTPASSWORD_BY_MOBILE => __('SMS for user forgotpassword by mobile'),
            self::SMS_ACTION_NEWS                     => __('SMS for new order'),
            self::SMS_ACTION_COMPLETE                 => __('SMS for order complete'),
            self::SMS_ACTION_CANCELED                 => __('SMS for order canceled'),
            self::SMS_ACTION_REMIND                   => __('SMS for order remind'),
            self::SMS_ACTION_INVOICE                  => __('SMS for order invoice'),
            self::SMS_ACTION_INVOICE_URL              => __('SMS for order invoice url '),
            self::SMS_ACTION_INVOICE_URL_UPDATE       => __('SMS for order invoice url update'),
            self::SMS_ACTION_PICKUP_REMIND            => __('SMS for order pickup remind'),
            self::SMS_ACTION_ORDER_RECEIVING          => __('SMS for order receiving'),
            self::SMS_ACTION_ORDER_PICK_UP_REMINDER   => __('SMS for order pick up reminder'),
            self::SMS_ACTION_QUOTE_REMIND   => __('SMS for Quote reminder'),
        );

        if (!is_null($type)) {
            if (isset($actionLabels[$type])) {
                return $actionLabels[$type];
            }
            return null;
        }

        return $actionLabels;
    }

    /**
     *  get default SMS Code
     */
    public function getDefaultSmsCode()
    {
        $defaultMethod = $this->getConfigValue('sms_methods');
        return $this->_adapter->getSMSMethodCodeByNumber($defaultMethod);
    }

    /**
     * Validate SMS type action and sms log model data
     * @param $smsLog
     * @return bool
     */
    private function validate($smsLog)
    {
        if (!strlen(trim($smsLog->getPlatform())) ||
            !in_array($smsLog->getPlatform(), array_keys(\D1m\Wesms\Model\Adapter::getOptionArray()))
        ) {
            $this->setMessage('No SMS Send Method special!');
            return false;
        }

        if (!$smsLog || !($smsLog instanceof \D1m\Wesms\Model\Log)) {
            return false;
        }

        if (!$smsLog->getNumber()) {
            $this->setMessage(__('Number is not set.'));
            return false;
        }

        if (!strlen(trim($smsLog->getContent()))) {
            $this->setMessage(__('Content is not set.'));
            return false;
        }

        if (!preg_match('/^[0-9]{1,16}$/', $smsLog->getNumber())) {
            $this->setMessage(__("Number '%s' is not valid.", $smsLog->getNumber()));
            return false;
        }

        return true;
    }

    /**
     * send SMS Message
     *
     * @param $smsLog
     * @param null $order
     * @param null $product
     * @param null $rma
     * @param null $customer
     * @param null $param
     * @return array
     */
    public function sendSms($smsLog, $order = null, $product = null, $rma = null, $customer = null, $param = null)
    {
        /**
         * @var  StoreManagerInterface $storeManager
         * @var \Magento\Sales\Model\Order $order
         */
        try {
            $jsonParam = null;

            $smsLog->setPlatform($this->getDefaultSmsCode());

            $actionTypeId = $smsLog->getActionType();
            $contentTpl = $this->getActionInstance($actionTypeId);
            $tplCode = $contentTpl->_getSMSTemplateCode();
            $storeManager = ObjectManager::getInstance()->get(StoreManagerInterface::class);


            $smsLog->setData('store_id', $storeManager->getStore()->getId());

            //sms message content
            if ($order) {
                $contentTpl->setOrder($order);
                $smsLog->setData('store_id', $order->getStoreId());
            }

            if ($product) {
                $contentTpl->setProduct($product);
            }

            if ($rma) {
                $contentTpl->setRma($rma);
            }

            if ($customer) {
                $contentTpl->setCustomer($customer);
            }

            if ($param) {
                $contentTpl->setParam($param);
            }

            //add mobile
            $contentTpl->setMobile($smsLog->getNumber());
            $smsLog->setData('content', $contentTpl->getSMSContent());

            $platform = $smsLog->getPlatform();
            if ($platform == \D1m\Wesms\Model\Adapter::ADAPTER_SMS_ALI) {
                $contentTpl->getSMSContent();
                $jsonParam = $contentTpl->getJsonParam();
                $smsLog->setData('content', $jsonParam);
            }

            if (!$this->validate($smsLog)) {
                return ['status' => false, 'message' => $this->getMessage()];
            }

            $serviceAdapter = $this->_adapter->factory($platform);
            $result         = $serviceAdapter->setSmsService($smsLog)->sendMessage($smsLog->getNumber(), $smsLog->getContent(), $smsLog->isNoticeSms(), $tplCode, $actionTypeId, $jsonParam);

            if (is_array($result) && isset($result['status']) && $result['status'] == true) {
                $smsLog->setStatus(\D1m\Wesms\Model\Log::STATUS_SUCCESS);
                $smsLog->setRemark($result['message']);
            } elseif (isset($result['message']) && $result['message']) {
                $smsLog->setStatus(\D1m\Wesms\Model\Log::STATUS_ERROR);
                $smsLog->setRemark($result['message']);
            }

            $smsLog->setIp($this->_dataHelper->getClientIpAddress());
            $smsLog->getResource()->save($smsLog);

            if (isset($result['status']) && $result['status']) {
                return ['status' => true];
            } else {
                return ['status' => false, 'message' => $smsLog->getRemark()];
            }

        } catch (\Exception $e) {
            $this->_logger->addError($e->getMessage());
            return ['status' => false, 'message' => $e->getMessage()];
        }

    }

    /**
     * Return instance of action wrapper
     *
     * @param $action
     * @param bool $isFactoryName
     * @return mixed|null
     */
    public function getActionInstance($action, $isFactoryName = false)
    {
        $instance = null;
        if ($isFactoryName) {
            $action = array_search($action, self::$_actionModelClasses);
            if (!$action) {
                return null;
            }
        }

        //get the sms object
        if (array_key_exists($action, self::$_actionModelClasses)) {
            $instance = \Magento\Framework\App\ObjectManager::getInstance()->get(self::$_actionModelClasses[$action]);
        }

        return $instance;
    }

    /**
     * Get config data from magento
     * @param $key
     * @return mixed
     */
    public function getConfigValue($key)
    {
        return $this->scopeConfig->getValue('sms_setting/sms/' . $key, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param $key
     * @return mixed
     */
    public function getPaymentConfigValue($key)
    {
        return $this->scopeConfig->getValue('oms_setting/payment/' . $key, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param $mobile
     * @return bool
     */
    public function validateChinaMobile($mobile)
    {
        //if this is mobile, send SMS
        if (preg_match(\D1m\Wesms\Helper\Data::MOBILE_REGEX, $mobile)) {
            return true;
        }
        return false;
    }

}
