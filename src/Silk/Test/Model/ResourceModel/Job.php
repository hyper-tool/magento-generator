<?php
namespace Silk\Test\Model\ResourceModel;

use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;
class Job extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        // Table Name and Primary Key column
        $this->_init('silk_job', 'entity_id');
    }

}