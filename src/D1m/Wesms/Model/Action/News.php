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
 * Class News
 * Create order notify SMS
 * @package D1m\Wesms\Model\Action
 */
class News extends AbstractModel
{
    /**
     *  sales order
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;

    /**
     * @var \D1m\Wesms\Model\TemplateFactory
     */
    protected $_templateFactory;

    /**
     * News constructor.
     * @param \D1m\Wesms\Model\TemplateFactory $templateFactory
     * @param \D1m\Wesms\Helper\Data $weSmsHelper
     * @param \Magento\Framework\Stdlib\DateTime\Timezone $timezone
     * @param array $data
     */
    public function __construct(
        \D1m\Wesms\Model\TemplateFactory $templateFactory,
        \D1m\Wesms\Helper\Data $weSmsHelper,
        \Magento\Framework\Stdlib\DateTime\Timezone $timezone,
        array $data = []
    ) {
        $this->_templateFactory = $templateFactory;

        parent::__construct($weSmsHelper, $timezone, $data);
    }

    /**
     * Set order
     * @param $order
     * @return $this
     */
    public function setOrder($order)
    {
        $this->_order = $order;
        return $this;
    }

    /**
     * Get order
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * Get whether send this type SMS config
     * @return bool
     */
    protected function _enableSendSMS()
    {
        return $this->_getStoreConfig('sms_setting/sms_template/news_enable');
    }

    /**
     * Get SMS content
     * @return mixed
     */
    protected function _getSMSTemplate()
    {
        if ($this->_enableSendSMS())
        {
            return $this->_getStoreConfig('sms_setting/sms_template/news_sms_template');
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
            return $this->_getStoreConfig('sms_setting/sms_template/news_sms_template_code');
        }

        return null;
    }

    /**
     * Get SMS content
     */
    public function getSMSContent()
    {
        //if not sms template was set in the admin
        if (is_null($this->_getSMSTemplate()))
        {
            return '';
        }

        //if no order for this type sms notify
        if (is_null($this->getOrder()))
        {
            return '';
        }

        //get template by template id
        $template = $this->_templateFactory->create();
        $template->getResource()->load($template, $this->_getSMSTemplate());

        if ($template && $template->getId())
        {
            $variables = array(
                'order'        => $this->getOrder()
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
        $order = $this->getOrder();
        $updatedAtPrc = $this->timezone->date($order->getUpdatedAt(), null)->format('Y-m-d H:i:s');
        $param = [
            'orderId' => $order->getIncrementId(),
            'grandTotal' => $order->getGrandTotal(),
            'updatedAtPrc' => $updatedAtPrc
        ];

        return json_encode($param,JSON_UNESCAPED_UNICODE);
    }
}