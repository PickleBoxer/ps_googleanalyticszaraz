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
use PickleBoxer\Ps_GoogleanalyticsZaraz\Handler\GanalyticsZarazJsHandler;
use PickleBoxer\Ps_GoogleanalyticsZaraz\Wrapper\ProductWrapper;
use Ps_GoogleanalyticsZaraz;

class HookDisplayFooterProduct implements HookInterface
{
    private $module;
    private $context;

    public function __construct(Ps_GoogleanalyticsZaraz $module, Context $context)
    {
        $this->module = $module;
        $this->context = $context;
    }

    /**
     * run
     *
     * @return string|void
     */
    public function run()
    {
        // Check we are really on product page
        if ($this->context->controller->php_self !== 'product') {
            return;
        }

        // Get lazy array from context
        $product = $this->context->smarty->getTemplateVars('product');
        if (empty($product)) {
            return;
        }

        // Initialize tag handler
        $gaZarazTagHandler = new GanalyticsZarazJsHandler($this->module, $this->context);

        // Prepare it and format it for our purpose
        $productWrapper = new ProductWrapper($this->context);
        $item = $productWrapper->prepareItemFromProduct($product);

        $jsCode = '';

        // Prepare and render event
        $eventData = [
            'currency' => $this->context->currency->iso_code,
            'value' => $item['price'],
            //'items' => [$item],
            //'products' => [$item],
            'availability_message' => $product['availability_message'],
        ];

        // Add additional data to the event
        $eventData = array_merge($eventData, $item);

        //$jsCode .= $this->module->getTools()->renderEvent(
        //    'view_item',
        //    $eventData
        //);

        //var_dump($product);

        $jsCode .= $this->module->getTools()->renderEventZarazEcommerce(
            'Product Viewed',
            $eventData
        );

        // If the user got to the product page from previous page on our shop,
        // we will also send select_item event.
        if ($this->wasPreviousPageOurShop()) {
            //$eventData = [
            //'items' => [$item],
            //'products' => [$item],
            //];
            $eventData = [];
            $eventData = array_merge($eventData, $item);

            // We will also try to get the information about the last visited listing.
            // We save this information into a cookie. If it's the page that got the user here,
            // we will use it.
            $previousListingData = $this->getLastVisitedListing();
            if (!empty($previousListingData)) {
                $eventData = array_merge($previousListingData, $eventData);
            }

            // Render the event
            //$jsCode .= $this->module->getTools()->renderEvent(
            //    'select_item',
            //    $eventData
            //);
            $jsCode .= $this->module->getTools()->renderEventZarazEcommerce(
                'Product Clicked',
                $eventData
            );
        }

        // Add product availability message
        if (!empty($product['availability_message'])) {
            $eventData = [
                'disponibilita' => $product['availability_message'],
            ];

            $jsCode .= $this->module->getTools()->renderEventZarazTrack(
                'disponibilita_prodotto',
                $eventData
            );
        }

        return $gaZarazTagHandler->generate($jsCode);
    }

    /**
     * Checks HTTP_REFERER to see if the previous page that got user to this product
     * was our shop.
     *
     * @return bool
     */
    private function wasPreviousPageOurShop()
    {
        if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) !== false) {
            return true;
        }

        return false;
    }

    /**
     * Tries to get details of previous listing from the cookie.
     *
     * @return bool|array
     */
    private function getLastVisitedListing()
    {
        // Fetch it from the cookie
        $last_listing = $this->context->cookie->gaz_last_listing;
        if (empty($last_listing)) {
            return false;
        }

        // Decode the data and check if it contains something sensible
        $last_listing = json_decode($last_listing, true);
        if (empty($last_listing['item_list_id'])) {
            return false;
        }

        // Check if the last listing is the page the user came from
        if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], $last_listing['item_list_url']) !== false) {
            unset($last_listing['item_list_url']);

            return $last_listing;
        }

        return false;
    }
}
