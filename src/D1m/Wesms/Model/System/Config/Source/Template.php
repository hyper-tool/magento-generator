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
namespace D1m\Wesms\Model\System\Config\Source;

/**
 * Class Template
 * SMS templates
 * @package D1m\Wesms\Model\System\Config\Source
 */
class Template
{
    /**
     * @var \D1m\Wesms\Model\TemplateFactory
     */
    protected $_templateFactory;

    /**
     * Template constructor.
     * @param \D1m\Wesms\Model\TemplateFactory $templateFactory
     */
    public function __construct(
        \D1m\Wesms\Model\TemplateFactory $templateFactory
    )
    {
        $this->_templateFactory = $templateFactory;
    }

    /**
     * Get SMS Templates
     * @return array
     */
    public function toOptionArray()
    {
        $enableTemplates = $this->_templateFactory->create()->getCollection()->addFieldToFilter('is_active', true);

        $templates = array(
            '' => __('Choose SMS Template')
        );
        if ($enableTemplates && $enableTemplates->getSize())
        {
            foreach ($enableTemplates as $template) {
                $templates[$template->getId()] = $template->getName();
            }
        }

        return $templates;
    }

    /**
     * Options getter
     * @return array
     */
    public function toOptions()
    {

    }
}