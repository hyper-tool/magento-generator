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
namespace D1m\Wesms\Model\Config\Service;

use D1m\Wesms\Model\Config\AbstractModel;

/**
 * Class Wesms
 * Get We sms config data
 * @package D1m\Wesms\Model\Config\Service
 */
class Wesms extends AbstractModel
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $config;

    /**
     * Default web service url
     */
    const API_URL_GATEWAY = "http://www.wemediacn.net/webservice/smsservice.asmx";

    /**
     * Message errors
     * @var array
     */
    private $errors = array();

    /**
     * Wesms constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \D1m\Wesms\Helper\Data $wesmsHelper
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \D1m\Wesms\Helper\Data $wesmsHelper
    )
    {
        $this->config = $config;

        parent::__construct($wesmsHelper);
    }

    /**
     * Get config data
     * @param $url
     * @return mixed
     */
    private function _getStoreConfig($url){
        $value = $this->config->getValue($url, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $value;
    }

    /**
     * Get API URL
     * @return mixed|string
     */
    public function getServiceUrl()
    {
        $apiUrl = $this->_getStoreConfig('sms_setting/sms/service_url');
        return strlen(trim($apiUrl)) ? $apiUrl : self::API_URL_GATEWAY;
    }


    /**
     * Get API token
     * @return mixed|string
     */
    public function getServiceToken()
    {
        return trim($this->_getStoreConfig('sms_setting/sms/service_token'));
    }

    /**
     * Adds error to the list of errors
     * @param $error
     */
    protected function setError($error)
    {
        $this->errors[] = $error;
    }

    /**
     * Returns the last error message
     */
    public function getError()
    {
        return end($this->errors);
    }

    /**
     * Validate
     * @return bool
     */
    public function validate()
    {
        if(!strlen($this->getServiceToken()))
        {
            $this->setError(__('API token can not be empty.'));
            return false;
        }

        return true;
    }
}