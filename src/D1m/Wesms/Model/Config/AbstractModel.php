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
namespace D1m\Wesms\Model\Config;

/**
 * Class AbstractModel
 * @package D1m\Wesms\Model\Config
 */
abstract class AbstractModel
{
    /**
     * @var \D1m\Wesms\Helper\Data
     */
    protected $_wesmsHelper;

    /**
     * AbstractModel constructor.
     * @param \D1m\Wesms\Helper\Data $wesmsHelper
     */
    public function __construct(
        \D1m\Wesms\Helper\Data $wesmsHelper
    )
    {
        $this->_wesmsHelper = $wesmsHelper;
    }

    /**
     * Get message helper
     * @return \D1m\Wesms\Helper\Data
     */
    public function _helper()
    {
        return $this->_wesmsHelper;
    }

    /**
     * Get Api Url
     * @return string
     */
    public function getApiUrl()
    {

    }

    /**
     * Getting API login from SMS API config
     * @return string
     */
    public function getApiLogin()
    {
    }


    /**
     * Getting API password from SMSAPI config
     * @return string
     */
    public function getApiPassword()
    {

    }

    /**
     * Get SMS Template
     *
     */
    public function getSmsTemplate()
    {

    }

    /**
     * Enable test mode
     *
     */
    public function enableTestMode()
    {

    }

    /**
     * Determine whether Username and API key is valid or not.
     *
     * @return string
     */
    public function testCredentials()
    {

    }

}