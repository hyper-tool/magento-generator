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
namespace D1m\Wesms\Helper;

/**
 * Class Data
 * Public functions for Wesms module
 * @package D1m\Wesms\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * mobile regex, for global
     */
    const MOBILE_REGEX = '/^[0-9.+_-]*$/';
    /**
     * @var \D1m\Wesms\Model\Adapter
     */
    protected $_adapter;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \D1m\Wesms\Model\Adapter $adapter
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \D1m\Wesms\Model\Adapter $adapter
    )
    {
        parent::__construct($context);
        $this->_adapter = $adapter;
    }

    /***
     * Get the client ip address
     * @return string
     */
    function getClientIpAddress()
    {
        if (getenv('HTTP_CLIENT_IP'))
            $ipAddress = getenv('HTTP_CLIENT_IP');
        else if(getenv('HTTP_X_FORWARDED_FOR'))
            $ipAddress = getenv('HTTP_X_FORWARDED_FOR');
        else if(getenv('HTTP_X_FORWARDED'))
            $ipAddress = getenv('HTTP_X_FORWARDED');
        else if(getenv('HTTP_FORWARDED_FOR'))
            $ipAddress = getenv('HTTP_FORWARDED_FOR');
        else if(getenv('HTTP_FORWARDED'))
            $ipAddress = getenv('HTTP_FORWARDED');
        else if(getenv('REMOTE_ADDR'))
            $ipAddress = getenv('REMOTE_ADDR');
        else
            $ipAddress = 'UNKNOWN';

        return $ipAddress;
    }

    /**
     * Get config data
     * @param  int $storeid
     * @param $key
     * @return mixed
     */
    public function getConfigValue($key, $storeid = 0)
    {
        if ($storeid) {
            return $this->scopeConfig->getValue($key, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeid);
        }
        return $this->scopeConfig->getValue($key);
    }
}
