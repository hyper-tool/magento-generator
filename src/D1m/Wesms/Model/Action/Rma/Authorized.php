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

namespace D1m\Wesms\Model\Action\Rma;

/**
 * Class Authorized
 * Rma Authorized SMS
 * @package D1m\Wesms\Model\Action\Rma
 */
class Authorized extends \D1m\Wesms\Model\Action\AbstractModel
{

    /**
     * Sales order
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;

    /**
     * Rma
     * @var \Magento\Rma\Model\Rma
     */
    protected $_rma;

    /**
     * @var \D1m\Wesms\Model\TemplateFactory
     */
    protected $_templateFactory;

    /**
     * Authorized constructor.
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
     * Set rma
     * @param \Magento\Rma\Model\Rma
     * @return $this
     */
    public function setRma($rma)
    {
        $this->_rma = $rma;
        return $this;
    }

    /**
     * Get rma
     * @return \Magento\Rma\Model\Rma
     */
    public function getRma()
    {
        return $this->_rma;
    }

    /**
     * Get whether send this type SMS config
     * @return bool
     */
    protected function _enableSendSMS()
    {
        return $this->_getStoreConfig('sms_setting/sms_template/rma_authorized_enable');
    }

    /**
     * Get config SMS Template ID
     * @return bool|void
     */
    protected function _getSMSTemplate()
    {
        if ($this->_enableSendSMS()) {
            return $this->_getStoreConfig('sms_setting/sms_template/rma_authorized_sms_template');
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
            return $this->_getStoreConfig('sms_setting/sms_template/rma_authorized_sms_template_code');
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
        if (is_null($this->_getSMSTemplate())) {
            return '';
        }
        //if no order for this type sms notify
        if (is_null($this->getOrder())) {
            return '';
        }
        //if no rma for this type sms notify
        if (is_null($this->getRma())) {
            return '';
        }

        //get template
        $template = $this->_templateFactory->create();
        $template->getResource()->load($template, $this->_getSMSTemplate());

        if ($template && $template->getId()) {
            $variables = array(
                'order' => $this->getOrder(),
                'rma' => $this->getRma()
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
        $param = [
            'orderId' => $order->getIncrementId()
        ];

        return json_encode($param,JSON_UNESCAPED_UNICODE);
    }
}