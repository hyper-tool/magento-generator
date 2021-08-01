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
namespace D1m\Wesms\Model\Action\ValidateCode;

/**
 * Class AbstractModel
 * SMS captcha validate abstract model
 * @package D1m\Wesms\Model\Action\ValidateCode
 */
abstract class AbstractModel extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Sms type
     * @var string
     */
    protected $_checkPassTag           = 'check_pass';

    /**
     * Session code
     * @var string
     */
    protected $_preSmsValidationNumber = 'validate_default';

    /**
     * validate code
     * @var string
     */
    protected $_word;

    /**
     * Generate code format
     * @var string
     */
    protected  $_format;

    /**
     * Generate split
     * @var string
     */
    protected  $_split;

    /**
     * @var integer
     */
    protected  $_length;

    /**
     * @var string
     */
    protected  $_splitChar;

    /**
     * Set SMS timeout
     * @var integer
     */
    protected $_timeout;

    /**
     * @var \D1m\Wesms\Helper\RandomCode
     */
    protected $_randomCodeHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var
     */
    protected $_mobile;

    /**
     * AbstractModel constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \D1m\Wesms\Helper\RandomCode $randomCodeHelper
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \D1m\Wesms\Helper\RandomCode $randomCodeHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {

        $this->_randomCodeHelper = $randomCodeHelper;
        $this->_customerSession = $customerSession;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);

    }

    /**
     * Set format
     * @param String $format
     * @return $this
     */
    public function setFormat($format)
    {
        $this->_format = $format;
        return $this;
    }

    /**
     * Get format
     * @return String
     */
    public function getFormat()
    {
        return $this->_format;
    }

    /**
     * Set mobile
     * @param String $mobile
     * @return $this
     */
    public function setMobile($mobile)
    {
        $this->_mobile = $mobile;
        return $this;
    }

    /**
     * Get format
     * @return String
     */
    public function getMobile()
    {
        return $this->_mobile;
    }

    /**
     * Set length
     * @param mixed $length
     * @return $this
     */
    public function setLength($length)
    {
        $this->_length = $length;
        return $this;
    }

    /**
     * Get length
     * @return mixed
     */
    public function getLength()
    {
        return $this->_length;
    }

    /**
     * Set split
     * @param mixed $split
     * @return $this
     */
    public function setSplit($split)
    {
        $this->_split = $split;
        return $this;
    }

    /**
     * Get split
     * @return mixed
     */
    public function getSplit()
    {
        return $this->_split;
    }

    /**
     * Set split char
     * @param mixed $splitChar
     * @return $this
     */
    public function setSplitChar($splitChar)
    {
        $this->_splitChar = $splitChar;
        return $this;
    }

    /**
     * Get split char
     * @return mixed
     */
    public function getSplitChar()
    {
        return $this->_splitChar;
    }

    /**
     * Set timeout
     * @param mixed $timeout
     * @return $this
     */
    public function setTimeout($timeout)
    {
        $this->_timeout = $timeout;
        return $this;
    }

    /**
     * Get timeout
     * @return mixed
     */
    public function getTimeout()
    {
        return $this->_timeout;
    }

    /**
     * Returns session instance
     * @return \Magento\Customer\Model\Session
     */
    public function getSession()
    {
        return $this->_customerSession;
    }

    /**
     * Get pre validate tag number
     * @return string
     */
    public function getPreValidateTag()
    {
        return $this->_preSmsValidationNumber;
    }

    /**
     * Get check tag
     * @return string
     */
    public function getCheckPassTag()
    {
        return $this->_checkPassTag;
    }

    /**
     * Get SMS Validate word
     *
     * @param $mobile
     * @return null
     */
    public function getWord($mobile)
    {
        $ret = null;
        $sessionData = $this->getSession()->getData($this->getPreValidateTag());
        $sessionMobile = isset($sessionData['mobile']) ? $sessionData['mobile'] : '00';
        $expires =  isset($sessionData['expires']) ? $sessionData['expires'] : time();
        if ( (time() < $expires) && ($mobile == $sessionMobile) ) {
            $ret = $sessionData['data'];
        }
        return $ret;
    }

    /**
     * Set SMS Validate word
     * @param $word
     * @return $this
     */
    protected function _setWord($word)
    {
        $this->getSession()->setData($this->getPreValidateTag(),
            new \Magento\Framework\DataObject(array('data' => $word, 'mobile' => $this->getMobile(), 'expires' => time() + $this->getTimeout()))
        );
        $this->_word = $word;
        return $this;
    }

    /**
     * Set SMS Validate word
     * @return $this
     */
    public  function clearWord()
    {
        $this->getSession()->unsetData($this->getPreValidateTag());
        $this->_word = null;
        return $this;
    }

    /**
     * Whether to respect case while checking the answer
     * @return bool
     */
    public function isCaseSensitive()
    {
        return false;
    }

    /**
     *
     * Checks whether SMS was guessed correctly by user
     *
     * @param $word
     * @param null $mobile
     * @return bool
     */
    public function isCorrect($word, $mobile = null)
    {
        $storedWord = $this->getWord($mobile);

        if (!$word || !$storedWord) {
            return false;
        }

        if (!$this->isCaseSensitive()) {
            $storedWord = strtolower($storedWord);
            $word = strtolower($word);
        }
        return $word == $storedWord;
    }

    /**
     * Generate word used for SMS render
     * @return string
     */
    protected function _generateWord()
    {
        return $this->_randomCodeHelper->generateRandom($this->getFormat(),$this->getSplit(),$this->getLength(),$this->getSplitChar());
    }

    /**
     * Public method for generate word, when customer submit form, generate once, then save it in session 
     * for next time comparision.
     * @access public
     * @return void
     */
    public function generateWord()
    {
        $this->_setWord($this->_generateWord());
    }

    /**
     * Captcha check
     * @param bool $isCheck
     */
    public function setPassCheck($isCheck = false)
    {
        if (!in_array($isCheck,array(true,false)))
        {
            $isCheck = false;
        }
        $this->getSession()->setData($this->getCheckPassTag(),$isCheck);
    }

    /**
     *  Whether is checked or not
     * @return bool
     */
    public function isPassedCheck()
    {
        $result = false;
        if ($this->getSession()->getData($this->getCheckPassTag()) == true)
        {
            $result = true;
        }

        $this->getSession()->unsetData($this->getCheckPassTag());
        return $result;
    }
}
