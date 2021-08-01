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
 * Class RandomCode
 * Generate random code for SMS captcha code
 * @package D1m\Wesms\Helper
 */
class RandomCode extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * alpha and digit characters
     */
    const CHARSET_FORMAT_ALPHANUM = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

    /**
     * only alpha characters
     */
    const CHARSET_FORMAT_ALPHA     = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    /**
     * only digit characters
     */
    const CHARSET_FORMAT_NUMBER   = '0123456789';

    /***
     * Get format charset
     * @param null $format
     * @return array
     */
    private function _getFormatCharset($format=null)
    {
        $formatCharsets = array(
            'alphanum' =>  self::CHARSET_FORMAT_ALPHANUM,
            'alpha'    =>  self::CHARSET_FORMAT_ALPHA,
            'num'      =>  self::CHARSET_FORMAT_NUMBER
        );

        if(!is_null($format))
        {
            return $formatCharsets[$format];
        }

        return $formatCharsets;
    }

    /**
     * Generate random code
     *
     * @return string
     */
    public function generateRandom($format='num',$split=1,$length = 6,$splitChar='')
    {
        $length  = max(1, (int) $length);
        $split   = max(0, (int)$split);

        $charset =  str_split((string)$this->_getFormatCharset($format));

        $code = '';
        $charsetSize = count($charset);
        for ($i=0; $i<$length; $i++) {
            $char = $charset[mt_rand(0, $charsetSize - 1)];
            if ($split > 0 && ($i % $split) == 0 && $i != 0) {
                $char = $splitChar . $char;
            }
            $code .= $char;
        }

        return $code;
    }
}
