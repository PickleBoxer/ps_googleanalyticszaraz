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

use Context;
use Ps_GoogleanalyticsZaraz;

class HookActionValidateOrder implements HookInterface
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
        // Check if we are creating backoffice order, we are only launching this hook when creating backoffice order
        // For FO purposes, we use displayOrderConfirmation.
        if (empty($this->context->controller->controller_name)
        || !in_array($this->context->controller->controller_name, ['AdminOrders', 'Admin'])) {
            return;
        }

        // Mark this ID to immediately display it on next page load
        $order = $this->params['order'];

        // We are checking this, because in case of multishipping, there could be multiple orders
        if (!empty($this->context->cookie->gaz_admin_order)) {
            $gaz_admin_order = sprintf(
                '%1$s,%2$s',
                $this->context->cookie->gaz_admin_order,
                $order->id
            );
        } else {
            $gaz_admin_order = $order->id;
        }
        $this->context->cookie->gaz_admin_order = $gaz_admin_order;
        $this->context->cookie->write();
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
}
