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
 * Class Bind
 * @package D1m\Wesms\Model\Action\ValidateCode
 */
class Bind extends AbstractModel
{
    /**
     * @var string
     */
    protected $_checkPassTag           = 'bind_check';

    /**
     *  session tag
     * @var string
     */
    protected $_preSmsValidationNumber = 'validate_bind';


    /**
     * Bind constructor.
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
        $this->setFormat('num')
            ->setLength(6)
            ->setSplit(1)
            ->setSplitChar('')
            ->setTimeout(300)
        ;
        parent::__construct($context, $registry, $randomCodeHelper, $customerSession, $resource, $resourceCollection, $data);
    }
}
