<?php

declare (strict_types=1);
/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2016-2026
 * @package Controller
 * @subpackage Frontend
 */
namespace Aimeos\Controller\Frontend\Service\Decorator;

use Psr\Http\Message\Response_Interface;
use Psr\Http\Message\Server_Request_Interface;
/**
 * Base for service frontend controller decorators
 *
 * @package Controller
 * @subpackage Frontend
 */
abstract class Base extends \Aimeos\Controller\Frontend\Base implements \Aimeos\Controller\Frontend\Common\Decorator\Iface, \Aimeos\Controller\Frontend\Service\Iface
{
    use \Aimeos\Controller\Frontend\Common\Decorator\Traits;
    /**
     * Initializes the controller decorator.
     *
     * @param \Aimeos\Controller\Frontend\Iface $controller Controller object
     * @param \Aimeos\MShop\ContextIface $context Context object with required objects
     */
    public function __construct(private \Aimeos\Controller\Frontend\Service\Iface $controller, \Aimeos\M_Shop\Context_Iface $context)
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
     * Adds generic condition for filtering services
     *
     * @param string $operator Comparison operator, e.g. "==", "!=", "<", "<=", ">=", ">", "=~", "~="
     * @param string $key Search key defined by the service manager, e.g. "service.status"
     * @param array|string $value Value or list of values to compare to
     * @return \Aimeos\Controller\Frontend\Service\Iface Service controller for fluent interface
     * @since 2019.04
     */
    public function compare(string $operator, string $key, $value): \Aimeos\Controller\Frontend\Service\Iface
    {
        $this->controller->compare($operator, $key, $value);
        return $this;
    }
    /**
     * Sets the global configuration for the service providers
     *
     * @param array $conf Associative list of global provider configuration options
     * @return \Aimeos\Controller\Frontend\Service\Iface Service controller for fluent interface
     * @since 2024.10
     */
    public function config(array $conf): \Aimeos\Controller\Frontend\Service\Iface
    {
        $this->controller->config($conf);
        return $this;
    }
    /**
     * Returns the service for the given code
     *
     * @param string $code Unique service code
     * @return \Aimeos\MShop\Service\Item\Iface Service item including the referenced domains items
     * @since 2019.04
     */
    public function find(string $code): \Aimeos\M_Shop\Service\Item\Iface
    {
        return $this->controller->find($code);
    }
    /**
     * Creates a search function string for the given name and parameters
     *
     * @param string $name Name of the search function without parenthesis, e.g. "service:has"
     * @param array $params List of parameters for the search function with numeric keys starting at 0
     * @return string Search function string that can be used in compare()
     */
    public function function(string $name, array $params): string
    {
        return $this->controller->function($name, $params);
    }
    /**
     * Returns the service for the given ID
     *
     * @param string $id Unique service ID
     * @return \Aimeos\MShop\Service\Item\Iface Service item including the referenced domains items
     * @since 2019.04
     */
    public function get(string $id): \Aimeos\M_Shop\Service\Item\Iface
    {
        return $this->controller->get($id);
    }
    /**
     * Returns the service item for the given ID
     *
     * @param string $serviceId Unique service ID
     * @return \Aimeos\MShop\Service\Provider\Iface Service provider object
     */
    public function get_provider(string $service_id): \Aimeos\M_Shop\Service\Provider\Iface
    {
        return $this->controller->get_provider($service_id);
    }
    /**
     * Returns the service providers for the given type
     *
     * @return \Aimeos\Map List of service IDs as keys and service provider objects as values
     */
    public function get_providers(): \Aimeos\Map
    {
        return $this->controller->get_providers();
    }
    /**
     * Parses the given array and adds the conditions to the list of conditions
     *
     * @param array $conditions List of conditions, e.g. ['&&' => [['>' => ['service.status' => 0]], ['==' => ['service.type' => 'default']]]]
     * @return \Aimeos\Controller\Frontend\Service\Iface Service controller for fluent interface
     * @since 2019.04
     */
    public function parse(array $conditions): \Aimeos\Controller\Frontend\Service\Iface
    {
        $this->controller->parse($conditions);
        return $this;
    }
    /**
     * Processes the payment service for the given order
     *
     * @param \Aimeos\MShop\Order\Item\Iface $orderItem Order which should be processed
     * @param string $serviceId Unique service item ID
     * @param array $urls Associative list of keys and the corresponding URLs
     * 	(keys are payment.url-self, payment.url-success, payment.url-update)
     * @param array $params Request parameters and order service attributes
     * @return \Aimeos\MShop\Common\Helper\Form\Iface|null Form object with URL, parameters, etc.
     * 	or null if no form data is required
     */
    public function process(\Aimeos\M_Shop\Order\Item\Iface $order_item, string $service_id, array $urls, array $params): ?\Aimeos\M_Shop\Common\Helper\Form\Iface
    {
        return $this->controller->process($order_item, $service_id, $urls, $params);
    }
    /**
     * Returns the services filtered by the previously assigned conditions
     *
     * @param int &$total Parameter where the total number of found services will be stored in
     * @return \Aimeos\Map Ordered list of items implementing \Aimeos\MShop\Service\Item\Iface
     * @since 2019.04
     */
    public function search(?int &$total = null): \Aimeos\Map
    {
        return $this->controller->search($total);
    }
    /**
     * Sets the start value and the number of returned services for slicing the list of found services
     *
     * @param int $start Start value of the first attribute in the list
     * @param int $limit Number of returned services
     * @return \Aimeos\Controller\Frontend\Service\Iface Service controller for fluent interface
     * @since 2019.04
     */
    public function slice(int $start, int $limit): \Aimeos\Controller\Frontend\Service\Iface
    {
        $this->controller->slice($start, $limit);
        return $this;
    }
    /**
     * Sets the sorting of the result list
     *
     * @param string|null $key Sorting of the result list like "position", null for no sorting
     * @return \Aimeos\Controller\Frontend\Service\Iface Service controller for fluent interface
     * @since 2019.04
     */
    public function sort(?string $key = null): \Aimeos\Controller\Frontend\Service\Iface
    {
        $this->controller->sort($key);
        return $this;
    }
    /**
     * Adds attribute types for filtering
     *
     * @param array|string $code Service type or list of types
     * @return \Aimeos\Controller\Frontend\Service\Iface Service controller for fluent interface
     * @since 2019.04
     */
    public function type($code): \Aimeos\Controller\Frontend\Service\Iface
    {
        $this->controller->type($code);
        return $this;
    }
    /**
     * Updates the order status sent by payment gateway notifications
     *
     * @param ServerRequestInterface $request Request object
     * @param ResponseInterface $response Response object that will contain HTTP status and response body
     * @param string $code Unique code of the service used for the current order
     * @return \Psr\Http\Message\ResponseInterface Response object
     */
    public function update_push(Server_Request_Interface $request, Response_Interface $response, string $code): \Psr\Http\Message\Response_Interface
    {
        return $this->controller->update_push($request, $response, $code);
    }
    /**
     * Updates the payment or delivery status for the given request
     *
     * @param ServerRequestInterface $request Request object with parameters and request body
     * @param string $code Unique code of the service used for the current order
     * @param string $orderid ID of the order whose payment status should be updated
     * @return \Aimeos\MShop\Order\Item\Iface $orderItem Order item that has been updated
     */
    public function update_sync(Server_Request_Interface $request, string $code, string $orderid): \Aimeos\M_Shop\Order\Item\Iface
    {
        return $this->controller->update_sync($request, $code, $orderid);
    }
    /**
     * Sets the referenced domains that will be fetched too when retrieving items
     *
     * @param array $domains Domain names of the referenced items that should be fetched too
     * @return \Aimeos\Controller\Frontend\Service\Iface Service controller for fluent interface
     * @since 2019.04
     */
    public function uses(array $domains): \Aimeos\Controller\Frontend\Service\Iface
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
     */
    protected function get_controller(): \Aimeos\Controller\Frontend\Iface
    {
        return $this->controller;
    }
}