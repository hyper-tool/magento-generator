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

namespace D1m\Wesms\Model\Action\ValidateCode;

/**
 * Class Adapter
 * SMS captcha validate
 * @package D1m\Wesms\Model\Action\ValidateCode
 */
class Adapter
{
    /**
     * Register validate type
     */
    const VALIDATE_CODE_TYPE_REGISTER = 'register';

    /**
     * Invoice validate type
     */
    const VALIDATE_CODE_TYPE_INVOICE = 'invoice';

    /**
     * Forget password validate Type
     */
    const VALIDATE_CODE_TYPE_FORGET = 'forget';

    /**
     * Edit mobile validate Type
     */
    const VALIDATE_CODE_TYPE_EDITMOBILE = 'edit_mobile';

    /**
     * Login by mobile validate Type
     */
    const VALIDATE_CODE_TYPE_LOGINBYMOBILE = 'login_by_mobile';

    /**
     * Bind validate Type
     */
    const VALIDATE_CODE_TYPE_BIND = 'bind';

    /**
     * Arrival notice validate Type
     */
    const VALIDATE_CODE_TYPE_ARRIVALNOTICE = 'arrival_notice';

    /**
     * Arrival notice request validate Type
     */
    const VALIDATE_CODE_TYPE_ARRIVALNOTICEREQUEST = 'arrival_notice_request';

    /**
     * @var \D1m\Wesms\Model\Action\ValidateCode\ForgetPwdFactory
     */
    protected $_forgetPwdFactory;

    /**
     * @var \D1m\Wesms\Model\Action\ValidateCode\RegisterFactory
     */
    protected $_registerFactory;

    /**
     * @var \D1m\Wesms\Model\Action\ValidateCode\InvoiceFactory
     */
    protected $_invoiceFactory;

    /**
     * @var \D1m\Wesms\Model\Action\ValidateCode\EditMobileFactory
     */
    protected $_editMobileFactory;

    /**
     * @var \D1m\Wesms\Model\Action\ValidateCode\LoginByMobileFactory
     */
    protected $_loginByMobileFactory;

    /**
     * @var \D1m\Wesms\Model\Action\ValidateCode\BindFactory
     */
    protected $_bindFactory;

    /**
     * @var \D1m\Wesms\Model\Action\ValidateCode\ArrivalNoticeFactory
     */
    protected $_arrivalNoticeFactory;

    /**
     * @var
     */
    protected $_registerValidateCodeGeneration;

    /**
     * Adapter constructor.
     * @param ForgetPwdFactory $forgetPwdFactory
     * @param RegisterFactory $registerFactory
     * @param InvoiceFactory $invoiceFactory
     * @param EditMobileFactory $editMobileFactory
     * @param LoginByMobileFactory $loginByMobileFactory
     * @param ArrivalNoticeFactory $arrivalNoticeFactory
     * @param ArrivalNoticeRequestFactory $arrivalNoticeRequestFactory
     * @param array $data
     */
    public function __construct(
        \D1m\Wesms\Model\Action\ValidateCode\ForgetPwdFactory $forgetPwdFactory,
        \D1m\Wesms\Model\Action\ValidateCode\RegisterFactory $registerFactory,
        \D1m\Wesms\Model\Action\ValidateCode\InvoiceFactory $invoiceFactory,
        \D1m\Wesms\Model\Action\ValidateCode\EditMobileFactory $editMobileFactory,
        \D1m\Wesms\Model\Action\ValidateCode\LoginByMobileFactory $loginByMobileFactory,
        \D1m\Wesms\Model\Action\ValidateCode\BindFactory $bindFactory,
        \D1m\Wesms\Model\Action\ValidateCode\ArrivalNoticeFactory $arrivalNoticeFactory,
        \D1m\Wesms\Model\Action\ValidateCode\ArrivalNoticeRequestFactory $arrivalNoticeRequestFactory,
        array $data = []
    )
    {
        $this->_forgetPwdFactory            = $forgetPwdFactory;
        $this->_registerFactory             = $registerFactory;
        $this->_invoiceFactory              = $invoiceFactory;
        $this->_editMobileFactory           = $editMobileFactory;
        $this->_loginByMobileFactory        = $loginByMobileFactory;
        $this->_bindFactory                 = $bindFactory;
        $this->_arrivalNoticeFactory        = $arrivalNoticeFactory;
        $this->_arrivalNoticeRequestFactory = $arrivalNoticeRequestFactory;
    }

    /**
     * Sms adapter
     * @param $validateType
     * @param array $params
     * @return \D1m\Wesms\Model\Action\ValidateCode\AbstractModel
     */
    public function factory($validateType, $params = array())
    {
        switch ($validateType) {
            case self::VALIDATE_CODE_TYPE_FORGET:
                $instance = $this->_forgetPwdFactory->create($params);
                break;
            case self::VALIDATE_CODE_TYPE_REGISTER:
                $instance = $this->_registerFactory->create($params);
                break;
            case self::VALIDATE_CODE_TYPE_INVOICE:
                $instance = $this->_invoiceFactory->create($params);
                break;
            case self::VALIDATE_CODE_TYPE_EDITMOBILE:
                $instance = $this->_editMobileFactory->create($params);
                break;
            case self::VALIDATE_CODE_TYPE_LOGINBYMOBILE:
                $instance = $this->_loginByMobileFactory->create($params);
                break;
            case self::VALIDATE_CODE_TYPE_ARRIVALNOTICE:
                $instance = $this->_arrivalNoticeFactory->create($params);
                break;
            case self::VALIDATE_CODE_TYPE_BIND:
                $instance = $this->_bindFactory->create($params);
                break;
            case self::VALIDATE_CODE_TYPE_ARRIVALNOTICEREQUEST:
                $instance = $this->_arrivalNoticeRequestFactory->create($params);
                break;

            default:
                $instance = $this->_registerFactory->create($params);
                break;
        }
        return $instance;
    }

    /**
     * Sms adapter
     * @param $validateType
     * @param array $params
     * @return \D1m\Wesms\Model\Action\ValidateCode\AbstractModel
     */
    public static function weChatFactory($validateType, $params = array())
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        switch ($validateType) {
            case self::VALIDATE_CODE_TYPE_REGISTER:
                /* @var  $model \D1m\Wesms\Model\Action\ValidateCode\Register */
                $instance = $objectManager->create('D1m\Wesms\Model\Action\ValidateCode\Register', $params);
                break;
            case self::VALIDATE_CODE_TYPE_EDITMOBILE:
                $instance = $objectManager->create('D1m\Wesms\Model\Action\ValidateCode\EditMobile', $params);
                break;
            default:
                $instance = $objectManager->create('D1m\Wesms\Model\Action\ValidateCode\Register', $params);
        }
        return $instance;
    }
}