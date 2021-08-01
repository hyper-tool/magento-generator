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

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

/**
 * Class UpgradeData
 * @package D1m\Wesms\Setup
 * @codeCoverageIgnore
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpgradeData implements UpgradeDataInterface
{

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.1', '<')
        ) {
            /**
             * set the default sms template
             * Fill table wesms/sms_template
             */
            $data = [
                ['付款确认', '您的订单已成功支付，商品将在1-2日内发出，您可随时登录中国官网查询订单状态。', '1'],
                ['发货确认', '您订购的商品已发出，您可登录中国官网，进入我的订单查询物流单号及快递公司信息。', '1'],
                ['退款确认', '您的退款申请已通过，款项将原路退回您的支付账户中，银行处理时间为1-5个工作日，请耐心等待。', '1'],
            ];

            foreach ($data as $row) {
                $bind = ['name' => $row[0], 'content' => $row[1], 'is_active' => $row[2]];
                $setup->getConnection()->insert($setup->getTable('sms_template'), $bind);
            }
        }

        if (version_compare($context->getVersion(), '1.0.2', '<')
        ) {
            /**
             * set the default sms template
             * Fill table wesms/sms_template
             */
            $data = [
                ['创建退款', '您的退款已创建。', '1'],
                ['同意退款', '您的退款请求已被通过。', '1'],
                ['收到商品', '您的退款商品已收到', '1']
            ];

            foreach ($data as $row) {
                $bind = ['name' => $row[0], 'content' => $row[1], 'is_active' => $row[2]];
                $setup->getConnection()->insert($setup->getTable('sms_template'), $bind);
            }
        }

        //add mobile edit mobile sms template
        if (version_compare($context->getVersion(), '1.0.3', '<')
        ) {
            $data = [
                ['手机修改验证码', '手机修改验证码： {{var validate.code}} , 日期： {{var date}}', '1']
            ];

            foreach ($data as $row) {
                $bind = ['name' => $row[0], 'content' => $row[1], 'is_active' => $row[2]];
                $setup->getConnection()->insert($setup->getTable('sms_template'), $bind);
            }
        }

        //add mobile login sms template
        if (version_compare($context->getVersion(), '1.0.4', '<')
        ) {
            $data = [
                ['手机登录验证码', '手机登录验证码： {{var validate.code}} , 日期： {{var date}}', '1']
            ];

            foreach ($data as $row) {
                $bind = ['name' => $row[0], 'content' => $row[1], 'is_active' => $row[2]];
                $setup->getConnection()->insert($setup->getTable('sms_template'), $bind);
            }
        }

        $this->_setupstorefields($setup, $context);

        $setup->endSetup();
    }

    /** 安装 多store支持字段
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    protected function _setupstorefields(ModuleDataSetupInterface $setup, ModuleContextInterface $context) {

        //remark
        if (version_compare($context->getVersion(), '1.0.5', '<')) {

            $setup->getConnection()->addColumn(
                $setup->getTable('sms_template'),
                'store_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'default' => 0,
                    'comment' => 'store id',
                ]
            );

            $setup->getConnection()->addColumn(
                $setup->getTable('sms_log'),
                'store_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'default' => 0,
                    'comment' => 'store id',
                ]
            );
        }
    }

}
