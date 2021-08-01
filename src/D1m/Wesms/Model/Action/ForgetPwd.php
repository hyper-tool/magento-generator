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
 * Class ForgetPwd
 * Forget password type SMS
 * @package D1m\Wesms\Model\Action
 */
class ForgetPwd extends AbstractModel
{
    /**
     * @var \D1m\Wesms\Model\Action\ValidateCode\ForgetPwd
     */
    protected $_forgetPwdValidation;

    /**
     * @var \D1m\Wesms\Model\TemplateFactory
     */
    protected $_templateFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_dateTimeFormater;

    /**
     * ForgetPwd constructor.
     * @param ValidateCode\ForgetPwd $forgetPwdValidation
     * @param \D1m\Wesms\Model\TemplateFactory $templateFactory
     * @param \D1m\Wesms\Helper\Data $weSmsHelper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTimeFormater
     * @param \Magento\Framework\Stdlib\DateTime\Timezone $timezone
     * @param array $data
     */
    public function __construct(
        \D1m\Wesms\Model\Action\ValidateCode\ForgetPwd $forgetPwdValidation,
        \D1m\Wesms\Model\TemplateFactory $templateFactory,
        \D1m\Wesms\Helper\Data $weSmsHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTimeFormater,
        \Magento\Framework\Stdlib\DateTime\Timezone $timezone,
        array $data = []
    ) {
        $this->_forgetPwdValidation = $forgetPwdValidation;
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
        return $this->_getStoreConfig('sms_setting/sms_template/forgetpwd_enable');
    }

    /**
     * Get SMS Template
     * @return mixed
     */
    protected function _getSMSTemplate()
    {
        if ($this->_enableSendSMS())
        {
            return $this->_getStoreConfig('sms_setting/sms_template/forgetpwd_sms_template');
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
            return $this->_getStoreConfig('sms_setting/sms_template/forgetpwd_sms_template_code');
        }

        return null;
    }

    /**
     * Get sms content from template
     * @return mixed|string
     */
    public function getSMSContent(){
        //if not sms template was set in the admin
        if (is_null($this->_getSMSTemplate()))
        {
            return '';
        }

        $mobile = trim($this->getMobile());
        $this->_forgetPwdValidation->setTimeout($this->getLifetime());
        $this->_forgetPwdValidation->setMobile($mobile);
        $this->_forgetPwdValidation->generateWord();

        //get template
        $template = $this->_templateFactory->create();
        $template->getResource()->load($template, $this->_getSMSTemplate());

        if ($template && $template->getId())
        {
            $date = $this->_dateTimeFormater->date('Y-m-d');
            $variables = array(
                'validate'        => $this->_forgetPwdValidation->setData(array('code'=>$this->_forgetPwdValidation->getWord($mobile))),
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
            'code' => $this->_forgetPwdValidation->getWord($mobile),
            //'date' => $date = $this->_dateTimeFormater->date('Y-m-d')
        ];

        return json_encode($param,JSON_UNESCAPED_UNICODE);
    }
}
