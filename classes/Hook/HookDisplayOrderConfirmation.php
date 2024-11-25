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

use Cart;
use Configuration;
use Context;
use PickleBoxer\Ps_GoogleanalyticsZaraz\Handler\GanalyticsZarazJsHandler;
use PickleBoxer\Ps_GoogleanalyticsZaraz\Repository\GanalyticsZarazRepository;
use PickleBoxer\Ps_GoogleanalyticsZaraz\Wrapper\OrderWrapper;
use PickleBoxer\Ps_GoogleanalyticsZaraz\Wrapper\ProductWrapper;
use Ps_GoogleanalyticsZaraz;
use Validate;

class HookDisplayOrderConfirmation implements HookInterface
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
     * @return string
     */
    public function run()
    {
        $gazScripts = '';
        $order = $this->params['order'];

        if (!Validate::isLoadedObject($order) || $order->getCurrentState() == (int) Configuration::get('PS_OS_ERROR')) {
            return $gazScripts;
        }

        // Load up our handlers and repositories
        $ganalyticsZarazRepository = new GanalyticsZarazRepository();
        $gaZarazTagHandler = new GanalyticsZarazJsHandler($this->module, $this->context);
        $productWrapper = new ProductWrapper($this->context);
        $orderWrapper = new OrderWrapper($this->context);

        // If it's a completely new order, add order to repository, so we can later mark it as sent
        if (empty($ganalyticsZarazRepository->findGazOrderByOrderId((int) $order->id))) {
            $ganalyticsZarazRepository->addOrder((int) $order->id, (int) $order->id_shop);
        }

        // If the customer is revisiting confirmation screen and the order was already sent, we don't do anything
        if ($ganalyticsZarazRepository->hasOrderBeenAlreadySent((int) $order->id)) {
            return $gazScripts;
        }

        // Prepare transaction data
        $orderData = $orderWrapper->wrapOrder($order);

        // Prepare order products, if the cart still exists
        $orderProducts = [];
        $cart = new Cart($order->id_cart);
        if (Validate::isLoadedObject($cart)) {
            $orderProducts = $productWrapper->prepareItemListFromProductList($cart->getProducts(), true);
        }

        // Add payment event
        //$gazScripts .= $this->module->getTools()->renderEvent(
        //    'add_payment_info',
        //    [
        //        'currency' => $orderData['currency'],
        //        'value' => (float) $orderData['value'],
        //        'payment_type' => $orderData['payment_type'],
        //        'items' => $orderProducts,
        //    ]
        //);

        // Add payment event
        $gazScripts .= $this->module->getTools()->renderEventZarazEcommerce(
            'Payment Info Entered',
            [
                'currency' => $orderData['currency'],
                'value' => (float) $orderData['value'],
                'payment_type' => $orderData['payment_type'],
                //'items' => $orderProducts,
                'products' => $orderProducts,
            ]
        );

        // Render transaction code
        //$gazScripts .= $this->module->getTools()->renderPurchaseEvent(
        //    $orderProducts,
        //    $orderData,
        //    $this->context->link->getModuleLink('ps_googleanalytics', 'ajax', [], true)
        //);

        // Render transaction code
        $gazScripts .= $this->module->getTools()->renderPurchaseEventZarazEcommerce(
            $orderProducts,
            $orderData,
            $this->context->link->getModuleLink('ps_googleanalyticszaraz', 'ajax', [], true)
        );

        return $gaZarazTagHandler->generate($gazScripts);
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
