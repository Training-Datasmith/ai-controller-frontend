<?php

declare (strict_types=1);
/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017-2026
 * @package Controller
 * @subpackage Frontend
 */
namespace Aimeos\Controller\Frontend\Customer\Decorator;

/**
 * Base for customer frontend controller decorators
 *
 * @package Controller
 * @subpackage Frontend
 */
abstract class Base extends \Aimeos\Controller\Frontend\Base implements \Aimeos\Controller\Frontend\Common\Decorator\Iface, \Aimeos\Controller\Frontend\Customer\Iface
{
    use \Aimeos\Controller\Frontend\Common\Decorator\Traits;
    /**
     * Initializes the controller decorator.
     *
     * @param \Aimeos\Controller\Frontend\Iface $controller Controller object
     * @param \Aimeos\MShop\ContextIface $context Context object with required objects
     */
    public function __construct(private \Aimeos\Controller\Frontend\Customer\Iface $controller, \Aimeos\M_Shop\Context_Iface $context)
    {
        parent::__construct($context);
    }
    /**
     * Passes unknown methods to wrapped objects.
     *
     * @param string $name Name of the method
     * @param array $param List of method parameter
     * @return mixed Returns the value of the called method
     * @throws \Aimeos\Controller\Frontend\Exception If method call failed
     */
    public function __call(string $name, array $param)
    {
        return @call_user_func_array([$this->controller, $name], $param);
    }
    /**
     * Adds and returns a new customer item object
     *
     * @param array $values Values added to the customer item (new or existing) like "customer.code"
     * @return \Aimeos\Controller\Frontend\Customer\Iface Customer controller for fluent interface
     * @since 2019.04
     */
    public function add(array $values): \Aimeos\Controller\Frontend\Customer\Iface
    {
        $this->controller->add($values);
        return $this;
    }
    /**
     * Adds the given address item to the customer object (not yet stored)
     *
     * @param \Aimeos\MShop\Common\Item\Address\Iface $item Address item to add
     * @param int|null $pos Position (key) in the list of address items or null to add the item at the end
     * @return \Aimeos\Controller\Frontend\Customer\Iface Customer controller for fluent interface
     * @since 2019.04
     */
    public function add_address_item(\Aimeos\M_Shop\Common\Item\Address\Iface $item, ?int $position = null): \Aimeos\Controller\Frontend\Customer\Iface
    {
        $this->controller->add_address_item($item, $position);
        return $this;
    }
    /**
     * Adds the given list item to the customer object (not yet stored)
     *
     * @param string $domain Domain name the referenced item belongs to
     * @param \Aimeos\MShop\Common\Item\Lists\Iface $item List item to add
     * @param \Aimeos\MShop\Common\Item\Iface|null $refItem Referenced item to add or null if list item contains refid value
     * @return \Aimeos\Controller\Frontend\Customer\Iface Customer controller for fluent interface
     * @since 2019.04
     */
    public function add_list_item(string $domain, \Aimeos\M_Shop\Common\Item\Lists\Iface $item, ?\Aimeos\M_Shop\Common\Item\Iface $ref_item = null): \Aimeos\Controller\Frontend\Customer\Iface
    {
        $this->controller->add_list_item($domain, $item, $ref_item);
        return $this;
    }
    /**
     * Adds the given property item to the customer object (not yet stored)
     *
     * @param \Aimeos\MShop\Common\Item\Property\Iface $item Property item to add
     * @return \Aimeos\Controller\Frontend\Customer\Iface Customer controller for fluent interface
     * @since 2019.04
     */
    public function add_property_item(\Aimeos\M_Shop\Common\Item\Property\Iface $item): \Aimeos\Controller\Frontend\Customer\Iface
    {
        $this->controller->add_property_item($item);
        return $this;
    }
    /**
     * Creates a new address item object pre-filled with the given values
     *
     * @param array $values Associative list of key/value pairs for populating the item
     * @return \Aimeos\MShop\Customer\Item\Address\Iface Address item
     * @since 2019.04
     */
    public function create_address_item(array $values = []): \Aimeos\M_Shop\Customer\Item\Address\Iface
    {
        return $this->controller->create_address_item($values);
    }
    /**
     * Creates a new list item object pre-filled with the given values
     *
     * @param array $values Associative list of key/value pairs for populating the item
     * @return \Aimeos\MShop\Common\Item\Lists\Iface List item
     * @since 2019.04
     */
    public function create_list_item(array $values = []): \Aimeos\M_Shop\Common\Item\Lists\Iface
    {
        return $this->controller->create_list_item($values);
    }
    /**
     * Creates a new property item object pre-filled with the given values
     *
     * @param array $values Associative list of key/value pairs for populating the item
     * @return \Aimeos\MShop\Common\Item\Property\Iface Property item
     * @since 2019.04
     */
    public function create_property_item(array $values = []): \Aimeos\M_Shop\Common\Item\Property\Iface
    {
        return $this->controller->create_property_item($values);
    }
    /**
     * Deletes a customer item that belongs to the current authenticated user
     *
     * @return \Aimeos\Controller\Frontend\Customer\Iface Customer controller for fluent interface
     * @since 2017.04
     */
    public function delete(): \Aimeos\Controller\Frontend\Customer\Iface
    {
        $this->controller->delete();
        return $this;
    }
    /**
     * Removes the given address item from the customer object (not yet stored)
     *
     * @param \Aimeos\MShop\Common\Item\Address\Iface $item Address item to remove
     * @return \Aimeos\Controller\Frontend\Customer\Iface Customer controller for fluent interface
     */
    public function delete_address_item(\Aimeos\M_Shop\Common\Item\Address\Iface $item): \Aimeos\Controller\Frontend\Customer\Iface
    {
        $this->controller->delete_address_item($item);
        return $this;
    }
    /**
     * Removes the given list item from the customer object (not yet stored)
     *
     * @param string $domain Domain name the referenced item belongs to
     * @param \Aimeos\MShop\Common\Item\Lists\Iface $item List item to remove
     * @param \Aimeos\MShop\Common\Item\Iface|null $refItem Referenced item to remove or null if only list item should be removed
     * @return \Aimeos\Controller\Frontend\Customer\Iface Customer controller for fluent interface
     */
    public function delete_list_item(string $domain, \Aimeos\M_Shop\Common\Item\Lists\Iface $list_item, ?\Aimeos\M_Shop\Common\Item\Iface $ref_item = null): \Aimeos\Controller\Frontend\Customer\Iface
    {
        $this->controller->delete_list_item($domain, $list_item, $ref_item);
        return $this;
    }
    /**
     * Removes the given property item from the customer object (not yet stored)
     *
     * @param \Aimeos\MShop\Common\Item\Property\Iface $item Property item to remove
     * @return \Aimeos\Controller\Frontend\Customer\Iface Customer controller for fluent interface
     */
    public function delete_property_item(\Aimeos\M_Shop\Common\Item\Property\Iface $item): \Aimeos\Controller\Frontend\Customer\Iface
    {
        $this->controller->delete_property_item($item);
        return $this;
    }
    /**
     * Returns the customer item for the given code
     *
     * This method doesn't check if the customer item belongs to the logged in user!
     *
     * @param string $code Unique customer code
     * @return \Aimeos\MShop\Customer\Item\Iface Customer item
     * @since 2019.04
     */
    public function find(string $code): \Aimeos\M_Shop\Customer\Item\Iface
    {
        return $this->controller->find($code);
    }
    /**
     * Returns the customer item for the current authenticated user
     *
     * @return \Aimeos\MShop\Customer\Item\Iface Customer item
     * @since 2019.04
     */
    public function get(): \Aimeos\M_Shop\Customer\Item\Iface
    {
        return $this->controller->get();
    }
    /**
     * Adds or updates the modified customer item in the storage
     *
     * @return \Aimeos\Controller\Frontend\Customer\Iface Customer controller for fluent interface
     * @since 2019.04
     */
    public function store(): \Aimeos\Controller\Frontend\Customer\Iface
    {
        $this->controller->store();
        return $this;
    }
    /**
     * Sets the domains that will be used when working with the customer item
     *
     * @param array $domains Domain names of the referenced items that should be fetched too
     * @return \Aimeos\Controller\Frontend\Customer\Iface Customer controller for fluent interface
     * @since 2019.04
     */
    public function uses(array $domains): \Aimeos\Controller\Frontend\Customer\Iface
    {
        $this->controller->uses($domains);
        return $this;
    }
    /**
     * Injects the reference of the outmost object
     *
     * @param \Aimeos\Controller\Frontend\Iface $object Reference to the outmost controller or decorator
     * @return \Aimeos\Controller\Frontend\Iface Controller object for chaining method calls
     */
    public function set_object(\Aimeos\Controller\Frontend\Iface $object): \Aimeos\Controller\Frontend\Iface
    {
        parent::set_object($object);
        $this->controller->set_object($object);
        return $this;
    }
    /**
     * Returns the frontend controller
     *
     * @return \Aimeos\Controller\Frontend\Iface Frontend controller object
     * @since 2017.04
     */
    protected function get_controller(): \Aimeos\Controller\Frontend\Iface
    {
        return $this->controller;
    }
}