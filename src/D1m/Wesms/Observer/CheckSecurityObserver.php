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
 * Class CheckSecurityObserver
 * Security check for sms send request
 * @package D1m\Wesms\Observer
 */
class CheckSecurityObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\ActionFlag
     */
    protected $_actionFlag;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \Magento\Captcha\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Captcha\Observer\CaptchaStringResolver
     */
    protected $captchaStringResolver;

    /**
     * @var \D1m\Wesms\Model\Security
     */
    protected $_security;

    /**
     * @var \D1m\Wesms\Helper\Sms
     */
    protected $_smsHelper;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var mixed
     */
    public $stream;

    /**
     * @var \Magento\Customer\Model\Session $customerSession
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cache;

    /**
     * max check times
     */
    const MAX_NOTICE_CHECK_TIME = 10;

    /**
     * @var int
     */
    private $cacheLifetime = 86400;

    /**
     * @var bool
     */
    private $useCache = true;

    /**
     * CheckSecurityObserver constructor.
     * @param \Magento\Framework\App\ActionFlag $actionFlag
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Captcha\Helper\Data $helper
     * @param \D1m\Wesms\Helper\Sms $smsHelper
     * @param \D1m\Wesms\Model\Security $security
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\App\CacheInterface $cache
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Captcha\Observer\CaptchaStringResolver $captchaStringResolver
     */
    public function __construct(
        \Magento\Framework\App\ActionFlag $actionFlag,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Captcha\Helper\Data $helper,
        \D1m\Wesms\Helper\Sms $smsHelper,
        \D1m\Wesms\Model\Security $security,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Captcha\Observer\CaptchaStringResolver $captchaStringResolver
    )
    {
        $this->_actionFlag           = $actionFlag;
        $this->jsonHelper            = $jsonHelper;
        $this->_helper               = $helper;
        $this->_smsHelper            = $smsHelper;
        $this->_security             = $security;
        $this->customerSession       = $customerSession;
        $this->resultJsonFactory     = $resultJsonFactory;
        $this->captchaStringResolver = $captchaStringResolver;
        $this->cache                 = $cache;
        $this->stream                = file_get_contents('php://input');
    }

    /**
     * Check Captcha On User Login Page
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Framework\App\Action\Action $controller */
        $controller = $observer->getControllerAction();

        $stream  = $this->conversionToArray();
        $mobile  = isset($stream['mobile']) ? $stream['mobile'] : '';
        $type    = isset($stream['type']) ? $stream['type'] : '';
        $captcha = isset($stream['captcha']) ? $stream['captcha'] : '';
        $result  = [];

        if (empty($captcha)) {
            $result['code']  = 0;
            $result['message'] = __('Empty Image CAPTCHA');
            $this->_actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
            return $controller->getResponse()->representJson($this->jsonHelper->jsonEncode($result));
        }

        if (!preg_match(\D1m\Wesms\Helper\Data::MOBILE_REGEX, $mobile)) {
            $result['code']  = 1004;
            $result['message'] = __('Please input a valid mobile.')->__toString();
            $this->_actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
            return $controller->getResponse()->representJson($this->jsonHelper->jsonEncode($result));
        }

        //can send sms types check
        if (!in_array($type, $this->_smsHelper->canSendTypes())) {
            $result['code']  = 1003;
            $result['message'] = __('We can not send this type message');
            $this->_actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
            return $controller->getResponse()->representJson($this->jsonHelper->jsonEncode($result));
        }

        if ($type == 'arrival_notice_request') {
            $checkResult = $this->_security->arrivalNoticeCheckTimeLimit($mobile);
            if (!$checkResult) {
                $result['code']  = 0;
                $result['message'] = __('Mobile phone number to send request too much');
                $this->_actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
                return $controller->getResponse()->representJson($this->jsonHelper->jsonEncode($result));
            }
        }

        //type message type check
        $formId = '';
        if ($type == 'forget_password') {
            $formId = 'forgotpassword_by_mobile';
        } elseif ($type == 'register') {
            $formId = 'created_account_by_mobile';
        } elseif ($type == 'bind') {
            $formId = 'created_account_by_mobile';
        } elseif ($type == 'invoice') {
            $formId = 'invoice';
        } elseif ($type == 'login_by_mobile') {
            $formId = 'login_by_mobile';
        } elseif ($type == 'forgotpassword_by_mobile') {
            $formId = 'forgotpassword_by_mobile';
        } elseif ($type == 'arrival_notice_request') {
            $formId = 'arrival_notice_request';
        }

        $captchaModel = $this->_helper->getCaptcha($formId);
        if ($captchaModel->isRequired()) {
            if (!$captchaModel->isCorrect($captcha)) {
                $result['code']  = 1006;
                $result['message'] = __('Incorrect Image CAPTCHA');
                $this->_actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
                return $controller->getResponse()->representJson($this->jsonHelper->jsonEncode($result));
            }
        }

        //4)check all security
        /* @var $security \D1m\Wesms\Model\Security */
        $checkResult = $this->_security->checkSecurity($mobile);
        if (isset($checkResult['error']) && $checkResult['error'] == true) {
            $result['code']  = 1007;
            $result['message'] = $checkResult['message'];
            $this->_actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
            return $controller->getResponse()->representJson($this->jsonHelper->jsonEncode($result));
        }

        return $this;
    }

    /**
     * @param null $testData
     * @return array|mixed
     */
    public function conversionToArray($testData = null)
    {
        try {
            if ($testData) {
                return \Zend_Json::decode($testData, true);
            }
            if ($this->stream) {
                return \Zend_Json::decode($this->stream, true);
            } else {
                return [];
            }
        } catch (\Exception $e) {
            return [];
        }
    }

}
