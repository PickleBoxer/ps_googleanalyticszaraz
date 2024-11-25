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
$(document).ready(function () {

    // prestashop 1.7 on product click
    $('article[data-id-product]').find('button.quick-view').on('click', function () {
        let $product = $(this).closest('article[data-id-product]');
        let idProduct = $product.data('id-product');
        let idProductAttribute = $product.data('id-product-attribute') | 0;
        let nameProduct = $product.find('.product-title').text();
        let priceProduct = $product.find('.price').text().replace(/[^0-9.,]/g, '');
        // if nameProduct is empy dont send the event
        if (!nameProduct) {
            return;
        }
        zaraz.ecommerce('Product Clicked', {
            product_id: idProduct,
            variant: idProductAttribute,
            name: nameProduct,
            price: priceProduct,
        });
    });

    // prestashop 1.7 on add to wishlist
    let wishlistButtons = document.querySelectorAll('.wishlist-button-add');

    wishlistButtons.forEach(button => {
        button.addEventListener('click', function () {
            let article = this.closest('article[data-id-product]');
            let idProduct = article.getAttribute('data-id-product');
            let nameProduct = article.querySelector('.product-title').textContent;

            if (!nameProduct) {
                return;
            }
            zaraz.ecommerce('Product Added to Wishlist', {
                product_id: idProduct,
                name: nameProduct,
            });
        });
    });

    function addClickEvent(selector, eventName, dataFunction) {
        let elements = document.querySelectorAll(selector);

        elements.forEach(element => {
            element.addEventListener('click', function () {
                // Log the click with a 50% probability
                if (Math.random() < 0.5) {
                    zaraz.track(eventName, dataFunction(this));
                }
            });
        });
    }

    // prestashop 1.7 on Menu Click
    addClickEvent('.dropdown-item', 'click_menu', (element) => ({ voce_menu: element.textContent }));

    // prestashop 1.7 on All Links Click
    addClickEvent('a', 'click_link', (element) => ({ testo_cliccato: element.textContent }));

    // prestashop 1.7 on Btn Click
    addClickEvent('.btn', 'click_btn', (element) => ({ testo_bottone: element.textContent }));

});