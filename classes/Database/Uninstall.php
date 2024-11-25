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

namespace PickleBoxer\Ps_GoogleanalyticsZaraz\Database;

use Db;
use Tab;
use Validate;

class Uninstall
{
    /**
     * uninstallTables
     *
     * @return bool
     */
    public function uninstallTables()
    {
        $sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ganalyticszaraz`';
        $sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ganalyticszaraz_data`';

        foreach ($sql as $query) {
            if (!Db::getInstance()->execute($query)) {
                return false;
            }
        }

        return true;
    }

    /**
     * uninstall tab
     *
     * @return bool
     */
    public function uninstallTab()
    {
        $result = true;
        $id_tab = (int) Tab::getIdFromClassName('AdminGanalyticsZarazAjax');
        $tab = new Tab($id_tab);
        if (Validate::isLoadedObject($tab)) {
            $result = $tab->delete();
        }

        return $result;
    }
}
