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


/**
 * Class Copy
 * Copy template action class
 * @package D1m\Wesms\Controller\Adminhtml\Template
 */
class Copy extends \Magento\Backend\App\Action
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
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * Copy constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \D1m\Wesms\Model\TemplateFactory $templateFactory
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     */
    public function __construct(
       \Magento\Backend\App\Action\Context $context,
        \D1m\Wesms\Model\TemplateFactory $templateFactory,
       \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
    ) {
        $this->_templateFactory = $templateFactory;
        $this->dataPersistor = $dataPersistor;
        parent::__construct($context);
    }

    /**
     * copy action
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id', 0);
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {

            try {
                /** @var \D1m\Wesms\Model\Template $model */
                $model = $this->_templateFactory->create();

                if ($id) {
                    $model->getResource()->load($model, $id);
                }
                if (!$model->getId()) {
                    throw new Exception("复制标未找到.");
                }
                $model->setId(null);
                $model->setName('copy:' . $model->getName());
                $model->getResource()->save($model);
                $this->messageManager->addSuccessMessage(__('copy template successfull.'));
                $this->dataPersistor->set('sms_template', $model->getData());
                return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId(), '_current' => true]);

            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, $e->getMessage());
            }
        }
        return $resultRedirect->setPath('*/*/');
    }
}
