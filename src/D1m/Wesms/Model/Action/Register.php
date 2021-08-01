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
 * Class Register
 * SMS captcha when registering
 * @package D1m\Wesms\Model\Action
 */
class Register extends AbstractModel
{

    /**
     * @var \D1m\Wesms\Model\Action\ValidateCode\Register
     */
    protected $_registerValidation;

    /**
     * @var \D1m\Wesms\Model\TemplateFactory
     */
    protected $_templateFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_dateTimeFormater;

    /**
     * Register constructor.
     * @param ValidateCode\Register $registerValidation
     * @param \D1m\Wesms\Model\TemplateFactory $templateFactory
     * @param \D1m\Wesms\Helper\Data $weSmsHelper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTimeFormater
     * @param \Magento\Framework\Stdlib\DateTime\Timezone $timezone
     * @param array $data
     */
    public function __construct(
        \D1m\Wesms\Model\Action\ValidateCode\Register $registerValidation,
        \D1m\Wesms\Model\TemplateFactory $templateFactory,
        \D1m\Wesms\Helper\Data $weSmsHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTimeFormater,
        \Magento\Framework\Stdlib\DateTime\Timezone $timezone,
        array $data = []
    ) {
        $this->_registerValidation = $registerValidation;
        $this->_templateFactory = $templateFactory;
        $this->_dateTimeFormater = $dateTimeFormater;

        parent::__construct($weSmsHelper, $timezone, $data);
    }

    /**
     * Get whether send this type SMS config
     * @return bool
     */
    protected function _enableSendSMS()
    {
        return $this->_getStoreConfig('sms_setting/sms_template/register_enable');
    }

    /**
     * Get config SMS Template ID
     * @return mixed
     */
    protected function _getSMSTemplate()
    {
        if ($this->_enableSendSMS())
        {
            return $this->_getStoreConfig('sms_setting/sms_template/register_sms_template');
        }

        return null;
    }

    /**
     * Get SMS Template Code
     * @return mixed
     */
    public function _getSMSTemplateCode()
    {
        if ($this->_enableSendSMS())
        {
            return $this->_getStoreConfig('sms_setting/sms_template/register_sms_template_code');
        }

        return null;
    }

    /**
     * Get SMS Content
     * @return string
     */
    public function getSMSContent()
    {
        //if not sms template was set in the admin
        if (is_null($this->_getSMSTemplate()))
        {
            return '';
        }

        $mobile = trim($this->getMobile());
        $this->_registerValidation->setTimeout($this->getLifetime());
        $this->_registerValidation->setMobile($mobile);
        $this->_registerValidation->generateWord();

        //get template
        $template = $this->_templateFactory->create();
        $template->getResource()->load($template, $this->_getSMSTemplate());

        if ($template && $template->getId())
        {
            $date = $this->_dateTimeFormater->date('Y-m-d');
            $param = $this->getParam();// 如果指定了code，不再从session里再生成一个code
            $variables = array(
                'validate'        => $this->_registerValidation->setData(array('code'=> (!empty($param['code']) ? $param['code'] :  $this->_registerValidation->getWord($mobile)) )),
                'date'            => $date
            );
            $content = $template->filterMessageContent($variables, $template->getContent());

            return $content;
        }

        return '';
    }

    /**
     * Get Json Param
     *
     * @return string
     */
    public function getJsonParam()
    {
        $mobile = trim($this->getMobile());
        $param = [
            'code' => $this->_registerValidation->getWord($mobile),
            //'date' => $date = $this->_dateTimeFormater->date('Y-m-d')
        ];

        return json_encode($param,JSON_UNESCAPED_UNICODE);
    }
}
