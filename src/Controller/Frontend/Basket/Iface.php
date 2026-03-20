<?php

declare (strict_types=1);
/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2012
 * @copyright Aimeos (aimeos.org), 2015-2021
 * @package Controller
 * @subpackage Frontend
 */
namespace Aimeos\Controller\Frontend\Basket;

/**
 * Interface for basket frontend controllers.
 *
 * @package Controller
 * @subpackage Frontend
 */
interface Iface
{
    /**
     * Adds values like comments to the basket
     *
     * @param array $values Order values like comment
     * @return \Aimeos\Controller\Frontend\Basket\Iface Basket frontend object for fluent interface
     */
    public function add(array $values): Iface;
    /**
     * Empties the basket and removing all products, addresses, services, etc.
     *
     * @return \Aimeos\Controller\Frontend\Basket\Iface Basket frontend object for fluent interface
     */
    public function clear(): Iface;
    /**
     * Returns the basket object.
     *
     * @return \Aimeos\MShop\Order\Item\Iface Basket holding products, addresses and delivery/payment options
     */
    public function get(): \Aimeos\M_Shop\Order\Item\Iface;
    /**
     * Explicitely persists the basket content
     *
     * @return \Aimeos\Controller\Frontend\Basket\Iface Basket frontend object for fluent interface
     */
    public function save(): Iface;
    /**
     * Sets the new basket type
     *
     * @param string $type Basket type
     * @return \Aimeos\Controller\Frontend\Basket\Iface Basket frontend object for fluent interface
     */
    public function set_type(string $type): Iface;
    /**
     * Creates a new order object from the current basket
     *
     * @return \Aimeos\MShop\Order\Item\Iface Order object including products, addresses and services
     */
    public function store(): \Aimeos\M_Shop\Order\Item\Iface;
    /**
     * Returns the order object for the given ID
     *
     * @param string $id Unique ID of the order object
     * @param array $ref References items that should be fetched too
     * @param bool $default True to add default criteria (user logged in), false if not
     * @return \Aimeos\MShop\Order\Item\Iface Order object including the given parts
     */
    public function load(string $id, array $ref = ['order/address', 'order/coupon', 'order/product', 'order/service'], bool $default = true): \Aimeos\M_Shop\Order\Item\Iface;
    /**
     * Adds a product to the basket of the customer stored in the session
     *
     * @param \Aimeos\MShop\Product\Item\Iface $product Product to add including texts, media, prices, attributes, etc.
     * @param float $quantity Amount of products that should by added
     * @param array $variant List of variant-building attribute IDs that identify an article in a selection product
     * @param array $config List of configurable attribute IDs the customer has chosen from
     * @param array $custom Associative list of attribute IDs as keys and arbitrary values that will be added to the ordered product
     * @param string $stocktype Unique code of the stock type to deliver the products from
     * @param string|null $siteId Unique ID of the site the product should be bought from or NULL for site the product is from
     * @return \Aimeos\Controller\Frontend\Basket\Iface Basket frontend object for fluent interface
     * @throws \Aimeos\Controller\Frontend\Basket\Exception If the product isn't available
     */
    public function add_product(\Aimeos\M_Shop\Product\Item\Iface $product, float $quantity = 1, array $variant = [], array $config = [], array $custom = [], string $stocktype = 'default', ?string $site_id = null): Iface;
    /**
     * Deletes a product item from the basket.
     *
     * @param int $position Position number (key) of the order product item
     * @return \Aimeos\Controller\Frontend\Basket\Iface Basket frontend object for fluent interface
     */
    public function delete_product(int $position): Iface;
    /**
     * Edits the quantity of a product item in the basket.
     *
     * @param int $position Position number (key) of the order product item
     * @param float $quantity New quantiy of the product item
     * @return \Aimeos\Controller\Frontend\Basket\Iface Basket frontend object for fluent interface
     */
    public function update_product(int $position, float $quantity): Iface;
    /**
     * Adds the given coupon code and updates the basket.
     *
     * @param string $code Coupon code entered by the user
     * @throws \Aimeos\Controller\Frontend\Basket\Exception if the coupon code is invalid or not allowed
     * @return \Aimeos\Controller\Frontend\Basket\Iface Basket frontend object for fluent interface
     */
    public function add_coupon(string $code): Iface;
    /**
     * Removes the given coupon code and its effects from the basket.
     *
     * @param string $code Coupon code entered by the user
     * @throws \Aimeos\Controller\Frontend\Basket\Exception if the coupon code is invalid
     * @return \Aimeos\Controller\Frontend\Basket\Iface Basket frontend object for fluent interface
     */
    public function delete_coupon(string $code): Iface;
    /**
     * Adds an address of the customer to the basket
     *
     * @param string $type Address type code like 'payment' or 'delivery'
     * @param array $values Associative list of key/value pairs with address details
     * @param int|null $position Position number (key) of the order address item
     * @return \Aimeos\Controller\Frontend\Basket\Iface Basket frontend object for fluent interface
     */
    public function add_address(string $type, array $values = [], ?int $position = null): Iface;
    /**
     * Removes the address of the given type and position if available
     *
     * @param string $type Address type code like 'payment' or 'delivery'
     * @param int|null $position Position of the address in the list to overwrite
     * @return \Aimeos\Controller\Frontend\Basket\Iface Basket frontend object for fluent interface
     */
    public function delete_address(string $type, ?int $position = null): Iface;
    /**
     * Adds the delivery/payment service including the given configuration
     *
     * @param \Aimeos\MShop\Service\Item\Iface $service Service item selected by the customer
     * @param array $config Associative list of key/value pairs with the options selected by the customer
     * @param int|null $position Position of the address in the list to overwrite
     * @return \Aimeos\Controller\Frontend\Basket\Iface Basket frontend object for fluent interface
     * @throws \Aimeos\Controller\Frontend\Basket\Exception If given service attributes are invalid
     */
    public function add_service(\Aimeos\M_Shop\Service\Item\Iface $service, array $config = [], ?int $position = null): Iface;
    /**
     * Removes the delivery or payment service items from the basket
     *
     * @param string $type Service type code like 'payment' or 'delivery'
     * @param int|null $position Position of the service in the list to overwrite
     * @return \Aimeos\Controller\Frontend\Basket\Iface Basket frontend object for fluent interface
     */
    public function delete_service(string $type, ?int $position = null): Iface;
}