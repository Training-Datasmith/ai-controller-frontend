<?php

declare (strict_types=1);
/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017-2026
 * @package Controller
 * @subpackage Frontend
 */
namespace Aimeos\Controller\Frontend\Basket\Decorator;

/**
 * Bundle product handling
 *
 * @package Controller
 * @subpackage Frontend
 */
class Bundle extends \Aimeos\Controller\Frontend\Basket\Decorator\Base implements \Aimeos\Controller\Frontend\Basket\Iface, \Aimeos\Controller\Frontend\Common\Decorator\Iface
{
    /**
     * Adds a product to the basket of the customer stored in the session
     *
     * @param \Aimeos\MShop\Product\Item\Iface $product Product to add including texts, media, prices, attributes, etc.
     * @param float $quantity Amount of products that should by added
     * @param array $variant List of variant-building attribute IDs that identify an article in a selection product
     * @param array $config List of configurable attribute IDs the customer has chosen from
     * @param array $custom Associative list of attribute IDs as keys and arbitrary values that will be added to the ordered product
     * @param string $stocktype Unique code of the stock type to deliver the products from
     * @param string|null $supplierid Unique supplier ID the product is from
     * @param string|null $siteid Unique site ID the product is from or null for siteid of the product item
     * @return \Aimeos\Controller\Frontend\Basket\Iface Basket frontend object for fluent interface
     * @throws \Aimeos\Controller\Frontend\Basket\Exception If the product isn't available
     */
    public function add_product(\Aimeos\M_Shop\Product\Item\Iface $product, float $quantity = 1, array $variant = [], array $config = [], array $custom = [], string $stocktype = 'default', ?string $site_id = null): \Aimeos\Controller\Frontend\Basket\Iface
    {
        if ($product->get_type() !== 'bundle') {
            $this->get_controller()->add_product($product, $quantity, $variant, $config, $custom, $stocktype, $site_id);
            return $this;
        }
        $quantity = $this->call('checkQuantity', $product, $quantity);
        $this->call('checkAttributes', [$product], 'custom', array_keys($custom));
        $this->call('checkAttributes', [$product], 'config', array_keys($config));
        $prices = $product->get_ref_items('price', 'default', 'default');
        $hidden = $product->get_ref_items('attribute', null, 'hidden');
        $cust_attr = $this->call('getOrderProductAttributes', 'custom', array_keys($custom), $custom);
        $conf_attr = $this->call('getOrderProductAttributes', 'config', array_keys($config), [], $config);
        $hide_attr = $this->call('getOrderProductAttributes', 'hidden', $hidden->keys()->to_array());
        $order_product_item = \Aimeos\M_Shop::create($this->context(), 'order')->create_product()->copy_from($product)->set_quantity($quantity)->set_stock_type($stocktype)->set_site_id($site_id ?: $product->get_site_id())->set_attribute_items(array_merge($cust_attr, $conf_attr, $hide_attr))->set_products($this->get_bundle_products($product, $quantity, $stocktype));
        $price = $this->call('calcPrice', $order_product_item, $prices, $quantity);
        $order_product_item->set_price($price)->set_site_id($site_id ?: $price->get_site_id())->set_vendor($this->get_vendor($site_id ?: $price->get_site_id()));
        $this->get_controller()->get()->add_product($order_product_item);
        $this->get_controller()->save();
        return $this;
    }
    /**
     * Adds the bundled products to the order product item.
     *
     * @param \Aimeos\MShop\Product\Item\Iface $product Bundle product item
     * @param float $quantity Amount of products that should by added
     * @param string $stocktype Unique code of the stock type to deliver the products from
     * @return \Aimeos\MShop\Order\Item\Product\Iface[] List of order product item from bundle
     */
    protected function get_bundle_products(\Aimeos\M_Shop\Product\Item\Iface $product, float $quantity, string $stocktype): array
    {
        $order_products = [];
        $order_manager = \Aimeos\M_Shop::create($this->context(), 'order');
        foreach ($product->get_ref_items('product', null, 'default') as $item) {
            $prices = $item->get_ref_items('price', 'default', 'default');
            $order_product = $order_manager->create_product()->copy_from($item)->set_stock_type($stocktype)->set_parent_product_id($product->get_id());
            $order_products[] = $order_product->set_price($this->call('calcPrice', $order_product, $prices, $quantity));
        }
        return $order_products;
    }
}