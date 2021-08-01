<?php
/**
 * D1m
 *
 * Author: horsed1m
 */
namespace D1m\Wesms\Model\Adapter;

/**
 * Class ShgmNets
 * @package D1m\Wesms\Model\Adapter
 */
class ShgmNets extends AbstractModel
{
    /**
     * @var \D1m\Wesms\Model\Config\Service\ShgmNets
     */
    protected $_config;

    /**
     * @var \D1m\Sales\Helper\Log
     */
    protected $_logger;

    /**
     * curl error
     */
    private $_curlError = '';

    /**
     * ShgmNets constructor.
     * @param \D1m\Wesms\Model\Config\Service\ShgmNets $config
     * @param \D1m\Sales\Helper\Log $logger
     */
    public function __construct(
        \D1m\Wesms\Model\Config\Service\ShgmNets $config,
        \D1m\Sales\Helper\Log $logger
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

            $data['account'] = $this->_config->getApiUsr();
            $data['ts'] = time();
            $data['mobile'] = $to;
            $data['sign'] = md5(md5($data['account'] . $data['ts']).$this->_config->getApiPwd());
            $data['msg'] = $text;

            if( ($result = $this->curlRequest($this->_url, json_encode($data), true )) ) {
                $result = json_decode($result, true);
                if (isset($result['status']) && $result['status'] == true ) {
                    return array('status'=>true, 'message' => $result['data']['message']);
                } else {
                    $this->_logger->log('ShgmNets_error', print_r($result['data'], true), 'd1m_sms');
                    return array('status'=>false, 'message' => isset($result['data']['message']) ? $result['data']['message'] : $result['data']['code']  );
                }
            } else {
                return array('status'=>false, 'message'=>__('ShgmNets is unusable').':'.$this->_curlError);
            }

        } catch (\Exception $e)
        {
            $this->_logger->log('ShgmNets_error', $e->getMessage(), 'd1m_sms');
            return array('status'=>false, 'message'=>__('Program is abnormal'), 'error' => $e->getMessage());
        }
    }

    /**
     * @param $url
     * @param array $data
     * @param bool $ispost
     * @return mixed
     */
    public function curlRequest($url, $data = [], $ispost = false)
    {
        $header = array(
            "Content-Type: application/json",
        );
        $ch  = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        if ($ispost) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        // use a user agent to mimic a browser
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.7.5) Gecko/20041107 Firefox/1.0');

        $content = curl_exec($ch);
        $errorMsg = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $this->_curlError = '('.$httpCode.' : '.$errorMsg.')';
        if($errorMsg){
            $this->_logger->log('ShgmNets_error', $errorMsg, 'd1m_sms');
        }
        curl_close($ch);
        return $content;
    }

}
