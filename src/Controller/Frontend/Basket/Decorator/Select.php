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
 * Selection product handling
 *
 * @package Controller
 * @subpackage Frontend
 */
class Select extends \Aimeos\Controller\Frontend\Basket\Decorator\Base implements \Aimeos\Controller\Frontend\Basket\Iface, \Aimeos\Controller\Frontend\Common\Decorator\Iface
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
     * @param string|null $siteId Unique ID of the site the product should be bought from or NULL for site the product is from
     * @return \Aimeos\Controller\Frontend\Basket\Iface Basket frontend object for fluent interface
     * @throws \Aimeos\Controller\Frontend\Basket\Exception If the product isn't available
     */
    public function add_product(\Aimeos\M_Shop\Product\Item\Iface $product, float $quantity = 1, array $variant = [], array $config = [], array $custom = [], string $stocktype = 'default', ?string $site_id = null): \Aimeos\Controller\Frontend\Basket\Iface
    {
        if ($product->get_type() !== 'select') {
            $this->get_controller()->add_product($product, $quantity, $variant, $config, $custom, $stocktype, $site_id);
            return $this;
        }
        $attr = [];
        $prices = $product->get_ref_items('price', 'default', 'default');
        $hidden = $product->get_ref_items('attribute', null, 'hidden');
        $product_item = $this->get_article($product, $variant);
        $quantity = $this->call('checkQuantity', $product_item, $quantity);
        $order_product_item = \Aimeos\M_Shop::create($this->context(), 'order')->create_product()->copy_from($product)->set_quantity($quantity)->set_stock_type($stocktype)->set_name($product_item->get_name())->set_scale($product_item->get_scale())->set_product_id($product_item->get_id())->set_parent_product_id($product->get_id())->set_product_code($product_item->get_code())->set_site_id($site_id ?: $product_item->get_site_id());
        $this->call('checkAttributes', [$product, $product_item], 'custom', array_keys($custom));
        $this->call('checkAttributes', [$product, $product_item], 'config', array_keys($config));
        if (!($subprices = $product_item->get_ref_items('price', 'default', 'default'))->is_empty()) {
            $prices = $subprices;
        }
        if ($media_item = $product_item->get_ref_items('media', 'default', 'default')->first()) {
            $order_product_item->set_media_url($media_item->get_preview());
        }
        $hidden->union($product_item->get_ref_items('attribute', null, 'hidden'));
        $order_manager = \Aimeos\M_Shop::create($this->context(), 'order');
        $attributes = $product_item->get_ref_items('attribute', null, 'variant');
        foreach ($this->call('getAttributes', $attributes->keys()->to_array(), ['text']) as $attr_item) {
            $attr[] = $order_manager->create_product_attribute()->copy_from($attr_item)->set_type('variant');
        }
        $cust_attr = $this->call('getOrderProductAttributes', 'custom', array_keys($custom), $custom);
        $conf_attr = $this->call('getOrderProductAttributes', 'config', array_keys($config), [], $config);
        $hide_attr = $this->call('getOrderProductAttributes', 'hidden', $hidden->keys()->to_array());
        $order_product_item->set_attribute_items(array_merge($attr, $cust_attr, $conf_attr, $hide_attr));
        $price = $this->call('calcPrice', $order_product_item, $prices, $quantity);
        $order_product_item->set_price($price)->set_site_id($site_id ?: $price->get_site_id())->set_vendor($this->get_vendor($site_id ?: $price->get_site_id()));
        $this->get_controller()->get()->add_product($order_product_item);
        $this->get_controller()->save();
        return $this;
    }
    /**
     * Edits the quantity of a product item in the basket.
     *
     * @param int $position Position number (key) of the order product item
     * @param float $quantity New quantiy of the product item
     * @return \Aimeos\Controller\Frontend\Basket\Iface Basket frontend object for fluent interface
     */
    public function update_product(int $position, float $quantity): \Aimeos\Controller\Frontend\Basket\Iface
    {
        $order_product = $this->get()->get_product($position);
        if ($order_product->get_type() !== 'select') {
            $this->get_controller()->update_product($position, $quantity);
            return $this;
        }
        $context = $this->context();
        if ($order_product->get_flags() & \Aimeos\M_Shop\Order\Item\Product\Base::FLAG_IMMUTABLE) {
            $msg = $context->translate('controller/frontend', 'Basket item at position "%1$d" cannot be changed');
            throw new \Aimeos\Controller\Frontend\Basket\Exception(sprintf($msg, $position));
        }
        $manager = \Aimeos\M_Shop::create($context, 'product');
        $product = $manager->get($order_product->get_product_id(), ['price' => ['default']], true);
        $product = \Aimeos\M_Shop::create($context, 'rule')->apply($product, 'catalog');
        $quantity = $this->call('checkQuantity', $product, $quantity);
        if (($prices = $product->get_ref_items('price', 'default', 'default'))->is_empty()) {
            $product = $manager->get($order_product->get_parent_product_id(), ['price' => ['default']], true);
            $product = \Aimeos\M_Shop::create($context, 'rule')->apply($product, 'catalog');
            $prices = $product->get_ref_items('price', 'default', 'default');
        }
        $price = $this->call('calcPrice', $order_product, $prices, $quantity);
        $order_product = $order_product->set_quantity($quantity)->set_price($price);
        $this->get_controller()->get()->add_product($order_product, $position);
        $this->get_controller()->save();
        return $this;
    }
    /**
     * Returns the variant attributes and updates the price list if necessary.
     *
     * @param \Aimeos\MShop\Product\Item\Iface $productItem Product item which is replaced if necessary
     * @param array $variantAttributeIds List of product variant attribute IDs
     * @return \Aimeos\MShop\Product\Item\Iface Product variant article
     * @throws \Aimeos\Controller\Frontend\Basket\Exception If no product variant is found
     */
    protected function get_article(\Aimeos\M_Shop\Product\Item\Iface $product_item, array $variant): \Aimeos\M_Shop\Product\Item\Iface
    {
        $items = [];
        $context = $this->context();
        /** controller/frontend/basket/require-variant
         * A variant of a selection product must be chosen
         *
         * Selection products normally consist of several article variants and
         * by default exactly one article variant of a selection product can be
         * put into the basket.
         *
         * By setting this option to false, the selection product including the
         * chosen attributes (if any attribute values were selected) can be put
         * into the basket as well. This makes it possible to get all articles
         * or a subset of articles (e.g. all of a color) at once.
         *
         * This option replace the "client/html/basket/require-variant" setting.
         *
         * @param boolean True if a variant must be chosen, false if also the selection product with attributes can be added
         * @since 2018.01
         * @category Developer
         * @category User
         */
        $require_variant = $context->config()->get('controller/frontend/basket/require-variant', true);
        foreach ($product_item->get_ref_items('product', null, 'default') as $item) {
            foreach ($variant as $id) {
                if ($item->get_list_item('attribute', 'variant', $id) === null) {
                    continue 2;
                }
            }
            $items[] = $item;
        }
        if (count($items) > 1) {
            $msg = $context->translate('controller/frontend', 'No unique article found for selected attributes and product ID "%1$s"');
            throw new \Aimeos\Controller\Frontend\Basket\Exception(sprintf($msg, $product_item->get_id()));
        }
        if (empty($items) && $require_variant != false) {
            // count == 0
            $msg = $context->translate('controller/frontend', 'No article found for selected attributes and product ID "%1$s"');
            throw new \Aimeos\Controller\Frontend\Basket\Exception(sprintf($msg, $product_item->get_id()));
        }
        return current($items) ?: $product_item;
    }
}