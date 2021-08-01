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
            'isv.BLACK_KEY_CONTROL_LIMIT' => '黑名单管控',
            'isv.MOBILE_NUMBER_ILLEGAL' => '非法手机号',
            'VALVE:M_MC' => '重复过滤',
            'VALVE:H_MC' => '重复过滤',
            'VALVE:D_MC' => '重复过滤',
            'isv.ACCOUNT_ABNORMAL' => '账户异常',
            'isv.AMOUNT_NOT_ENOUGH' => '账户余额不足',
            'isv.ACCOUNT_NOT_EXISTS' => '账户不存在',
            'isp.SYSTEM_ERROR' => '系统错误',
            'isv.SMS_SIGNATURE_ILLEGAL' => '短信签名不合法',
            'isv.SMS_TEMPLATE_ILLEGAL' => '短信模板不合法',
            'isv.TEMPLATE_MISSING_PARAMETERS' => '模版缺少变量',
            'isv.TEMPLATE_PARAMS_ILLEGAL' => '模板变量里包含非法关键字',
            'isv.PRODUCT_UN_SUBSCRIPT' => '未开通云通信产品的阿里云客户',
            'isv.MOBILE_COUNT_OVER_LIMIT' => '手机号码数量超过限制',
            'isv.PARAM_LENGTH_LIMIT' => '参数超出长度限制',
            'isv.INVALID_PARAMETERS' => '参数异常',
            'FILTER' => '关键字拦截',
            'isv.PRODUCT_UNSUBSCRIBE' => '产品未开通',
            'isv.BUSINESS_LIMIT_CONTROL' => '业务限流',
            'isv.OUT_OF_SERVICE' => '业务停机',
            'isv.PARAM_NOT_SUPPORT_URL' => '不支持URL',
            'isp.RAM_PERMISSION_DENY' => 'RAM权限DENY',
            'isv.INVALID_JSON_PARAM' => 'JSON参数不合法，只接受字符串值',
            'isv.DAY_LIMIT_CONTROL' => '已经达到您在控制台设置的短信日发送量限额值',
            'isv.SMS_CONTENT_ILLEGAL' => '短信内容包含禁止发送内容,请修改短信文案',
            'isv.SMS_SIGN_ILLEGAL' => '签名禁止使用,请在短信服务控制台中申请符合规定的签名',
            'SignatureDoesNotMatch' => '签名（Signature）加密错误',
        );
        if (isset($message[$status])) {
            return $message[$status];
        }
        return $status;
    }

}
