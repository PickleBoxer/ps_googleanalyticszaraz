<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace PickleBoxer\Ps_GoogleanalyticsZaraz\Hooks;

use Configuration;
use Context;
use Db;
use Ps_GoogleanalyticsZaraz;

class HookActionOrderStatusPostUpdate implements HookInterface
{
    /**
     * @var Ps_GoogleanalyticsZaraz
     */
    private $module;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var array
     */
    private $params;

    public function __construct(Ps_GoogleanalyticsZaraz $module, Context $context)
    {
        $this->module = $module;
        $this->context = $context;
    }

    /**
     * run
     *
     * @return void
     */
    public function run()
    {
        // If we do not have an order or a new order status, we return
        if (empty($this->params['id_order']) || empty($this->params['newOrderStatus']->id)) {
            return;
        }

        // We get all states in which the merchant want to have refund sent and check if the new state being set belongs there
        $gazCancelledStates = json_decode(Configuration::get('GAZ_CANCELLED_STATES'), true);
        if (empty($gazCancelledStates) || !in_array($this->params['newOrderStatus']->id, $gazCancelledStates)) {
            return;
        }

        // We check if the refund was already sent to Google Analytics
        $gazRefundSent = Db::getInstance()->getValue(
            'SELECT id_order FROM `' . _DB_PREFIX_ . 'ganalyticszaraz` WHERE id_order = ' . (int) $this->params['id_order'] . ' AND refund_sent = 1'
        );

        // If it was not already refunded
        if ($gazRefundSent === false) {
            // We refund it and set the "sent" flag to true
            $jsCode = $this->getGoogleAnalytics4($this->params['id_order']);
            $this->context->cookie->gaz_admin_refund = $jsCode;
            $this->context->cookie->write();

            // We save this information to database
            Db::getInstance()->execute(
                'UPDATE `' . _DB_PREFIX_ . 'ganalyticszaraz` SET refund_sent = 1 WHERE id_order = ' . (int) $this->params['id_order']
            );
        }
    }

    /**
     * setParams
     *
     * @param array $params
     */
    public function setParams($params)
    {
        $this->params = $params;
    }

    /**
     * @param int $idOrder
     */
    protected function getGoogleAnalytics4($idOrder)
    {
        $eventData = [
            //'transaction_id' => (int) $idOrder,
            'order_id' => (int) $idOrder,
        ];

        //return $this->module->getTools()->renderEvent(
        //    'refund',
        //    $eventData
        //);

        return $this->module->getTools()->renderEventZarazEcommerce(
            'Refund',
            $eventData
        );
    }
}
