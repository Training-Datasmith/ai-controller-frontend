<?php

declare (strict_types=1);
/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2021-2026
 * @package Controller
 * @subpackage Frontend
 */
namespace Aimeos\Controller\Frontend\Common\Decorator;

/**
 * Decorator trait class for controllers
 *
 * @package Controller
 * @subpackage Frontend
 */
trait Traits
{
    /**
     * Adds the given compare, combine or sort expression to the list of expressions
     *
     * @param \Aimeos\Base\Criteria\Expression\Iface|null $expr Compare, combine or sort expression
     * @return \Aimeos\Controller\Frontend\Iface Controller object for chaining method calls
     */
    public function add_expression(?\Aimeos\Base\Criteria\Expression\Iface $expr = null): \Aimeos\Controller\Frontend\Iface
    {
        $this->get_controller()->add_expression($expr);
        return $this;
    }
    /**
     * Returns the compare and combine expressions added by addExpression()
     *
     * @return array List of compare and combine expressions
     */
    public function get_conditions(): array
    {
        $this->get_controller()->get_conditions();
    }
    /**
     * Returns the compare and combine expressions added by addExpression()
     *
     * @return array List of sort expressions
     */
    public function get_sortations(): array
    {
        $this->get_controller()->get_sortations();
    }
    /**
     * Returns the frontend controller
     *
     * @return \Aimeos\Controller\Frontend\Iface Frontend controller object
     */
    abstract protected function get_controller(): \Aimeos\Controller\Frontend\Iface;
}