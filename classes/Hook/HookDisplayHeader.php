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
use Customer;
use Ps_GoogleanalyticsZaraz;
use Tools;

class HookDisplayHeader implements HookInterface
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
     * @var bool
     */
    private $backOffice;

    public function __construct(Ps_GoogleanalyticsZaraz $module, Context $context)
    {
        $this->module = $module;
        $this->context = $context;
    }

    /**
     * @return false|string
     */
    public function run()
    {
        // Resolve if we should add user ID into the code
        $userId = null;
        if (Configuration::get('GAZ_USERID_ENABLED')
            && $this->context->customer instanceof Customer
            && $this->context->customer->isLogged()
        ) {
            $userId = (int) $this->context->customer->id;
        }

        $this->context->smarty->assign(
            [
                'backOffice' => $this->backOffice,
                'trackBackOffice' => Configuration::get('GAZ_TRACK_BACKOFFICE_ENABLED'),
                'userId' => $userId,
                'gazAccountId' => Tools::safeOutput(Configuration::get('GAZ_ACCOUNT_ID')),
                'gazAnonymizeEnabled' => Configuration::get('GAZ_ANONYMIZE_ENABLED'),
                'gazDebugKey' => Configuration::get('GAZ_DEBUG_KEY'),
                'gazDebugEnabled' => Configuration::get('GAZ_DEBUG_ENABLED'),
            ]
        );

        return $this->module->display(
            $this->module->getLocalPath() . $this->module->name,
            'ps_googleanalytics.tpl'
        );
    }

    /**
     * @param bool $backOffice
     */
    public function setBackOffice($backOffice)
    {
        $this->backOffice = $backOffice;
    }
}
