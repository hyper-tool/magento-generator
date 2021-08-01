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
use Magento\Customer\Model\Session;
use Magento\Customer\Api\CustomerRepositoryInterface;

/**
 * Class CheckValidCodeObserver
 * Validate SMS captcha code
 * @package D1m\Wesms\Observer
 */
class CheckValidCodeObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\ActionFlag
     */
    protected $_actionFlag;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $_session;

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $_customerRepository;

    /**
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlManager;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirect;

    /**
     * @var array
     */
    protected $actions = array(
        'createbymobilepost'         => 'createbymobile',
        'loginbymobilepost'          => 'login',
        'forgotpasswordbymobilepost' => 'forgotpasswordbymobile',
        'resetpasswordbymobilepost'  => 'resetpasswordbymobile',
        'forgotpasswordpost'         => 'forgotpassword',
        'createpost'                 => 'create',
        'checkpost'                  => 'check',
        'bindingpost'                => 'binding',
        'arrivalnoticepost'          => 'arrivalnotice',
        'edit'                       => 'edit',
        'invoice'                    => 'invoice'
    );

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \D1m\Wesms\Model\Action\ValidateCode\AdapterFactory
     */
    protected $_adapterFactory;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var mixed
     */
    public $stream;

    /**
     * CheckValidCodeObserver constructor.
     * @param \Magento\Framework\App\ActionFlag $actionFlag
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param \Magento\Framework\UrlInterface $urlManager
     * @param \D1m\Wesms\Model\Action\ValidateCode\AdapterFactory $adapterFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param Session $customerSession
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        \Magento\Framework\App\ActionFlag $actionFlag,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magento\Framework\UrlInterface $urlManager,
        \D1m\Wesms\Model\Action\ValidateCode\AdapterFactory $adapterFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        Session $customerSession,
        CustomerRepositoryInterface $customerRepository
    )
    {
        $this->_actionFlag         = $actionFlag;
        $this->messageManager      = $messageManager;
        $this->_session            = $session;
        $this->_urlManager         = $urlManager;
        $this->redirect            = $redirect;
        $this->resultJsonFactory   = $resultJsonFactory;
        $this->jsonHelper          = $jsonHelper;
        $this->_scopeConfig        = $scopeConfig;
        $this->_adapterFactory     = $adapterFactory;
        $this->_customerSession    = $customerSession;
        $this->_customerRepository = $customerRepository;
        $this->stream              = file_get_contents('php://input');
    }

    /**
     * Check Validate SMS captcha code On User register page pr forgot password page pr mobile login page
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        //Only registered message opens, is used
        if (!$this->_enableRegSendSMS()) {
            return $this;
        }

        //mobile login
        if (!$this->_enableMobileLoginSendSMS()) {
            return $this;
        }

        /** @var \Magento\Framework\App\Action\Action $controller */
        $controller = $observer->getControllerAction();

        $stream = $this->conversionToArray();
        $type   = isset($stream['authorizationType']) ? $stream['authorizationType'] : '';
        $code   = isset($stream['authorizationCode']) ? $stream['authorizationCode'] : '';
        $mobile = isset($stream['mobile']) ? $stream['mobile'] : '';
        $action = str_replace('Post', '', $controller->getRequest()->getActionName());

        try {
            if (empty($this->actions[$action])) {
                throw new \Exception(__('Action Name not legal'));
            }
            if (!$type) {
                throw new \Exception(__('Verify Type is Empty!'));
            }

            $canSkip = false;
            //only when edit mobile needs to validate
            if ($type == 'edit_mobile') {
                $canSkip = $this->needEditMobileValidateCodeCheck($mobile);
            }

            if (!$canSkip) {
                $model = $this->_adapterFactory->create()->factory($type, array('formId' => $type));
                if (strlen($model->getWord($mobile)) && $code && $model->isCorrect($code, $mobile)) {
                    //pass checked
                    $model->setPassCheck(true);
                    $model->clearWord();
                } else {
                    $errorMsg = !strlen($code) ? __('Empty SMS CAPTCHA') : __('Incorrect SMS CAPTCHA');
                    throw new \Exception($errorMsg);
                }
            }
        } catch (\Exception $e) {
            $result = [
                'code'  => 1005,
                'message' => __($e->getMessage()),
                'error'   => $e->getMessage()
            ];
            $this->_actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);

            return $controller->getResponse()->representJson($this->jsonHelper->jsonEncode($result));
        }
        return $this;
    }

    /**
     * Get customer data object
     *
     * @param int $customerId
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    private function getCustomerDataObject($customerId)
    {
        return $this->_customerRepository->getById($customerId);
    }

    /**
     * If the mobile form the request is different with the current mobile, need check the code, otherwise we don't need
     *
     * @param $request
     * @return bool
     */
    protected function needEditMobileValidateCodeCheck($mobile)
    {
        //$mobile = $request->getParam('mobile');
        $customerID = $this->_customerSession->getCustomerId();
        if ($mobile && $customerID) {
            $currentCustomerDataObject = $this->getCustomerDataObject($customerID);
            $currentMobile             = $currentCustomerDataObject->getMobile();
            //if the mobile is the same, not need to check the verify code
            if ($currentMobile == $mobile) {
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }

    }

    /**
     * Enable send SMS Message
     * @return bool
     */
    protected function _enableRegSendSMS()
    {
        return $this->_scopeConfig->getValue('sms_setting/sms_template/register_enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Enable send SMS Message
     * @return bool
     */
    protected function _enableMobileLoginSendSMS()
    {
        return $this->_scopeConfig->getValue('sms_setting/sms_template/mobile_login_enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
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
