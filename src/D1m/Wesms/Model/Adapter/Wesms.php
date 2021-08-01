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
 * Class Wesms
 * Wesms SMS send service
 * @package D1m\Wesms\Model\Adapter
 */
class Wesms extends AbstractModel
{
    /**
     * @var \D1m\Wesms\Model\Config\Service\Wesms
     */
    protected $_wesmsConfig;

    /**
     * @var \D1m\Wesms\Model\Adapter\Wesms
     */
    protected $_wesms;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * Wesms constructor.
     * @param \D1m\Wesms\Model\Config\Service\Wesms $wesmsConfig
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \D1m\Wesms\Model\Config\Service\Wesms $wesmsConfig,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->_logger = $logger;
        $this->_wesmsConfig = $wesmsConfig;
        $this->_url = $this->_wesmsConfig->getServiceUrl();
    }

    /**
     * Run SoapClient connection
     * @return  soap SoapClient
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

    /**
     * @param $to
     * @param $text
     * @param bool $isNotice
     * @param null $tplCode
     * @param null $actionTypeId
     * @param null $jsonParam
     * @return array
     */
    public function sendMessage($to, $text, $isNotice = false, $tplCode = null, $actionTypeId = null, $jsonParam = null)
    {

        try {

            if (is_null($this->_wesmsConfig))
            {
                return array('status'=>false,'message'=>__('SMS API service config is empty'));
            }

            if (!$this->_wesmsConfig->validate())
            {
                return array('status'=>false,'message'=>$this->_wesmsConfig->getError());
            }

            $client = $this->connect(false);

            //prepare webservice param object
            $obj = new \stdClass();
            $obj->mobile = $to;
            $obj->FormatID = 8;//FormatID: SMS FORMAT(0:EN;8:ZH;10:CONFORM SMS;40:lONG SMS)；
            $obj->Content = $text;
            $obj->ScheduleDate = '2010-01-01';
            $obj->TokenID = $this->_wesmsConfig->getServiceToken();

            $result = $client->SendSMS($obj);

            if($result)
            {
                if(substr($result->SendSMSResult,0,2) == 'OK')
                {
                    return array('status'=>true,'message'=>$result->SendSMSResult);
                }
                else
                {
                    return array('status'=>false,'message'=>$result->SendSMSResult);
                }
            }
            else
            {
                return array('status'=>false,'message'=>__('SOAP is unusable'));
            }

        } catch (\Exception $e) {
            $this->_logger->critical($e);
            return array('status'=>false,'message'=>__('Program is abnormal'), 'error' => $e->getMessage());
        }
    }
}