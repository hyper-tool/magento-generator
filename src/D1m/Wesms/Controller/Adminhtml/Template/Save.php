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
use D1m\Wesms\Model\Template;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Save
 * Save SMS template
 * @package D1m\Wesms\Controller\Adminhtml\Template
 */
class Save extends \Magento\Backend\App\Action
{
    //const SPECIAL_CHARACTERS = '+~/\\<>\'":*$#@()!?`.=%&^';
    const SPECIAL_CHARACTERS = '\\<>\'"`^';

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'D1m_Wesms::wesms_sms_template';


    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var \D1m\Wesms\Model\TemplateFactory
     */
    protected $_templateFactory;

    /**
     * @var string[]
     */
    private $replaceSymbols = [];

    //safe check field
    protected $safeFields = ['name', 'link', 'sort'];


    /**
     * Save action constructor.
     * @param Action\Context $context
     * @param DataPersistorInterface $dataPersistor
     * @param \D1m\Wesms\Model\TemplateFactory $templateFactory
     */
    public function __construct(
        Action\Context $context,
        DataPersistorInterface $dataPersistor,
        \D1m\Wesms\Model\TemplateFactory $templateFactory
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->_templateFactory = $templateFactory;
        $this->replaceSymbols = str_split(self::SPECIAL_CHARACTERS, 1);
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            if (isset($data['is_active']) && $data['is_active'] === 'true') {
                $data['is_active'] = Template::STATUS_ENABLED;
            }
            if (empty($data['id'])) {
                $data['id'] = null;
            }

            /** @var \D1m\Wesms\Model\Template $model */
            $model = $this->_templateFactory->create();

            $id = $this->getRequest()->getParam('id');
            if ($id) {
                $model->getResource()->load($model, $id);
            }
            foreach ($data as $k => $v) {
                if (in_array($k, $this->safeFields)) {
                    $data[$k] = str_replace($this->replaceSymbols, '', $v);
                }
            }
            $model->setData($data);
            try {
                $model->getResource()->save($model);
                $this->messageManager->addSuccessMessage(__('You saved the template.'));
                $this->dataPersistor->clear('sms_template');
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the template.'));
            }

            $this->dataPersistor->set('sms_template', $data);
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
