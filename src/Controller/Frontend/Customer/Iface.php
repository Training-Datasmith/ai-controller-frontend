<?php

declare (strict_types=1);
/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017-2026
 * @package Controller
 * @subpackage Frontend
 */
namespace Aimeos\Controller\Frontend\Customer;

/**
 * Interface for customer frontend controllers
 *
 * @package Controller
 * @subpackage Frontend
 */
interface Iface
{
    /**
     * Adds the values to the customer object (not yet stored)
     *
     * @param array $values Values added to the customer item (new or existing) like "customer.code"
     * @return \Aimeos\Controller\Frontend\Customer\Iface Customer controller for fluent interface
     * @since 2019.04
     */
    public function add(array $values): Iface;
    /**
     * Adds the given address item to the customer object (not yet stored)
     *
     * @param \Aimeos\MShop\Common\Item\Address\Iface $item Address item to add
     * @param int|null $idx Key in the list of address items or null to add the item at the end
     * @return \Aimeos\Controller\Frontend\Customer\Iface Customer controller for fluent interface
     * @since 2019.04
     */
    public function add_address_item(\Aimeos\M_Shop\Common\Item\Address\Iface $item, ?int $idx = null): Iface;
    /**
     * Adds the given list item to the customer object (not yet stored)
     *
     * @param string $domain Domain name the referenced item belongs to
     * @param \Aimeos\MShop\Common\Item\Lists\Iface $item List item to add
     * @param \Aimeos\MShop\Common\Item\Iface|null $refItem Referenced item to add or null if list item contains refid value
     * @return \Aimeos\Controller\Frontend\Customer\Iface Customer controller for fluent interface
     * @since 2019.04
     */
    public function add_list_item(string $domain, \Aimeos\M_Shop\Common\Item\Lists\Iface $item, ?\Aimeos\M_Shop\Common\Item\Iface $ref_item = null): Iface;
    /**
     * Adds the given property item to the customer object (not yet stored)
     *
     * @param \Aimeos\MShop\Common\Item\Property\Iface $item Property item to add
     * @return \Aimeos\Controller\Frontend\Customer\Iface Customer controller for fluent interface
     * @since 2019.04
     */
    public function add_property_item(\Aimeos\M_Shop\Common\Item\Property\Iface $item): Iface;
    /**
     * Creates a new address item object pre-filled with the given values
     *
     * @param array $values Associative list of key/value pairs for populating the item
     * @return \Aimeos\MShop\Customer\Item\Address\Iface Address item
     * @since 2019.04
     */
    public function create_address_item(array $values = []): \Aimeos\M_Shop\Customer\Item\Address\Iface;
    /**
     * Creates a new list item object pre-filled with the given values
     *
     * @param array $values Associative list of key/value pairs for populating the item
     * @return \Aimeos\MShop\Common\Item\Lists\Iface List item
     * @since 2019.04
     */
    public function create_list_item(array $values = []): \Aimeos\M_Shop\Common\Item\Lists\Iface;
    /**
     * Creates a new property item object pre-filled with the given values
     *
     * @param array $values Associative list of key/value pairs for populating the item
     * @return \Aimeos\MShop\Common\Item\Property\Iface Property item
     * @since 2019.04
     */
    public function create_property_item(array $values = []): \Aimeos\M_Shop\Common\Item\Property\Iface;
    /**
     * Deletes a customer item that belongs to the current authenticated user
     *
     * @return \Aimeos\Controller\Frontend\Customer\Iface Customer controller for fluent interface
     * @since 2019.04
     */
    public function delete(): Iface;
    /**
     * Removes the given address item from the customer object (not yet stored)
     *
     * @param \Aimeos\MShop\Common\Item\Address\Iface $item Address item to remove
     * @return \Aimeos\Controller\Frontend\Customer\Iface Customer controller for fluent interface
     */
    public function delete_address_item(\Aimeos\M_Shop\Common\Item\Address\Iface $item): Iface;
    /**
     * Removes the given list item from the customer object (not yet stored)
     *
     * @param string $domain Domain name the referenced item belongs to
     * @param \Aimeos\MShop\Common\Item\Lists\Iface $item List item to remove
     * @param \Aimeos\MShop\Common\Item\Iface|null $refItem Referenced item to remove or null if only list item should be removed
     * @return \Aimeos\Controller\Frontend\Customer\Iface Customer controller for fluent interface
     */
    public function delete_list_item(string $domain, \Aimeos\M_Shop\Common\Item\Lists\Iface $list_item, ?\Aimeos\M_Shop\Common\Item\Iface $ref_item = null): Iface;
    /**
     * Removes the given property item from the customer object (not yet stored)
     *
     * @param \Aimeos\MShop\Common\Item\Property\Iface $item Property item to remove
     * @return \Aimeos\Controller\Frontend\Customer\Iface Customer controller for fluent interface
     */
    public function delete_property_item(\Aimeos\M_Shop\Common\Item\Property\Iface $item): Iface;
    /**
     * Returns the customer item for the given code
     *
     * This method doesn't check if the customer item belongs to the logged in user!
     *
     * @param string $code Unique customer code
     * @return \Aimeos\MShop\Customer\Item\Iface Customer item
     * @since 2019.04
     */
    public function find(string $code): \Aimeos\M_Shop\Customer\Item\Iface;
    /**
     * Returns the customer item for the current authenticated user
     *
     * @return \Aimeos\MShop\Customer\Item\Iface Customer item
     * @since 2019.04
     */
    public function get(): \Aimeos\M_Shop\Customer\Item\Iface;
    /**
     * Adds or updates the modified customer item in the storage
     *
     * @return \Aimeos\Controller\Frontend\Customer\Iface Customer controller for fluent interface
     * @since 2019.04
     */
    public function store(): Iface;
    /**
     * Sets the domains that will be used when working with the customer item
     *
     * @param array $domains Domain names of the referenced items that should be fetched too
     * @return \Aimeos\Controller\Frontend\Customer\Iface Customer controller for fluent interface
     * @since 2019.04
     */
    public function uses(array $domains): Iface;
}