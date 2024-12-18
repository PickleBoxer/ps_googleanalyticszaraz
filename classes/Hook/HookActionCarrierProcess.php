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
use PickleBoxer\Ps_GoogleanalyticsZaraz\Repository\CarrierRepository;
use Ps_GoogleanalyticsZaraz;

class HookActionCarrierProcess implements HookInterface
{
    private $module;
    private $context;
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
        if (isset($this->params['cart']->id_carrier)) {
            $carrierRepository = new CarrierRepository();

            // Load carrier name
            $carrierName = (string) $carrierRepository->findByCarrierId((int) $this->params['cart']->id_carrier);

            // Check if we actually have some name
            if (empty($carrierName)) {
                return;
            }

            // Prepare and render the event
            $eventData = [
                'currency' => $this->context->currency->iso_code,
                'value' => (float) $this->context->cart->getSummaryDetails()['total_price'],
                'shipping_tier' => $carrierName,
            ];
            //$jsCode = $this->module->getTools()->renderEvent(
            //    'add_shipping_info',
            //    $eventData
            //);
            $jsCode = $this->module->getTools()->renderEventZarazEcommerce(
                'Shipping Info Entered',
                $eventData
            );

            // Store it into our repository so we can flush it on next page load
            $this->module->getDataHandler()->persistData($jsCode);
        }
    }

    /**
     * @param array $params
     */
    public function setParams($params)
    {
        $this->params = $params;
    }
}
