<?php
/**
 * User: BenHuang
 * Email: benhuang1024@gmail.com
 * Date: 2021/8/1
 * Time: 14:55
 */

namespace D1m\Tag\Block\Adminhtml;

class Index extends \Magento\Framework\View\Element\Template
{
    protected $_template = 'D1m_Tag::index.phtml';

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context
    ){
        parent::__construct($context);
    }

    public function sayHello()
    {
        return __('Hello World');
    }

    public function getPostCollection()
    {
        $post = $this->_postFactory->create();
        return $post->getCollection();
    }
}
