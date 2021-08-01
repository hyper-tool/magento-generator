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
namespace D1m\Wesms\Model;

/**
 * Class Log
 * SMS Log model
 * @package D1m\Wesms\Model
 */
class Log extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var string
     */
    const COMMON_ID = 'id';

    /**
     * send success status
     * @var integer
     */
    const STATUS_SUCCESS  = 1;

    /**
     * send fail status
     * @var integer
     */
    const STATUS_ERROR    = 0;

    /**
     * Name of object id field
     * @var string
     */
    protected $_idFieldName = self::COMMON_ID;

    /**
     * SMS is send to an administrator.
     * @var int
     */
    const TYPE_ADMIN = 1;

    /**
     * SMS is send to an web request. eg. message for registering and forgot password
     */
    const TYPE_CUSTOMER = 2;

    /**
     * Type of SMS.
     */
    protected $_type = self::TYPE_CUSTOMER;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_dateTime;

    /**
     * Log constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {

        $this->_dateTime = $dateTime;
        $this->_logger = $context->getLogger();

        parent::__construct($context,$registry,$resource,$resourceCollection,$data);
    }

    /**
     * Get sms log status options
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [self::STATUS_SUCCESS => __('Success'), self::STATUS_ERROR => __('Fail')];
    }

    /**
     * log model construct
     */
    protected function _construct()
    {
        $this->_init('D1m\Wesms\Model\ResourceModel\Log');

        $this->setType(self::TYPE_CUSTOMER);
        $this->setCanSend(true);
    }

    /**
     * Set type. You can use TYPE_ADMIN or TYPE_CUSTOMER as $type.
     * If you use other value method does nothing and log warning.
     * Method returns $this for keep the influence interface.
     * @param $type
     * @return $this
     */
    public function setType($type)
    {
        if ($type != self::TYPE_ADMIN && $type != self::TYPE_CUSTOMER)
        {
            $this->_logger->addError(__CLASS__.":".__METHOD__.": $type is not allowed for type SMS.");
        }
        else
        {
            $this->_type = $type;
        }

        return $this;
    }

    /**
     * Determine whether SMS is send to a customer or not.
     * @return bool
     */
    public function isCustomerSMS()
    {
        return ($this->getType() == self::TYPE_CUSTOMER);
    }

    /**
     * Determine whether SMS is send to an administrator or not.
     * @return bool
     */
    public function isAdminSMS()
    {
        return ($this->getType() == self::TYPE_ADMIN);
    }

    /**
     * @return bool
     */
    public function isNoticeSms()
    {
        switch($this->getData('action_type'))
        {
            case 20:
            case 21:
            case 1:
            case 2:
            case 18:
                return false;
                break;
            default:
                return true;
                break;
        }

        return true;
    }

    /**
     * Get current type of SMS.
     * @return int
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Before save action
     * @return \Magento\Framework\Model\AbstractModel|void
     */
    public function beforeSave()
    {
        $nowDate = $this->_dateTime->date();

        if (!$this->getId())
        {
            $this->setCreatedTime($nowDate)
                ->setUpdateTime($nowDate);
        }else{
            $this->setUpdateTime($nowDate);
        }

        parent::beforeSave();
    }

    /**
     *  just save sms sent log to DB
     *  only save the customer request sms , the system request sms don't need to save
     * @return \Magento\Framework\Model\AbstractModel|void
     */
    public function save()
    {
        if ($this->getType() == self::TYPE_CUSTOMER)
        {
            parent::save();
        }
    }


}