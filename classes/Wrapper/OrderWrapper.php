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

namespace PickleBoxer\Ps_GoogleanalyticsZaraz\Wrapper;

use Configuration;
use Context;
use Currency;
use Shop;

class OrderWrapper
{
    private $context;

    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    /**
     * Return a detailed transaction for Google Analytics
     */
    public function wrapOrder($order)
    {
        // Prepare currency information
        $currency = new Currency((int) $order->id_currency);

        // Get coupon information
        $cartRules = $order->getCartRules();
        if (is_array($cartRules) && count($cartRules) > 0) {
            $coupon = $cartRules[0]['name'];
            $coupon_id = $cartRules[0]['id_cart_rule'];
        }

        return [
            //'transaction_id' => (int) $order->id,
            'order_id' => (int) $order->id,
            'affiliation' => Shop::isFeatureActive() ? $this->context->shop->name : Configuration::get('PS_SHOP_NAME'),
            'value' => (float) $order->total_paid,
            'shipping' => (float) $order->total_shipping,
            'tax' => (float) $order->total_paid_tax_incl - $order->total_paid_tax_excl,
            'customer' => (int) $order->id_customer,
            'currency' => $currency->iso_code,
            'payment_type' => (string) $order->payment,
            'coupon' => isset($coupon) ? $coupon : '',
            'coupon_id' => isset($coupon_id) ? $coupon_id : '',
        ];
    }
}
