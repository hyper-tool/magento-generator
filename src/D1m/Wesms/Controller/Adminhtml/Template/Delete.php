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
namespace D1m\Wesms\Controller\Adminhtml\Template;

use Magento\Backend\App\Action;

/**
 * Class Delete
 * Delete template action class
 * @package D1m\Wesms\Controller\Adminhtml\Template
 */
class Delete extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'D1m_Wesms::wesms_sms_template';

    /**
     * @var \D1m\Wesms\Model\TemplateFactory
     */
    protected $_templateFactory;

    /**
     * Delete SMS template constructor.
     * @param Action\Context $context
     * @param \D1m\Wesms\Model\TemplateFactory $templateFactory
     */
    public function __construct(
        Action\Context $context,
        \D1m\Wesms\Model\TemplateFactory $templateFactory
    ) {
        $this->_templateFactory = $templateFactory;
        parent::__construct($context);
    }


    /**
     * Delete template action
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('id');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                // init model and delete
                $smsTpl = $this->_templateFactory->create();
                $smsTpl->getResource()->load($smsTpl, $id);
                $smsTpl->getResource()->delete($smsTpl);

                // display success message
                $this->messageManager->addSuccessMessage(__('The template has been deleted.'));
                // go to grid
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addErrorMessage($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            }
        }
        // display error message
        $this->messageManager->addErrorMessage(__('We can\'t find a template to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}
