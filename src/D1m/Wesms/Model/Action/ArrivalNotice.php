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
 * Class ArrivalNotice
 * @package D1m\Wesms\Model\Action
 */
class ArrivalNotice extends AbstractModel
{
    /**
     * @var mixed
     */
    protected $_param;

    /**
     * @var \D1m\Wesms\Model\TemplateFactory
     */
    protected $_templateFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_dateTimeFormater;

    /**
     * ArrivalNotice constructor.
     * @param \D1m\Wesms\Model\TemplateFactory $templateFactory
     * @param \D1m\Wesms\Helper\Data $weSmsHelper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTimeFormater
     * @param \Magento\Framework\Stdlib\DateTime\Timezone $timezone
     * @param array $data
     */
    public function __construct(
        \D1m\Wesms\Model\TemplateFactory $templateFactory,
        \D1m\Wesms\Helper\Data $weSmsHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTimeFormater,
        \Magento\Framework\Stdlib\DateTime\Timezone $timezone,
        array $data = []
    ) {
        $this->_templateFactory = $templateFactory;
        $this->_dateTimeFormater = $dateTimeFormater;
        parent::__construct($weSmsHelper, $timezone, $data);
    }

    /**
     * @param $param
     * @return $this
     */
    public function setParam($param)
    {
        $this->_param = mb_strlen($param, 'utf-8') <= 20 ? $param : mb_substr($param, 0, 17, 'utf-8').'...';
        return $this;
    }

    /**
     * @return mixed
     */
    public function getParam()
    {
        return $this->_param;
    }

    /**
     * Get whether send this type SMS config
     * @return bool
     */
    protected function _enableSendSMS()
    {
        return $this->_getStoreConfig('sms_setting/sms_template/arrival_notice_enable');
    }

    /**
     * Get SMS Template
     * @return mixed
     */
    protected function _getSMSTemplate()
    {
        if ($this->_enableSendSMS())
        {
            return $this->_getStoreConfig('sms_setting/sms_template/arrival_notice_sms_template');
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
            return $this->_getStoreConfig('sms_setting/sms_template/arrival_notice_sms_template_code');
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

        //if no product for this type sms notify
        if (is_null($this->getParam()))
        {
            return '';
        }

        //get template
        $template = $this->_templateFactory->create();
        $template->getResource()->load($template, $this->_getSMSTemplate());

        if ($template && $template->getId())
        {
            $variables = array(
                'title' => $this->getParam(),
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
        $param = ['title' => $this->getParam()];

        return json_encode($param,JSON_UNESCAPED_UNICODE);
    }
}
