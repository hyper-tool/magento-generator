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
namespace D1m\Wesms\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Status
 * SMS status of send successfully or fail
 * @package D1m\Wesms\Model\Source
 */
class Status implements OptionSourceInterface
{
    /**
     * @var \D1m\Wesms\Model\LogFactory
     */
    protected $_smsLogFactory;

    /**
     * Status constructor.
     * @param \D1m\Wesms\Model\LogFactory $smsLogFactory
     */
    public function __construct(
        \D1m\Wesms\Model\LogFactory $smsLogFactory
    ){
        $this->_smsLogFactory = $smsLogFactory;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $smsLog = $this->_smsLogFactory->create();
        $availableOptions = $smsLog->getAvailableStatuses();
        $options = [];
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}
