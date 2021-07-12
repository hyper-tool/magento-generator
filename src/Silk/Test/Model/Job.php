<?php
namespace Silk\Test\Model;

use \Magento\Framework\Model\AbstractModel;

class Job extends AbstractModel
{
    const JOB_ID = 'entity_id'; // We define the id fieldname

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'test';

    /**
     * Name of the event object
     *
     * @var string
     */
    protected $_eventObject = 'job';

    /**
     * Name of object id field
     *
     * @var string
     */
    protected $_idFieldName = self::JOB_ID;

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Silk\Test\Model\ResourceModel\Job');
    }
}