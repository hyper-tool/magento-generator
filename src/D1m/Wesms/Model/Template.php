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

use \Magento\Framework\Model\AbstractModel;

/**
 * Class Template
 * @package D1m\Wesms\Model
 */
class Template extends AbstractModel
{
    /**
     * @var string
     */
    const COMMON_ID = 'id';

    /**
     * @var integer
     */
    const STATUS_ENABLED  = 1;

    /**
     * @var integer
     */
    const STATUS_DISABLED = 0;

    /**
     * Name of object id field
     * @var string
     */
    protected $_idFieldName = self::COMMON_ID;

    /**
     * @var \D1m\Wesms\Model\Template\Filter
     */
    protected $_filter;

    /**
     * Template constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param Template\Filter $filter
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \D1m\Wesms\Model\Template\Filter $filter,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {

        $this->_filter = $filter;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);

        $this->_init('D1m\Wesms\Model\ResourceModel\Template');
    }

    /**
     * Filter SMS Template
     * @param array $variables
     * @param string $message_content
     * @return mixed
     */
    public function filterMessageContent(array $variables, $message_content='')
    {
        $this->_filter->setPlainTemplateMode(true);
        $this->_filter->setVariables($variables);
        return $this->_filter->filter($message_content);
    }
}