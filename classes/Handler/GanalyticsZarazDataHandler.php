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

namespace PickleBoxer\Ps_GoogleanalyticsZaraz\Handler;

use PickleBoxer\Ps_GoogleanalyticsZaraz\Repository\GanalyticsZarazDataRepository;

class GanalyticsZarazDataHandler
{
    private $ganalyticsZarazDataRepository;
    private $cartId;
    private $shopId;

    /**
     * __construct
     *
     * @param int $cartId
     * @param int $shopId
     */
    public function __construct($cartId, $shopId)
    {
        $this->ganalyticsZarazDataRepository = new GanalyticsZarazDataRepository();
        $this->cartId = (int) $cartId;
        $this->shopId = (int) $shopId;
    }

    /**
     * readData
     *
     * @return array
     */
    public function readData()
    {
        $dataReturned = $this->ganalyticsZarazDataRepository->findDataByCartIdAndShopId(
            $this->cartId,
            $this->shopId
        );

        if (false === $dataReturned) {
            return [];
        }

        return $this->jsonDecodeValidJson($dataReturned);
    }

    /**
     * Deletes all persisted data, probably because it was flushed.
     *
     * @return bool
     */
    public function deleteData()
    {
        return $this->ganalyticsZarazDataRepository->deleteRow(
            $this->cartId,
            $this->shopId
        );
    }

    /**
     * Stores event into data repository so we can output it
     * on first available chance.
     *
     * @param string $dataToPersist
     *
     * @return bool
     */
    public function persistData($dataToPersist)
    {
        // Try to get current data
        $currentData = $this->readData();

        // If no data has been persisted yet, we create a new array, otherwise
        // we add it to the previous events stored.
        if (!empty($currentData)) {
            $newData = $currentData;
            $newData[] = $dataToPersist;
        } else {
            $newData = [$dataToPersist];
        }

        return $this->ganalyticsZarazDataRepository->addNewRow(
            (int) $this->cartId,
            (int) $this->shopId,
            json_encode($newData)
        );
    }

    /**
     * Check if the json is valid and returns an empty array if not
     *
     * @param string $json
     *
     * @return array
     */
    protected function jsonDecodeValidJson($json)
    {
        $array = json_decode($json, true);

        if (JSON_ERROR_NONE === json_last_error()) {
            return $array;
        }

        return [];
    }
}
