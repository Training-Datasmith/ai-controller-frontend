<?php

declare (strict_types=1);
/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2012
 * @copyright Aimeos (aimeos.org), 2015-2026
 * @package Controller
 * @subpackage Frontend
 */
namespace Aimeos\Controller\Frontend\Basket;

/**
 * Base class for the basket frontend controller
 *
 * @package Controller
 * @subpackage Frontend
 */
abstract class Base extends \Aimeos\Controller\Frontend\Base implements Iface
{
    /**
     * Calculates and returns the current price for the given order product and product prices.
     *
     * @param \Aimeos\MShop\Order\Item\Product\Iface $orderProduct Ordered product item
     * @param \Aimeos\Map $prices List of price items implementing \Aimeos\MShop\Price\Item\Iface
     * @param float $quantity New product quantity
     * @return \Aimeos\MShop\Price\Item\Iface Price item with calculated price
     */
    protected function calc_price(\Aimeos\M_Shop\Order\Item\Product\Iface $order_product, \Aimeos\Map $prices, float $quantity): \Aimeos\M_Shop\Price\Item\Iface
    {
        $context = $this->context();
        $price_manager = \Aimeos\M_Shop::create($context, 'price');
        $price = $price_manager->get_lowest_price($prices, $quantity, null, $order_product->get_site_id());
        // customers can pay what they would like to pay
        if (($attr = $order_product->get_attribute_item('price', 'custom')) !== null) {
            $amount = $attr->get_value();
            if (preg_match('/^[0-9]*(\.[0-9]+)?$/', $amount) !== 1 || (float) $amount < 0.01) {
                $msg = $context->translate('controller/frontend', 'Invalid price value "%1$s"');
                throw new \Aimeos\Controller\Frontend\Basket\Exception(sprintf($msg, $amount));
            }
            $price = $price->set_value($amount);
        }
        $order_attributes = $order_product->get_attribute_items();
        $attr_items = $this->get_attribute_items($order_attributes);
        // add prices of (optional) attributes
        foreach ($order_attributes as $order_attr_item) {
            if (!$attr_item = $attr_items->get($order_attr_item->get_attribute_id())) {
                continue;
            }
            $prices = $attr_item->get_ref_items('price', 'default', 'default');
            if (!$prices->is_empty()) {
                $attr_price = $price_manager->get_lowest_price($prices, $order_attr_item->get_quantity(), null, $order_product->get_site_id());
                $price = $price->add_item(clone $attr_price, $order_attr_item->get_quantity());
                $order_attr_item->set_price($attr_price->add_item($attr_price, $order_attr_item->get_quantity() - 1)->get_value());
            }
        }
        // remove product rebate of original price in favor to rebates granted for the order
        return $price->set_rebate('0.00');
    }
    /**
     * Returns the allowed quantity for the given product
     *
     * @param \Aimeos\MShop\Product\Item\Iface $product Product item including referenced items
     * @param float $quantity New product quantity
     * @return float Updated quantity value
     */
    protected function check_quantity(\Aimeos\M_Shop\Product\Item\Iface $product, float $quantity): float
    {
        $scale = $product->get_scale();
        if (fmod($quantity, $scale) >= 0.0005) {
            return round(ceil($quantity / $scale) * $scale, 4);
        }
        return $quantity;
    }
    /**
     * Checks if the attribute IDs are really associated to the product
     *
     * @param \Aimeos\MShop\Product\Item\Iface $product Product item with referenced items
     * @param string $domain Domain the references must be of
     * @param array $refMap Associative list of list type codes as keys and lists of reference IDs as values
     * @throws \Aimeos\Controller\Frontend\Basket\Exception If one or more of the IDs are not associated
     */
    protected function check_attributes(array $products, string $list_type, array $ref_ids)
    {
        $attr_ids = map();
        foreach ($products as $product) {
            $attr_ids->merge($product->get_ref_items('attribute', null, $list_type)->keys());
        }
        if ($attr_ids->intersect($ref_ids)->count() !== count($ref_ids)) {
            $i18n = $this->context()->i18n();
            $prod_ids = map($products)->get_id()->join(', ');
            $msg = $i18n->dt('controller/frontend', 'Invalid "%1$s" references for product with ID %2$s');
            throw new \Aimeos\Controller\Frontend\Basket\Exception(sprintf($msg, 'attribute', $prod_ids));
        }
    }
    /**
     * Checks for a locale mismatch and migrates the products to the new basket if necessary.
     *
     * @param \Aimeos\MShop\Locale\Item\Iface $locale Locale object from current basket
     * @param string $type Basket type
     */
    protected function check_locale(\Aimeos\M_Shop\Locale\Item\Iface $locale, string $type)
    {
        $errors = [];
        $context = $this->context();
        $session = $context->session();
        $locale_str = $session->get('aimeos/basket/locale');
        $locale_key = $locale->get_site_item()->get_code() . '|' . $locale->get_language_id() . '|' . $locale->get_currency_id();
        if ($locale_str !== null && $locale_str !== $locale_key) {
            $loc_parts = explode('|', $locale_str);
            $loc_site = $loc_parts[0] ?? '';
            $loc_language = $loc_parts[1] ?? '';
            $loc_currency = $loc_parts[2] ?? '';
            $locale_manager = \Aimeos\M_Shop::create($context, 'locale');
            $locale = $locale_manager->bootstrap($loc_site, $loc_language, $loc_currency, false);
            $context = clone $context;
            $context->set_locale($locale);
            $manager = \Aimeos\M_Shop::create($context, 'order');
            $basket = $manager->get_session($type)->off();
            $this->copy_addresses($basket, $errors, $locale_key);
            $this->copy_services($basket, $errors);
            $this->copy_products($basket, $errors, $locale_key);
            $this->copy_coupons($basket, $errors, $locale_key);
            $this->object()->get()->set_customer_id($basket->get_customer_id())->set_customer_reference($basket->get_customer_reference())->set_comment($basket->get_comment())->set_locale($locale);
            $manager->set_session($basket, $type);
        }
        $session->set('aimeos/basket/locale', $locale_key);
    }
    /**
     * Migrates the addresses from the old basket to the current one.
     *
     * @param \Aimeos\MShop\Order\Item\Iface $basket Basket object
     * @param array $errors Associative list of previous errors
     * @param string $localeKey Unique identifier of the site, language and currency
     * @return array Associative list of errors occured
     */
    protected function copy_addresses(\Aimeos\M_Shop\Order\Item\Iface $basket, array $errors, string $locale_key): array
    {
        foreach ($basket->get_addresses() as $type => $items) {
            foreach ($items as $pos => $item) {
                try {
                    $this->object()->get()->add_address($item, $type, $pos);
                } catch (\Exception $e) {
                    $logger = $this->context()->logger();
                    $errors['address'][$type] = $e->get_message();
                    $str = 'Error migrating address with type "%1$s" in basket to locale "%2$s": %3$s';
                    $logger->info(sprintf($str, $type, $locale_key, $e->get_message()), 'controller/frontend');
                }
            }
            $basket->delete_address($type);
        }
        return $errors;
    }
    /**
     * Migrates the coupons from the old basket to the current one.
     *
     * @param \Aimeos\MShop\Order\Item\Iface $basket Basket object
     * @param array $errors Associative list of previous errors
     * @param string $localeKey Unique identifier of the site, language and currency
     * @return array Associative list of errors occured
     */
    protected function copy_coupons(\Aimeos\M_Shop\Order\Item\Iface $basket, array $errors, string $locale_key): array
    {
        foreach ($basket->get_coupons() as $code => $list) {
            try {
                $this->object()->add_coupon($code);
                $basket->delete_coupon($code);
            } catch (\Exception $e) {
                $logger = $this->context()->logger();
                $errors['coupon'][$code] = $e->get_message();
                $str = 'Error migrating coupon with code "%1$s" in basket to locale "%2$s": %3$s';
                $logger->info(sprintf($str, $code, $locale_key, $e->get_message()), 'controller/frontend');
            }
        }
        return $errors;
    }
    /**
     * Migrates the products from the old basket to the current one.
     *
     * @param \Aimeos\MShop\Order\Item\Iface $basket Basket object
     * @param array $errors Associative list of previous errors
     * @param string $localeKey Unique identifier of the site, language and currency
     * @return array Associative list of errors occured
     */
    protected function copy_products(\Aimeos\M_Shop\Order\Item\Iface $basket, array $errors, string $locale_key): array
    {
        $context = $this->context();
        $manager = \Aimeos\M_Shop::create($context, 'product');
        $rule_manager = \Aimeos\M_Shop::create($context, 'rule');
        $domains = ['attribute', 'catalog', 'media', 'price', 'product', 'text', 'locale/site'];
        foreach ($basket->get_products() as $pos => $product) {
            if ($product->get_flags() & \Aimeos\M_Shop\Order\Item\Product\Base::FLAG_IMMUTABLE) {
                continue;
            }
            try {
                $variant_ids = $config_ids = $custom_ids = [];
                foreach ($product->get_attribute_items() as $attr_item) {
                    switch ($attr_item->get_type()) {
                        case 'variant':
                            $variant_ids[] = $attr_item->get_attribute_id();
                            break;
                        case 'config':
                            $config_ids[$attr_item->get_attribute_id()] = $attr_item->get_quantity();
                            break;
                        case 'custom':
                            $custom_ids[$attr_item->get_attribute_id()] = $attr_item->get_value();
                            break;
                    }
                }
                $item = $manager->get($product->get_parent_product_id() ?: $product->get_product_id(), $domains);
                $item = $rule_manager->apply($item, 'catalog');
                $qty = $product->get_quantity();
                $this->object()->add_product($item, $qty, $variant_ids, $config_ids, $custom_ids, $product->get_stock_type());
                $basket->delete_product($pos);
            } catch (\Exception $e) {
                $code = $product->get_product_code();
                $logger = $this->context()->logger();
                $errors['product'][$pos] = $e->get_message();
                $str = 'Error migrating product with code "%1$s" in basket to locale "%2$s": %3$s';
                $logger->info(sprintf($str, $code, $locale_key, $e->get_message()), 'controller/frontend');
            }
        }
        return $errors;
    }
    /**
     * Migrates the services from the old basket to the current one.
     *
     * @param \Aimeos\MShop\Order\Item\Iface $basket Basket object
     * @param array $errors Associative list of previous errors
     * @return array Associative list of errors occured
     */
    protected function copy_services(\Aimeos\M_Shop\Order\Item\Iface $basket, array $errors): array
    {
        $new_basket = $this->object();
        $manager = \Aimeos\M_Shop::create($this->context(), 'service');
        foreach ($basket->get_services() as $type => $list) {
            foreach ($list as $item) {
                try {
                    $position = null;
                    foreach ($new_basket->get()->get_service($type) as $pos => $ord_service) {
                        if ($item->get_code() === $ord_service->get_code()) {
                            $position = $pos;
                        }
                    }
                    $attributes = [];
                    foreach ($item->get_attribute_items() as $attr_item) {
                        $attributes[$attr_item->get_code()] = $attr_item->get_value();
                    }
                    $service = $manager->get($item->get_service_id(), ['media', 'price', 'text']);
                    $new_basket->add_service($service, $attributes, $position);
                    $basket->delete_service($type);
                } catch (\Exception) {
                }
                // Don't notify the user as appropriate services can be added automatically
            }
        }
        return $errors;
    }
    /**
     * Creates the subscription entries for the ordered products with interval attributes
     *
     * @param \Aimeos\MShop\Order\Item\Iface $order Basket object
     */
    protected function create_subscriptions(\Aimeos\M_Shop\Order\Item\Iface $order)
    {
        $types = ['config', 'custom', 'hidden', 'variant'];
        $manager = \Aimeos\M_Shop::create($this->context(), 'subscription');
        foreach ($order->get_products() as $order_product) {
            if (($interval = $order_product->get_attribute('interval', $types)) !== null) {
                $interval = is_array($interval) ? reset($interval) : $interval;
                $item = $manager->create()->set_interval($interval)->set_product_id($order_product->get_product_id())->set_order_product_id($order_product->get_id())->set_order_id($order->get_id());
                if (($end = $order_product->get_attribute('intervalend', $types)) !== null) {
                    $item = $item->set_date_end($end);
                }
                $manager->save($item, false);
            }
        }
    }
    /**
     * Returns the attribute items for the given attribute IDs.
     *
     * @param array $attributeIds List of attribute IDs
     * @param string[] $domains Names of the domain items that should be fetched too
     * @return \Aimeos\Map List of items implementing \Aimeos\MShop\Attribute\Item\Iface
     * @throws \Aimeos\Controller\Frontend\Basket\Exception If the actual attribute number doesn't match the expected one
     */
    protected function get_attributes(array $attribute_ids, array $domains = ['text']): \Aimeos\Map
    {
        if (empty($attribute_ids)) {
            return map();
        }
        $attribute_manager = \Aimeos\M_Shop::create($this->context(), 'attribute');
        $search = $attribute_manager->filter(true)->add(['attribute.id' => $attribute_ids])->slice(0, count($attribute_ids));
        $attr_items = $attribute_manager->search($search, $domains);
        if ($attr_items->count() !== count($attribute_ids)) {
            $i18n = $this->context()->i18n();
            $expected = implode(',', $attribute_ids);
            $actual = $attr_items->keys()->join(',');
            $msg = $i18n->dt('controller/frontend', 'Available attribute IDs "%1$s" do not match the given attribute IDs "%2$s"');
            throw new \Aimeos\Controller\Frontend\Basket\Exception(sprintf($msg, $actual, $expected));
        }
        return $attr_items;
    }
    /**
     * Returns the attribute items using the given order attribute items.
     *
     * @param \Aimeos\Map $orderAttributes List of items implementing \Aimeos\MShop\Order\Item\Product\Attribute\Iface
     * @return \Aimeos\Map List of attribute IDs as key and attribute items implementing \Aimeos\MShop\Attribute\Item\Iface
     */
    protected function get_attribute_items(\Aimeos\Map $order_attributes): \Aimeos\Map
    {
        if ($order_attributes->is_empty()) {
            return map();
        }
        $attribute_manager = \Aimeos\M_Shop::create($this->context(), 'attribute');
        $search = $attribute_manager->filter(true);
        $expr = [];
        foreach ($order_attributes as $item) {
            if (is_scalar($item->get_value())) {
                $tmp = [$search->compare('==', 'attribute.domain', 'product'), $search->compare('==', 'attribute.code', $item->get_value()), $search->compare('==', 'attribute.type', $item->get_code()), $search->compare('>', 'attribute.status', 0), $search->get_conditions()];
                $expr[] = $search->and($tmp);
            }
        }
        $search->set_conditions($search->or($expr));
        return $attribute_manager->search($search, ['price']);
    }
    /**
     * Returns the order product attribute items for the given IDs and values
     *
     * @param string $type Attribute type code
     * @param array $ids List of attributes IDs of the given type
     * @param array $values Associative list of attribute IDs as keys and their codes as values
     * @param array $quantities Associative list of attribute IDs as keys and their quantities as values
     * @return array List of items implementing \Aimeos\MShop\Order\Item\Product\Attribute\Iface
     */
    protected function get_order_product_attributes(string $type, array $ids, array $values = [], array $quantities = []): array
    {
        if (empty($ids)) {
            return [];
        }
        $list = [];
        $context = $this->context();
        $price_manager = \Aimeos\M_Shop::create($context, 'price');
        $manager = \Aimeos\M_Shop::create($context, 'order');
        foreach ($this->get_attributes($ids, ['price', 'text']) as $id => $attr_item) {
            $qty = $quantities[$id] ?? 1;
            $item = $manager->create_product_attribute()->copy_from($attr_item)->set_type($type)->set_value($values[$id] ?? $attr_item->get_code())->set_quantity($qty);
            if (!($prices = $attr_item->get_ref_items('price', 'default', 'default'))->is_empty()) {
                $attr_price = $price_manager->get_lowest_price($prices, $qty);
                $item->set_price($attr_price->add_item($attr_price, $qty - 1)->get_value());
            }
            $list[] = $item;
        }
        return $list;
    }
    /**
     * Returns the vendor for the given site ID
     *
     * @param string $siteId Unique ID of the site
     * @return string Vendor name
     */
    protected function get_vendor(string $site_id): string
    {
        $manager = \Aimeos\M_Shop::create($this->context(), 'locale/site');
        $filter = $manager->filter(true)->add('locale.site.siteid', '==', $site_id)->slice(0, 1);
        return $manager->search($filter)->get_label()->first() ?: $this->context()->locale()->get_site_item()->get_label();
    }
}