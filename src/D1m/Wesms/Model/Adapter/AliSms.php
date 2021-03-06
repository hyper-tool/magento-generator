<?php
/**
 * 2012-2019 D1m Group
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0) that is available
 * through the world-wide-web at this URL: http://www.opensource.org/licenses/OSL-3.0
 * If you are unable to obtain it through the world-wide-web, please send an email
 * to sales@d1m.cn so we can send you a copy immediately.
 *
 * @author D1m Group
 * @copyright 2012-2019 D1m Group
 * @license http://www.opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */
namespace D1m\Wesms\Model\Adapter;

use D1m\Wesms\Helper\Sms;

/**
 * Class AliSms
 * @package D1m\Wesms\Model\Adapter
 */
class AliSms extends AbstractModel
{
    /**
     * @var \D1m\Wesms\Model\Config\Service\AliSms
     */
    protected $_config;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * curl error
     */
    private $_curlError = '';

    /**
     * YunXin constructor.
     * @param \D1m\Wesms\Model\Config\Service\AliSms $config
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \D1m\Wesms\Model\Config\Service\AliSms $config,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->_logger = $logger;
        $this->_config = $config;
        $this->_url = $this->_config->getServiceUrl();
    }

    /**
     * @param $to
     * @param $text
     * @param bool $isNotice
     * @param null $templateCode
     * @param null $actionTypeId
     * @param null $object
     * @return array
     */
    public function sendMessage($to, $text, $isNotice = false, $templateCode = null, $actionTypeId = null, $jsonParam = null)
    {
        try
        {
            if (is_null($this->_config)) {
                return array('status'=>false,'message'=>__('SMS API service config is empty'));
            } else if (is_null($templateCode) || is_null($actionTypeId) || is_null($jsonParam)) {
                return array('status'=>false,'message'=>__('Params is required'));
            } else if (!$this->_config->validate()) {
                return array('status'=>false,'message'=>$this->_config->getError());
            }

            $data = array (
                'SignName' => $this->_config->getAliSmsSignName(),
                'Format' => 'JSON',
                'Version' => '2017-05-25',
                'AccessKeyId' => $this->_config->getAliSmsAccessKeyId(),
                'SignatureVersion' => '1.0',
                'SignatureMethod' => 'HMAC-SHA1',
                'SignatureNonce' => uniqid(mt_rand(0, 0xffff), true),
                'Timestamp' => gmdate( 'Y-m-d\TH:i:s\Z' ),
                'Action' => 'SendSms',
                'TemplateCode' => $templateCode,
                'PhoneNumbers' => $to,
                'TemplateParam' => $jsonParam
            );

            $data['Signature'] = $this->computeSignature($data, $this->_config->getAliSmsAccessKeySecret());

            $paramStrs = [];
            foreach($data as $k => $v) {
                $paramStrs[$k] = $v;
            }
            $paramStr = $paramStrs;

            $url = $this->_url . '/?' . http_build_query($paramStr);

            if($resultCode = $this->curlPost($url)) {
                if($resultCode === 'OK') {
                    return array('status'=>true, 'message' => $resultCode);
                } else {
                    return array('status'=>false, 'message' => $this->_curlError);
                }
            } else {
                return array('status'=>false, 'message'=>__('AliSms is unusable').':'.$this->_curlError);
            }

        } catch (\Exception $e)
        {
            $this->_logger->critical($e);
            return array('status'=>false, 'message'=>__('Program is abnormal'), 'error' => $e->getMessage());
        }
    }

    /**
     * Percent Encode
     *
     * @param $string
     * @return mixed|string
     */
    private function percentEncode($string)
    {
        $string = urlencode ( $string );
        $string = preg_replace ( '/\+/', '%20', $string );
        $string = preg_replace ( '/\*/', '%2A', $string );
        $string = preg_replace ( '/%7E/', '~', $string );
        return $string;
    }

    /**
     * Signature
     *
     * @param $parameters
     * @param $accessKeySecret
     * @return string
     */
    private function computeSignature($parameters, $accessKeySecret)
    {
        ksort ($parameters);
        $canonicalizedQueryString = '';
        foreach ($parameters as $key => $value) {
            $canonicalizedQueryString .= '&' . $this->percentEncode($key) . '=' . $this->percentEncode($value);
        }
        $stringToSign = 'GET&%2F&' . $this->percentencode(substr ($canonicalizedQueryString, 1));
        $signature = base64_encode(hash_hmac('sha1', $stringToSign, $accessKeySecret . '&', true));

        return $signature;
    }

    /**
     * @param $url
     * @param $data
     * @return mixed
     */
    public function curlPost($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url );
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE );
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt($ch, CURLOPT_TIMEOUT, 10 );
        $content = curl_exec($ch);

        $result = json_decode($content, true);

        if ($result['Code'] != 'OK') {
            $error = $this->getErrorMessage($result ['Code']);
            $this->_curlError = '('.$result ['Code'].' : '.$error.')';
        }

        curl_close($ch);
        return $result['Code'];
    }

    /**
     * Get Error Message
     *
     * @param $status
     * @return mixed
     */
    public function getErrorMessage($status)
    {
        $message = array (
            'isv.BLACK_KEY_CONTROL_LIMIT' => '???????????????',
            'isv.MOBILE_NUMBER_ILLEGAL' => '???????????????',
            'VALVE:M_MC' => '????????????',
            'VALVE:H_MC' => '????????????',
            'VALVE:D_MC' => '????????????',
            'isv.ACCOUNT_ABNORMAL' => '????????????',
            'isv.AMOUNT_NOT_ENOUGH' => '??????????????????',
            'isv.ACCOUNT_NOT_EXISTS' => '???????????????',
            'isp.SYSTEM_ERROR' => '????????????',
            'isv.SMS_SIGNATURE_ILLEGAL' => '?????????????????????',
            'isv.SMS_TEMPLATE_ILLEGAL' => '?????????????????????',
            'isv.TEMPLATE_MISSING_PARAMETERS' => '??????????????????',
            'isv.TEMPLATE_PARAMS_ILLEGAL' => '????????????????????????????????????',
            'isv.PRODUCT_UN_SUBSCRIPT' => '??????????????????????????????????????????',
            'isv.MOBILE_COUNT_OVER_LIMIT' => '??????????????????????????????',
            'isv.PARAM_LENGTH_LIMIT' => '????????????????????????',
            'isv.INVALID_PARAMETERS' => '????????????',
            'FILTER' => '???????????????',
            'isv.PRODUCT_UNSUBSCRIBE' => '???????????????',
            'isv.BUSINESS_LIMIT_CONTROL' => '????????????',
            'isv.OUT_OF_SERVICE' => '????????????',
            'isv.PARAM_NOT_SUPPORT_URL' => '?????????URL',
            'isp.RAM_PERMISSION_DENY' => 'RAM??????DENY',
            'isv.INVALID_JSON_PARAM' => 'JSON???????????????????????????????????????',
            'isv.DAY_LIMIT_CONTROL' => '???????????????????????????????????????????????????????????????',
            'isv.SMS_CONTENT_ILLEGAL' => '????????????????????????????????????,?????????????????????',
            'isv.SMS_SIGN_ILLEGAL' => '??????????????????,?????????????????????????????????????????????????????????',
            'SignatureDoesNotMatch' => '?????????Signature???????????????',
        );
        if (isset($message[$status])) {
            return $message[$status];
        }
        return $status;
    }

}
