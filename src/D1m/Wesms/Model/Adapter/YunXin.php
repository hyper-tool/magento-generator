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

/**
 * Class YunXin
 * @package D1m\Wesms\Model\Adapter
 */
class YunXin extends AbstractModel
{
    /**
     * @var \D1m\Wesms\Model\Config\Service\KeHeng
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
     * @param \D1m\Wesms\Model\Config\Service\YunXin $config
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \D1m\Wesms\Model\Config\Service\YunXin $config,
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
     * @param null $tplCode
     * @param null $actionTypeId
     * @param null $jsonParam
     * @return array
     */
    public function sendMessage($to, $text, $isNotice = false, $tplCode = null, $actionTypeId = null, $jsonParam = null)
    {
        try
        {

            if (is_null($this->_config)) {
                return array('status'=>false,'message'=>__('SMS API service config is empty'));
            } else if (!$this->_config->validate()) {
                return array('status'=>false,'message'=>$this->_config->getError());
            }

            $data = [];

            $data['userCode'] = $this->_config->getYunXinCaptchaUsr();
            $data['userPass'] = $this->_config->getYunXinCaptchaPwd();


            //$data['userPass'] = md5($data['userPass']);
            $data['DesNo'] = $to;
            /*$base64Encode = base64_encode($text);
            $base64Encode = str_replace('+', "-", $base64Encode);
            $base64Encode = str_replace('/', "_", $base64Encode);
            $data['Msg'] = urlencode($base64Encode);*/
            $data['Msg'] = $text;
            $data['Channel'] = '';

            $paramStrs = [];
            foreach($data as $k => $v) {
                $paramStrs[$k] = $v;
            }
            $paramStr = $paramStrs;
           // var_dump($paramStrs);exit();
            if($result = $this->curlPost($this->_url, $paramStr)) {
                if(preg_match('/\d{19}/', $result)) {
                    return array('status'=>true, 'message' => $result);
                } else {
                    return array('status'=>false, 'message' => $result);
                }
            } else {
                return array('status'=>false, 'message'=>__('YunXin is unusable').':'.$this->_curlError);
            }

        } catch (\Exception $e)
        {
            $this->_logger->critical($e);
            return array('status'=>false, 'message'=>__('Program is abnormal'), 'error' => $e->getMessage());
        }
    }

    /**
     * @param $url
     * @param $data
     * @return mixed
     */
    public function curlPost($url, $data)
    {
        $ch  = curl_init();
        //echo '$url:'.$url."\n\r";
        //echo "\n\r************************\n\r";
        //print_r($data);
        //echo "\n\r************************\n\r";
        $config=array(CURLOPT_RETURNTRANSFER=>true,CURLOPT_URL=>$url);
        $config[CURLOPT_POST]=true;
        $config[CURLOPT_POSTFIELDS]=http_build_query($data);
        curl_setopt_array($ch,$config);
        /*curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.7.5) Gecko/20041107 Firefox/1.0');*/
        $content = curl_exec($ch);
        $errorMsg = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $this->_curlError = '('.$httpCode.' : '.$errorMsg.')';

        if($errorMsg){
            $this->_logger->critical($errorMsg);
        }
        curl_close($ch);
        return $content;
    }

}
