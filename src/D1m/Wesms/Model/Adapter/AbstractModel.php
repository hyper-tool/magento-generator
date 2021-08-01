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
namespace D1m\Wesms\Model\Adapter;

/**
 * Class AbstractModel
 * SMS send service abstract
 * @package D1m\Wesms\Model\Adapter
 */
abstract class AbstractModel extends \Magento\Framework\DataObject
{
    /**
     * SMS send service url
     * @var string
     */
    protected $_url;

    /**
     * default SoapClient
     * @var \SoapClient
     */
    protected $_hSopa;

    /**
     * SMS Config
     * @var \D1m\Wesms\Model\Config\AbstractModel
     */
    protected $_config;

    /**
     * Service client
     * @var array
     */
    protected $_client = array();

    /**
     * Run SoapClient connection
     * @return  $this
     */
    public function connect() {

        if(is_null($this->_hSopa))
        {
            $this->_hSopa = new \SoapClient(
                $this->_url,
                array(
                    'features'      => SOAP_SINGLE_ELEMENT_ARRAYS,
                    'cache_wsdl'    => WSDL_CACHE_BOTH,
                    'trace'         => TRUE
                )
            );
        }

        return $this->_hSopa;
    }

    /***
     *  Get user authorization data from config and save it to $_client
     * @return array
     */
    public function getClientData()
    {
        $this->_client = array(
            'username'  =>  $this->_config->getApiLogin(),
            'password'  =>  $this->_config->getApiPassword()
        );

        return $this->_client;
    }

    /**
     * Auth client on provider side
     *
     * @return \SoapClient
     */
    protected function _getAuthClient()
    {
        $login = $this->getConfigField('login');
        $password = $this->getConfigField('password');

        $auth = array(
            'login' => $login,
            'password' => $password
        );

        $auth = $this->_getClient()->Auth($auth);
        $this->_addErrorMessage($auth->AuthResult);

        return $this->_getClient();
    }

    /***
     * Send SMS
     * @param $to
     * @param $text
     * @return bool
     */
    public function sendMessage($to, $text)
    {
          return true;
    }
}