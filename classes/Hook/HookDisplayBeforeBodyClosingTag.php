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

class HookDisplayBeforeBodyClosingTag implements HookInterface
{
    private $module;
    private $context;
    private $gazScripts = '';

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
        // Prepare our tag handler
        $gaZarazTagHandler = new GanalyticsZarazJsHandler($this->module, $this->context);

        // Log information about product listing
        $this->saveInformationAboutListing();

        // Flush events stored in data storage from previous pages
        $this->outputStoredEvents();

        // Add events
        $this->renderProductListing();
        $this->renderSearch();
        $this->renderCartPage();
        $this->renderBeginCheckout();
        $this->renderLogin();
        $this->renderRegistration();

        // Output everything
        return $gaZarazTagHandler->generate($this->gazScripts);
    }

    /**
     * This method renders tracking code for product listings, like category pages.
     */
    private function renderProductListing()
    {
        // Try to get product list variable
        $listing = $this->context->smarty->getTemplateVars('listing');
        if (empty($listing['products'])) {
            return;
        }

        // Prepare items to our format
        $productWrapper = new ProductWrapper($this->context);
        $items = $productWrapper->prepareItemListFromProductList($listing['products']);

        // Prepare info about the list
        $item_list_id = $this->context->controller->php_self;
        $item_list_name = $listing['label'];

        // Render the event
        $eventData = [
            'item_list_id' => $item_list_id,
            'item_list_name' => $item_list_name,
            //'items' => $items,
            'products' => $items,
        ];

        //$this->gazScripts .= $this->module->getTools()->renderEvent(
        //    'view_item_list',
        //    $eventData
        //);

        $this->gazScripts .= $this->module->getTools()->renderEventZarazEcommerce(
            'Product List Viewed',
            $eventData
        );

        // Render quickview events
        /*
        foreach ($items as $item) {
            $eventData = [
                'item_list_id' => $item_list_id,
                'item_list_name' => $item_list_name,
                'items' => [$item],
            ];

            // Keep only product ID if id_product_attribute was appended
            $productId = explode('-', $item['item_id']);
            $productId = $productId[0];

            // Render the event wrapped in onclick
            $this->gazScripts .= '
            $(\'article[data-id-product="' . $productId . '"] a.quick-view\').on(
                "click",
                function() {' . $this->module->getTools()->renderEvent('select_item', $eventData) . '}
            );
            ';
        }
        */
    }

    /**
     * This method renders tracking code when user searches on the shop.
     */
    private function renderSearch()
    {
        // Check if we are on search page and we have a search string
        if ($this->context->controller->php_self != 'search' || empty($_GET['s'])) {
            return;
        }

        // Render the event
        $eventData = [
            'search_term' => (string) $_GET['s'],
            'query' => (string) $_GET['s'],
        ];
        //$this->gazScripts .= $this->module->getTools()->renderEvent(
        //    'search',
        //    $eventData
        //);
        $this->gazScripts .= $this->module->getTools()->renderEventZarazEcommerce(
            'Products Searched',
            $eventData
        );
    }

    /**
     * This method renders tracking code for product listings, like category pages.
     */
    private function renderCartpage()
    {
        // Check if we are on cart page
        if ($this->context->controller->php_self != 'cart') {
            return;
        }

        // Try to get product list variable and check if it's not empty
        $cart = $this->context->smarty->getTemplateVars('cart');
        if (empty($cart['products'])) {
            return;
        }

        // Prepare items to our format
        $productWrapper = new ProductWrapper($this->context);
        $items = $productWrapper->prepareItemListFromProductList($cart['products'], true);

        // Render the event
        $eventData = [
            'currency' => $this->context->currency->iso_code,
            'value' => $cart['totals']['total']['amount'],
            //'items' => $items,
            'products' => $items,
        ];
        //$this->gazScripts .= $this->module->getTools()->renderEvent(
        //    'view_cart',
        //    $eventData
        //);
        $this->gazScripts .= $this->module->getTools()->renderEventZarazEcommerce(
            'Cart Viewed',
            $eventData
        );
    }

    /**
     * This method renders tracking code for product listings, like category pages.
     */
    private function renderBeginCheckout()
    {
        // Check if we are on some supported order controller
        $allowed_controllers = ['order', 'orderopc', 'checkout'];
        if (!in_array($this->context->controller->php_self, $allowed_controllers)) {
            return;
        }

        // If the user reliably came from previous page, we won't render this event
        // We want to do it just for first visiting checkout
        if (!empty($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], $_SERVER['REQUEST_URI']) !== false) {
            return;
        }

        // Try to get product list variable and check if it's not empty
        $cart = $this->context->smarty->getTemplateVars('cart');
        if (empty($cart['products'])) {
            return;
        }

        // Prepare items to our format
        $productWrapper = new ProductWrapper($this->context);
        $items = $productWrapper->prepareItemListFromProductList($cart['products'], true);

        // Render the event
        $eventData = [
            'currency' => $this->context->currency->iso_code,
            'value' => $cart['totals']['total']['amount'],
            //items' => $items,
            'products' => $items,
        ];
        //$this->gazScripts .= $this->module->getTools()->renderEvent(
        //    'begin_checkout',
        //    $eventData
        //);
        $this->gazScripts .= $this->module->getTools()->renderEventZarazEcommerce(
            'Checkout Started',
            $eventData
        );
    }

    /**
     * This method renders tracking code after user logs in.
     */
    private function renderLogin()
    {
        // Render it only on login page AND if we are not creating a new account in older PS versions
        // For newer versions, registrations are handled with standalone registration controller.
        if ($this->context->controller->php_self != 'authentication' || isset($_GET['create_account'])) {
            return;
        }

        // Render the event
        //$this->gazScripts .= $this->module->getTools()->renderEvent('login', []);
        $this->gazScripts .= $this->module->getTools()->renderEventZarazTrack('login', []);
    }

    /**
     * This method renders tracking code after user registers.
     */
    private function renderRegistration()
    {
        if ($this->context->controller->php_self != 'registration' &&
            ($this->context->controller->php_self != 'authentication' || !isset($_GET['create_account']))
        ) {
            return;
        }

        // Render the event
        //$this->gazScripts .= $this->module->getTools()->renderEvent('sign_up', []);
        $this->gazScripts .= $this->module->getTools()->renderEventZarazTrack('sign_up', []);
    }

    /**
     * Saves information about last visited product listing, so we can later use it for select_item event.
     */
    private function saveInformationAboutListing()
    {
        // Try to get product list variable
        $listing = $this->context->smarty->getTemplateVars('listing');
        if (empty($listing['products']) || empty($listing['label'])) {
            return;
        }

        // Save this information to a cookie
        $this->context->cookie->gaz_last_listing = json_encode([
            'item_list_url' => $_SERVER['REQUEST_URI'],
            'item_list_id' => $this->context->controller->php_self,
            'item_list_name' => $listing['label'],
        ]);
    }

    /**
     * Outputs all events we stored into data repository during previous AJAX requests
     * on previous page.
     */
    private function outputStoredEvents()
    {
        // Get all stored events
        $storedEvents = $this->module->getDataHandler()->readData();
        if (empty($storedEvents)) {
            return;
        }

        foreach ($storedEvents as $event) {
            $this->gazScripts .= $event;
        }

        // Delete the repository because everything has been flushed
        $this->module->getDataHandler()->deleteData();
    }
}
