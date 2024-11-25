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
if (!defined('_PS_VERSION_')) {
    exit;
}

$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

class Ps_GoogleanalyticsZaraz extends Module
{
    public $name;
    public $tab;
    public $version;
    public $ps_versions_compliancy;
    public $author;
    public $bootstrap;
    public $displayName;
    public $description;
    public $confirmUninstall;
    public $products = [];
    public $_debug = 0;
    private $tools = null;
    private $dataHandler = null;

    public function __construct()
    {
        $this->name = 'ps_googleanalyticszaraz';
        $this->tab = 'analytics_stats';
        $this->version = '1.0.0';
        $this->ps_versions_compliancy = ['min' => '1.7.7', 'max' => _PS_VERSION_];
        $this->author = 'PickleBoxer';
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->trans('Google Analytics with Zaraz Integration', [], 'Modules.Googleanalyticszaraz.Admin');
        $this->description = $this->trans('Gain clear insights into important metrics about your customers, using Google Analytics with Zaraz integration', [], 'Modules.Googleanalyticszaraz.Admin');
        $this->confirmUninstall = $this->trans('Are you sure you want to uninstall Google Analytics with Zaraz integration? You will lose all the data related to this module.', [], 'Modules.Googleanalyticszaraz.Admin');
    }

    /**
     * Back office module configuration page content
     */
    public function getContent()
    {
        $configurationForm = new PickleBoxer\Ps_GoogleanalyticsZaraz\Form\ConfigurationForm($this);
        $output = '';

        if (Tools::isSubmit('submit' . $this->name)) {
            $output .= $configurationForm->treat();
        }

        $output .= $this->display(__FILE__, './views/templates/admin/configuration.tpl');
        $output .= $configurationForm->generate();

        return $output;
    }

    public function hookDisplayHeader($params, $back_office = false)
    {
        $hook = new PickleBoxer\Ps_GoogleanalyticsZaraz\Hooks\HookDisplayHeader($this, $this->context);
        $hook->setBackOffice($back_office);

        return $hook->run();
    }

    /**
     * Confirmation page hook.
     * This function is run to track transactions.
     */
    public function hookDisplayOrderConfirmation($params)
    {
        $hook = new PickleBoxer\Ps_GoogleanalyticsZaraz\Hooks\HookDisplayOrderConfirmation($this, $this->context);
        $hook->setParams($params);

        return $hook->run();
    }

    /**
     * Footer hook
     * This function is run to load JS script for standards actions such as product clicks
     */
    public function hookDisplayBeforeBodyClosingTag()
    {
        $hook = new PickleBoxer\Ps_GoogleanalyticsZaraz\Hooks\HookDisplayBeforeBodyClosingTag($this, $this->context);

        return $hook->run();
    }

    /**
     * Product page footer hook
     * This function is run to load JS for product details view
     */
    public function hookDisplayFooterProduct()
    {
        $hook = new PickleBoxer\Ps_GoogleanalyticsZaraz\Hooks\HookDisplayFooterProduct($this, $this->context);

        return $hook->run();
    }

    /**
     * Hook admin order.
     * This function is run to send transactions and refunds details
     */
    public function hookDisplayAdminOrder()
    {
        $gaZarazTagHandler = new PickleBoxer\Ps_GoogleanalyticsZaraz\Handler\GanalyticsZarazJsHandler($this, $this->context);

        $output = $gaZarazTagHandler->generate($this->context->cookie->gaz_admin_refund);
        unset($this->context->cookie->gaz_admin_refund);
        $this->context->cookie->write();

        return $output;
    }

    /**
     * Admin office header hook.
     * This function is run to add Google Analytics JavaScript
     */
    public function hookDisplayBackOfficeHeader()
    {
        $hook = new PickleBoxer\Ps_GoogleanalyticsZaraz\Hooks\HookDisplayBackOfficeHeader($this, $this->context);

        return $hook->run();
    }

    /**
     * Product cancel action hook (in Back office).
     * This function is run to add Google Analytics JavaScript
     */
    public function hookActionProductCancel($params)
    {
        $hook = new PickleBoxer\Ps_GoogleanalyticsZaraz\Hooks\HookActionProductCancel($this, $this->context);
        $hook->setParams($params);
        $hook->run();
    }

    /**
     * Hook used to detect backoffice orders and store their IDs into cookie.
     */
    public function hookActionValidateOrder($params)
    {
        $hook = new PickleBoxer\Ps_GoogleanalyticsZaraz\Hooks\HookActionValidateOrder($this, $this->context);
        $hook->setParams($params);
        $hook->run();
    }

    /**
     * Hook called after order status change, used to "refund" order after cancelling it
     */
    public function hookActionOrderStatusPostUpdate($params)
    {
        $hook = new PickleBoxer\Ps_GoogleanalyticsZaraz\Hooks\HookActionOrderStatusPostUpdate($this, $this->context);
        $hook->setParams($params);
        $hook->run();
    }

    /**
     * Hook to process add and remove items from cart events
     * This function is run to implement 'add to cart' and 'remove from cart' functionalities
     */
    public function hookActionCartUpdateQuantityBefore($params)
    {
        $hook = new PickleBoxer\Ps_GoogleanalyticsZaraz\Hooks\HookActionCartUpdateQuantityBefore($this, $this->context);
        $hook->setParams($params);
        $hook->run();
    }

    /**
     * Hook to process remove items from cart events
     * This function is run to implement 'remove from cart' functionalities
     */
    public function hookActionObjectProductInCartDeleteBefore($params)
    {
        $hook = new PickleBoxer\Ps_GoogleanalyticsZaraz\Hooks\HookActionObjectProductInCartDeleteBefore($this, $this->context);
        $hook->setParams($params);
        $hook->run();
    }

    public function hookActionCarrierProcess($params)
    {
        $hook = new PickleBoxer\Ps_GoogleanalyticsZaraz\Hooks\HookActionCarrierProcess($this, $this->context);
        $hook->setParams($params);
        $hook->run();
    }

    public function hookActionFrontControllerSetMedia()
    {
        $this->context->controller->registerJavascript(
            'ps_googleanalyticszaraz-javascript',
            'modules/' . $this->name . '/views/js/actions.js',
            [
                'position' => 'bottom',
                'priority' => 1000,
            ]
        );
    }

    protected function _debugLog($function, $log)
    {
        if (!$this->_debug) {
            return true;
        }

        $myFile = _PS_MODULE_DIR_ . $this->name . '/logs/analytics.log';
        $fh = fopen($myFile, 'a');
        fwrite($fh, date('F j, Y, g:i a') . ' ' . $function . "\n");
        fwrite($fh, print_r($log, true) . "\n\n");
        fclose($fh);
    }

    /**
     * This method is triggered at the installation of the module
     * - it installs all module tables
     * - it registers the hooks used by this module
     *
     * @return bool
     */
    public function install()
    {
        $database = new PickleBoxer\Ps_GoogleanalyticsZaraz\Database\Install($this);

        return parent::install() &&
            $database->registerHooks() &&
            $database->setDefaultConfiguration() &&
            $database->installTab() &&
            $database->installTables();
    }

    /**
     * Triggered at the uninstall of the module
     * - erases this module SQL tables
     *
     * @return bool
     */
    public function uninstall()
    {
        $database = new PickleBoxer\Ps_GoogleanalyticsZaraz\Database\Uninstall();

        return parent::uninstall() &&
            $database->uninstallTab() &&
            $database->uninstallTables();
    }

    /**
     * Returns instance of GoogleAnalyticsTools
     */
    public function getTools()
    {
        if ($this->tools === null) {
            $this->tools = new PickleBoxer\Ps_GoogleanalyticsZaraz\GoogleAnalyticsZarazTools();
        }

        return $this->tools;
    }

    /**
     * Returns instance of GanalyticsDataHandler
     */
    public function getDataHandler()
    {
        if ($this->dataHandler === null) {
            $this->dataHandler = new PickleBoxer\Ps_GoogleanalyticsZaraz\Handler\GanalyticsZarazDataHandler(
                $this->context->cart->id,
                $this->context->shop->id
            );
        }

        return $this->dataHandler;
    }
}
