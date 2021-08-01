<?php
namespace D1m\Tag\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Display extends \Magento\Backend\App\Action
{
    protected $_pageFactory;
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        echo 'dis';
        return $this->resultPageFactory->create();
    }
}
