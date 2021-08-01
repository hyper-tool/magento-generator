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
 * Class ArrivalNoticeRequest
 * @package D1m\Wesms\Model\Action
 */
class ArrivalNoticeRequest extends AbstractModel
{
    /**
     * Product
     *
     * @var \Magento\Catalog\Model\Product
     */
    protected $_product;

    /**
     * @var \D1m\Wesms\Model\Action\ValidateCode\ArrivalNoticeRequest
     */
    protected $_arrivalNoticeRequestValidation;

    /**
     * @var \D1m\Wesms\Model\TemplateFactory
     */
    protected $_templateFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_dateTimeFormater;

    /**
     * ArrivalNoticeRequest constructor.
     * @param ValidateCode\ArrivalNoticeRequest $arrivalNoticeRequestValidation
     * @param \D1m\Wesms\Model\TemplateFactory $templateFactory
     * @param \D1m\Wesms\Helper\Data $weSmsHelper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTimeFormater
     * @param \Magento\Framework\Stdlib\DateTime\Timezone $timezone
     * @param array $data
     */
    public function __construct(
        \D1m\Wesms\Model\Action\ValidateCode\ArrivalNoticeRequest $arrivalNoticeRequestValidation,
        \D1m\Wesms\Model\TemplateFactory $templateFactory,
        \D1m\Wesms\Helper\Data $weSmsHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTimeFormater,
        \Magento\Framework\Stdlib\DateTime\Timezone $timezone,
        array $data = []
    ) {
        $this->_arrivalNoticeRequestValidation = $arrivalNoticeRequestValidation;
        $this->_templateFactory = $templateFactory;
        $this->_dateTimeFormater = $dateTimeFormater;

        parent::__construct($weSmsHelper, $timezone, $data);
    }

    /**
     * Set product
     * @param $product
     * @return $this
     */
    public function setProduct($product)
    {
        $this->_product = $product;
        return $this;
    }

    /**
     * Get product
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        return $this->_product;
    }

    /**
     * Get whether send this type SMS config
     * @return bool
     */
    protected function _enableSendSMS()
    {
        return $this->_getStoreConfig('sms_setting/sms_template/arrival_notice_request_enable');
    }

    /**
     * Get SMS Template
     * @return mixed
     */
    protected function _getSMSTemplate()
    {
        if ($this->_enableSendSMS())
        {
            return $this->_getStoreConfig('sms_setting/sms_template/arrival_notice_request_sms_template');
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
            return $this->_getStoreConfig('sms_setting/sms_template/arrival_notice_request_sms_template_code');
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
        if (is_null($this->getProduct()))
        {
            $this->_product = '';
        }

        $mobile = trim($this->getMobile());
        $this->_arrivalNoticeRequestValidation->setTimeout($this->getLifetime());
        $this->_arrivalNoticeRequestValidation->setMobile($mobile);
        $this->_arrivalNoticeRequestValidation->generateWord();

        //get template
        $template = $this->_templateFactory->create();
        $template->getResource()->load($template, $this->_getSMSTemplate());

        if ($template && $template->getId())
        {
            $date = $this->_dateTimeFormater->date('Y-m-d');
            $variables = array(
                'validate'        => $this->_arrivalNoticeRequestValidation->setData(array('code'=>$this->_arrivalNoticeRequestValidation->getWord($mobile))),
                'product'         => $this->getProduct(),
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
            'code' => $this->_arrivalNoticeRequestValidation->getWord($mobile),
            'product' => $this->getProduct()->getName(),
            'date' => $date = $this->_dateTimeFormater->date('Y-m-d')
        ];

        return json_encode($param,JSON_UNESCAPED_UNICODE);
    }
}
