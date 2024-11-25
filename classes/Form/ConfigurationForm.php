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

namespace PickleBoxer\Ps_GoogleanalyticsZaraz\Form;

use AdminController;
use Configuration;
use Context;
use HelperForm;
use OrderState;
use Ps_GoogleanalyticsZaraz;
use Tools;

class ConfigurationForm
{
    private $module;

    public function __construct(Ps_GoogleanalyticsZaraz $module)
    {
        $this->module = $module;
    }

    /**
     * generate
     *
     * @return string
     */
    public function generate()
    {
        // Get default language
        $default_lang = (int) Configuration::get('PS_LANG_DEFAULT');

        $helper = new HelperForm();

        // Module, token and currentIndex
        $helper->module = $this->module;
        $helper->name_controller = $this->module->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->module->name;

        // Language
        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;

        // Title and toolbar
        $helper->title = $this->module->displayName;
        $helper->show_toolbar = true;        // false -> remove toolbar
        $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
        $helper->submit_action = 'submit' . $this->module->name;
        $helper->toolbar_btn = [
            'save' => [
                'desc' => $this->module->getTranslator()->trans('Save', [], 'Modules.Googleanalyticszaraz.Admin'),
                'href' => AdminController::$currentIndex . '&configure=' . $this->module->name . '&save=' . $this->module->name .
                '&token=' . $helper->token,
            ],
            'back' => [
                'href' => AdminController::$currentIndex . '&token=' . $helper->token,
                'desc' => $this->module->getTranslator()->trans('Back to list', [], 'Modules.Googleanalyticszaraz.Admin'),
            ],
        ];

        $fields_form = [];
        // Init Fields form array
        $fields_form[0]['form'] = [
            'legend' => [
                'title' => $this->module->getTranslator()->trans('Settings', [], 'Modules.Googleanalyticszaraz.Admin'),
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => $this->module->getTranslator()->trans('Google Analytics Tracking ID', [], 'Modules.Googleanalyticszaraz.Admin'),
                    'name' => 'GAZ_ACCOUNT_ID',
                    'size' => 20,
                    'required' => true,
                    'desc' => $this->module->getTranslator()->trans('This information is available in your Google Analytics account. Google Analytics 4 tracking ID starts with "G-".', [], 'Modules.Googleanalyticszaraz.Admin'),
                ],
                [
                    'type' => 'switch',
                    'label' => $this->module->getTranslator()->trans('Enable User ID tracking', [], 'Modules.Googleanalyticszaraz.Admin'),
                    'name' => 'GAZ_USERID_ENABLED',
                    'desc' => $this->module->getTranslator()->trans('This option adds unique user ID to the tag to better track the customer. Use this option only if it complies with laws in your country.', [], 'Modules.Googleanalyticszaraz.Admin'),
                    'values' => [
                        [
                            'id' => 'gaz_userid_enabled',
                            'value' => 1,
                            'label' => $this->module->getTranslator()->trans('Yes', [], 'Modules.Googleanalyticszaraz.Admin'),
                        ],
                        [
                            'id' => 'gaz_userid_disabled',
                            'value' => 0,
                            'label' => $this->module->getTranslator()->trans('No', [], 'Modules.Googleanalyticszaraz.Admin'),
                        ],
                    ],
                ],
                [
                    'type' => 'switch',
                    'label' => $this->module->getTranslator()->trans('Anonymize IP', [], 'Modules.Googleanalyticszaraz.Admin'),
                    'name' => 'GAZ_ANONYMIZE_ENABLED',
                    'desc' => $this->module->getTranslator()->trans('Use this option to anonymize the visitor’s IP to comply with data privacy laws in some countries', [], 'Modules.Googleanalyticszaraz.Admin'),
                    'values' => [
                        [
                            'id' => 'gaz_anonymize_enabled',
                            'value' => 1,
                            'label' => $this->module->getTranslator()->trans('Yes', [], 'Modules.Googleanalyticszaraz.Admin'),
                        ],
                        [
                            'id' => 'gaz_anonymize_disabled',
                            'value' => 0,
                            'label' => $this->module->getTranslator()->trans('No', [], 'Modules.Googleanalyticszaraz.Admin'),
                        ],
                    ],
                ],
                [
                    'type' => 'switch',
                    'label' => $this->module->getTranslator()->trans('Enable Back Office Tracking', [], 'Modules.Googleanalyticszaraz.Admin'),
                    'name' => 'GAZ_TRACK_BACKOFFICE_ENABLED',
                    'desc' => $this->module->getTranslator()->trans('Use this option to enable the tracking inside the Back Office', [], 'Modules.Googleanalyticszaraz.Admin'),
                    'values' => [
                        [
                            'id' => 'gaz_track_backoffice',
                            'value' => 1,
                            'label' => $this->module->getTranslator()->trans('Yes', [], 'Modules.Googleanalyticszaraz.Admin'),
                        ],
                        [
                            'id' => 'gaz_do_not_track_backoffice',
                            'value' => 0,
                            'label' => $this->module->getTranslator()->trans('No', [], 'Modules.Googleanalyticszaraz.Admin'),
                        ],
                    ],
                ],
                [
                    'type' => 'select',
                    'label' => $this->module->getTranslator()->trans('Canceled order states', [], 'Modules.Googleanalyticszaraz.Admin'),
                    'name' => 'GAZ_CANCELLED_STATES',
                    'desc' => $this->module->getTranslator()->trans('Choose order states in which you consider the given order canceled. This will usually be the default "Canceled" state, but some stores may have extra states like "Returned", etc.', [], 'Modules.Googleanalyticszaraz.Admin'),
                    'class' => 'chosen',
                    'multiple' => true,
                    'options' => [
                        'query' => OrderState::getOrderStates((int) Context::getContext()->language->id),
                        'id' => 'id_order_state',
                        'name' => 'name',
                    ],
                ],
                [
                    'type' => 'switch',
                    'label' => $this->module->getTranslator()->trans('Re-send failed orders', [], 'Modules.Googleanalyticszaraz.Admin'),
                    'name' => 'GAZ_BACKLOAD_ENABLED',
                    'desc' => $this->module->getTranslator()->trans('This option will resend all orders that failed to be sent normally in front-office, due to failures or ad-blockers.', [], 'Modules.Googleanalyticszaraz.Admin'),
                    'values' => [
                        [
                            'id' => 'gaz_backload_enabled',
                            'value' => 1,
                            'label' => $this->module->getTranslator()->trans('Yes', [], 'Modules.Googleanalyticszaraz.Admin'),
                        ],
                        [
                            'id' => 'gaz_backload_disabled',
                            'value' => 0,
                            'label' => $this->module->getTranslator()->trans('No', [], 'Modules.Googleanalyticszaraz.Admin'),
                        ],
                    ],
                ],
                [
                    'type' => 'text',
                    'label' => $this->module->getTranslator()->trans('Failed orders period', [], 'Modules.Googleanalyticszaraz.Admin'),
                    'name' => 'GAZ_BACKLOAD_DAYS',
                    'class' => 'input fixed-width-md',
                    'suffix' => 'days',
                    'desc' => $this->module->getTranslator()->trans('If you want to resend failed orders, specify how many days back the module should look for them. Default: 30.', [], 'Modules.Googleanalyticszaraz.Admin'),
                ],
                [
                    'type' => 'text',
                    'label' => $this->module->getTranslator()->trans('Zaraz Debug Key', [], 'Modules.Googleanalyticszaraz.Admin'),
                    'name' => 'GAZ_DEBUG_KEY',
                    'desc' => $this->module->getTranslator()->trans('Enter your Zaraz debug key.', [], 'Modules.Googleanalyticszaraz.Admin'),
                ],
                [
                    'type' => 'switch',
                    'label' => $this->module->getTranslator()->trans('Enable Debug', [], 'Modules.Googleanalyticszaraz.Admin'),
                    'name' => 'GAZ_DEBUG_ENABLED',
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'gaz_debug_active_on',
                            'value' => 1,
                            'label' => $this->module->getTranslator()->trans('Enabled', [], 'Admin.Global'),
                        ],
                        [
                            'id' => 'gaz_debug_active_off',
                            'value' => 0,
                            'label' => $this->module->getTranslator()->trans('Disabled', [], 'Admin.Global'),
                        ],
                    ],
                    'desc' => $this->module->getTranslator()->trans('Enable or disable debug mode.', [], 'Modules.Googleanalyticszaraz.Admin'),
                ],
            ],
            'submit' => [
                'title' => $this->module->getTranslator()->trans('Save', [], 'Modules.Googleanalyticszaraz.Admin'),
            ],
        ];

        // Load current value
        $helper->fields_value['GAZ_ACCOUNT_ID'] = Configuration::get('GAZ_ACCOUNT_ID');
        $helper->fields_value['GAZ_USERID_ENABLED'] = (bool) Configuration::get('GAZ_USERID_ENABLED');
        $helper->fields_value['GAZ_ANONYMIZE_ENABLED'] = (bool) Configuration::get('GAZ_ANONYMIZE_ENABLED');
        $helper->fields_value['GAZ_TRACK_BACKOFFICE_ENABLED'] = (bool) Configuration::get('GAZ_TRACK_BACKOFFICE_ENABLED');
        $helper->fields_value['GAZ_CANCELLED_STATES[]'] = json_decode(Configuration::get('GAZ_CANCELLED_STATES'), true);
        $helper->fields_value['GAZ_BACKLOAD_ENABLED'] = (bool) Configuration::get('GAZ_BACKLOAD_ENABLED');
        $helper->fields_value['GAZ_BACKLOAD_DAYS'] = (int) Configuration::get('GAZ_BACKLOAD_DAYS');
        $helper->fields_value['GAZ_DEBUG_KEY'] = Configuration::get('GAZ_DEBUG_KEY');
        $helper->fields_value['GAZ_DEBUG_ENABLED'] = (bool) Configuration::get('GAZ_DEBUG_ENABLED');

        return $helper->generateForm($fields_form);
    }

    /**
     * treat the form datas if submited
     *
     * @return string
     */
    public function treat()
    {
        $gazAccountId = Tools::getValue('GAZ_ACCOUNT_ID');
        $gazUserIdEnabled = Tools::getValue('GAZ_USERID_ENABLED');
        $gazAnonymizeEnabled = Tools::getValue('GAZ_ANONYMIZE_ENABLED');
        $gazTrackBackOffice = Tools::getValue('GAZ_TRACK_BACKOFFICE_ENABLED');
        $gazCancelledStates = Tools::getValue('GAZ_CANCELLED_STATES');
        $gazBackloadEnabled = Tools::getValue('GAZ_BACKLOAD_ENABLED');
        $gazBackloadDays = Tools::getValue('GAZ_BACKLOAD_DAYS');
        $gazDebugKey = Tools::getValue('GAZ_DEBUG_KEY');
        $gazDebugEnabled = Tools::getValue('GAZ_DEBUG_ENABLED');

        if (!empty($gazAccountId)) {
            Configuration::updateValue('GAZ_ACCOUNT_ID', $gazAccountId);
        }

        if (null !== $gazUserIdEnabled) {
            Configuration::updateValue('GAZ_USERID_ENABLED', (bool) $gazUserIdEnabled);
        }

        if (null !== $gazAnonymizeEnabled) {
            Configuration::updateValue('GAZ_ANONYMIZE_ENABLED', (bool) $gazAnonymizeEnabled);
        }

        if (null !== $gazTrackBackOffice) {
            Configuration::updateValue('GAZ_TRACK_BACKOFFICE_ENABLED', (bool) $gazTrackBackOffice);
        }

        if (null !== $gazBackloadEnabled) {
            Configuration::updateValue('GAZ_BACKLOAD_ENABLED', (bool) $gazBackloadEnabled);
        }

        if (null !== $gazBackloadDays) {
            Configuration::updateValue('GAZ_BACKLOAD_DAYS', (int) $gazBackloadDays);
        }

        if ($gazCancelledStates === false) {
            Configuration::updateValue('GAZ_CANCELLED_STATES', '');
        } else {
            Configuration::updateValue('GAZ_CANCELLED_STATES', json_encode($gazCancelledStates));
        }

        if (null !== $gazDebugKey) {
            Configuration::updateValue('GAZ_DEBUG_KEY', $gazDebugKey);
        }

        if (null !== $gazDebugEnabled) {
            Configuration::updateValue('GAZ_DEBUG_ENABLED', (bool) $gazDebugEnabled);
        }

        return $this->module->displayConfirmation($this->module->getTranslator()->trans('Settings updated successfully.', [], 'Modules.Googleanalyticszaraz.Admin'));
    }
}
