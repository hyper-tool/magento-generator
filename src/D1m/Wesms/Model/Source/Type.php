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
 * Class Type
 * SMS types
 * @package D1m\Wesms\Model\Source
 */
class Type implements OptionSourceInterface
{
    /**
     * @var \D1m\Wesms\Helper\Sms
     */
    protected $_smsHelper;

    /**
     * Type constructor.
     * @param \D1m\Wesms\Helper\Sms $smsHelper
     */
    public function __construct(\D1m\Wesms\Helper\Sms $smsHelper)
    {
        $this->_smsHelper = $smsHelper;
    }

    /**
     * Get options
     * @return array
     */
    public function toOptionArray()
    {
        $availableOptions = $this->_smsHelper->getActionTypeLabel();
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
