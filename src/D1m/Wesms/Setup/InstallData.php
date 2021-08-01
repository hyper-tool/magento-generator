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
namespace D1m\Wesms\Setup;

use Magento\Directory\Helper\Data;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Class InstallData
 * @package D1m\Wesms\Setup
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * SMS template data
     *
     * @var Data
     */
    private $directoryData;

    /**
     * Init
     *
     * @param Data $directoryData
     */
    public function __construct(Data $directoryData)
    {
        $this->directoryData = $directoryData;
    }

    /**
     * Install default data when setup
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /**
         * set the default sms template
         * Fill table wesms/sms_template
         */
        $data = [
            ['手机注册验证码', '手机注册验证码： {{var validate.code}} , 日期： {{var date}}', '1'],
            ['忘记密码验证码', '忘记密码验证码： {{var validate.code}} , 日期： {{var date}}', '1']
        ];

        foreach ($data as $row) {
            $bind = ['name' => $row[0], 'content' => $row[1], 'is_active' => $row[2]];
            $setup->getConnection()->insert($setup->getTable('sms_template'), $bind);
        }


    }
}
