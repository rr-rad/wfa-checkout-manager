<?php
/*
 * Plugin Name: wfa Ceckout Editor 
 * Plugin URI: https://wpfast.ir
 * Description: Edit WooCommerce checkout page for online stores
 * Version: 1.3.1
 * Author: Wpfast
 * Author URI: https://wpfast.ir
 * Text Domain: wfa-checkout-editor
*/
$plugin=plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin", function ($links) {
    array_unshift($links, '<a href="admin.php?page=wfa-checkout-settings">' . esc_html__('Setting', 'wfa-checkout-editor') . '</a>');
    return $links;
});
add_action('plugins_loaded', function () {load_plugin_textdomain('wfa-checkout-editor', false, basename(dirname(__FILE__)) . '/languages/');});

function wfa_checkout_pages()
{
    add_submenu_page('woocommerce',
        esc_html__("Checkout Page Modification Settings", 'wfa-checkout-editor'),
        esc_html__("Checkout Page", 'wfa-checkout-editor'),
        'manage_options',
        'wfa-checkout-settings',
        'wfa_checkout_settings_page'
    );
    add_action('admin_init', 'wfa_checkout_settings_init');
}
add_action('admin_menu', 'wfa_checkout_pages'); 
function wfa_checkout_settings_page()
{
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
        <div class="wrap">
            <?php settings_errors(); ?>
            <form method="POST" action="options.php">
                <?php settings_fields('wfa-checkout-settings'); ?>
                <?php do_settings_sections('wfa-checkout-settings') ?>
                <?php submit_button(); ?>
            </form>
        </div>
    <?php
}
function wfa_checkout_settings_init()
{
    add_settings_section(
        'wfa-checkout-settings-section', // id of the section
        'تنظیمات اصلاح صفحه تسویه حساب', // title to be displayed
        '', // callback function to be called when opening section
        'wfa-checkout-settings' // page on which to display the section, this should be the same as the slug used in add_submenu_page()
        //defalt-rw:my-settings-page
    );

    // register the setting
    register_setting('wfa-checkout-settings', 'wfa_checkout_activation');
    register_setting('wfa-checkout-settings', 'wfa_checkout_type');
    add_settings_field('wfa_checkout_activation', 'فعال سازی', 'wfa_checkout_activation_cb', 'wfa-checkout-settings', 'wfa-checkout-settings-section');
    add_settings_field('wfa_checkout_type', 'نوع فروشگاه', 'wfa_checkout_type_cb', 'wfa-checkout-settings', 'wfa-checkout-settings-section');
}
function wfa_checkout_activation_cb()
{
    $select = esc_attr(get_option('wfa_checkout_activation', ''));
    ?>
    <div id="titlediv">
        <label for="acc1"><?php _e('Active', 'wfa-checkout-editor'); ?> : </label>
        <input type="radio" id="acc1" name="wfa_checkout_activation" value="1" <?php if ($select == 1) echo "checked"; ?>>
        <label for="acc2"><?php _e('Deactive', 'wfa-checkout-editor'); ?> : </label>
        <input type="radio" id="acc2" name="wfa_checkout_activation" value="0" <?php if ($select == 0) echo "checked"; ?>>
    </div>
    <?php
}
function wfa_checkout_type_cb()
{
    $select = esc_attr(get_option('wfa_checkout_type', ''));
    ?>
    <div id="titlediv">
        <label for="acc3"><?php _e('Physical products', 'wfa-checkout-editor'); ?> : </label>
        <input type="radio" id="acc1" name="wfa_checkout_type" value="physical" <?php if ($select == 'physical') echo "checked"; ?>>
        <label for="acc4"><?php _e('Virtual - downloadable products', 'wfa-checkout-editor'); ?> : </label>
        <input type="radio" id="acc2" name="wfa_checkout_type" value="download" <?php if ($select == 'download') echo "checked"; ?>>
    </div>
    <?php
}

$wfa_checkout_activation = esc_attr(get_option('wfa_checkout_activation', ''));
$wfa_checkout_type = esc_attr(get_option('wfa_checkout_type', ''));
if ($wfa_checkout_activation == "1" && $wfa_checkout_type == 'download') {
    add_filter('woocommerce_checkout_fields', 'custom_override_checkout_fields');
    add_filter('woocommerce_billing_fields', 'custom_override_billing_fields');
    add_filter('woocommerce_shipping_fields', 'custom_override_shipping_fields');
} else {
    remove_filter('woocommerce_checkout_fields', 'custom_override_checkout_fields');
    remove_filter('woocommerce_billing_fields', 'custom_override_billing_fields');
    remove_filter('woocommerce_shipping_fields', 'custom_override_shipping_fields');
}
if ($wfa_checkout_activation == "1" && $wfa_checkout_type == 'physical') {
    add_filter('woocommerce_checkout_fields', 'custom_override_checkout_fields2');
    add_filter('woocommerce_billing_fields', 'custom_override_billing_fields2');
    add_filter('woocommerce_shipping_fields', 'custom_override_shipping_fields2');
    add_filter('woocommerce_default_address_fields', 'wfa_reorder_checkout_fields');
} else {
    remove_filter('woocommerce_checkout_fields', 'custom_override_checkout_fields2');
    remove_filter('woocommerce_billing_fields', 'custom_override_billing_fields2');
    remove_filter('woocommerce_shipping_fields', 'custom_override_shipping_fields2');
    remove_filter('woocommerce_default_address_fields', 'wfa_reorder_checkout_fields');
}
function custom_override_checkout_fields2($fields)
{
    unset($fields['billing']['billing_company']);
    unset($fields['billing']['billing_address_2']);
    unset($fields['shipping']['shipping_company']);
    unset($fields['shipping']['shipping_address_2']);
    return $fields;
}
function custom_override_billing_fields2($fields)
{
    unset($fields['billing_company']);
    unset($fields['billing_address_2']);

    return $fields;
}
function custom_override_shipping_fields2($fields)
{
    unset($fields['shipping_company']);
    unset($fields['shipping_address_2']);
    return $fields;
}
function wfa_reorder_checkout_fields($fields)
{
    $fields['first_name']['priority'] = 1;
    $fields['last_name']['priority'] = 2;
    $fields['company']['priority'] = 3;
    $fields['country']['priority'] = 4;
    $fields['state']['priority'] = 5;
    $fields['city']['priority'] = 6;
    $fields['address_1']['priority'] = 7;
    $fields['address_2']['priority'] = 8;
    $fields['postcode']['priority'] = 9;
    return $fields;
}
function custom_override_checkout_fields($fields)
{
    unset($fields['billing']['billing_state']);
    unset($fields['billing']['billing_city']);
    unset($fields['billing']['billing_address_1']);
    unset($fields['billing']['billing_postcode']);
    unset($fields['billing']['billing_country']);
    unset($fields['billing']['billing_company']);
    unset($fields['billing']['billing_address_2']);
    unset($fields['shipping']['shipping_state']);
    unset($fields['shipping']['shipping_address_1']);
    unset($fields['shipping']['shipping_postcode']);
    unset($fields['shipping']['shipping_city']);
    unset($fields['shipping']['shipping_country']);
    unset($fields['shipping']['shipping_company']);
    unset($fields['shipping']['shipping_address_2']);
    return $fields;
}
function custom_override_billing_fields($fields)
{
    unset($fields['billing_state']);
    unset($fields['billing_postcode']);
    unset($fields['billing_address_1']);
    unset($fields['billing_city']);
    unset($fields['billing_country']);
    unset($fields['billing_company']);
    unset($fields['billing_address_2']);
    return $fields;
}
function custom_override_shipping_fields($fields)
{
    unset($fields['shipping_state']);
    unset($fields['shipping_address_1']);
    unset($fields['shipping_postcode']);
    unset($fields['shipping_city']);
    unset($fields['shipping_country']);
    unset($fields['shipping_company']);
    unset($fields['shipping_address_2']);
    return $fields;
}
