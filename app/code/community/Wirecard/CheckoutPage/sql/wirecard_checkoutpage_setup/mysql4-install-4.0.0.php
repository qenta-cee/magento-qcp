<?php
/**
 * Shop System Plugins - Terms of Use
 *
 * The plugins offered are provided free of charge by Wirecard Central Eastern Europe GmbH
 * (abbreviated to Wirecard CEE) and are explicitly not part of the Wirecard CEE range of
 * products and services.
 *
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License Version 2 (GPLv2) and can be used, developed and passed on to third parties under
 * the same terms.
 *
 * However, Wirecard CEE does not provide any guarantee or accept any liability for any errors
 * occurring when used in an enhanced, customized shop system configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 *
 * Customers use the plugins at their own risk. Wirecard CEE does not guarantee their full
 * functionality neither does Wirecard CEE assume liability for any disadvantages related to
 * the use of the plugins. Additionally, Wirecard CEE does not guarantee the full functionality
 * for customized shop systems or installed plugins of other vendors of plugins within the same
 * shop system.
 *
 * Customers are responsible for testing the plugin's functionality before starting productive
 * operation.
 *
 * By installing the plugin into the shop system the customer agrees to these terms of use.
 * Please do not use the plugin if you do not agree to these terms of use!
 */

echo 'Running This Upgrade: '.get_class($this)."\n <br /> \n";die;
$installer = $this;
$installer->startSetup();

$installer->addAttribute(
    "customer",
    "company_trade_reg_number",
    array(
        "type" => "varchar",
        "backend" => "",
        "label" => "Company Trade Registration Number",
        "input" => "text",
        "source" => "",
        "visible" => true,
        "required" => false,
        "default" => "",
        "frontend" => "",
        "unique" => false,
        "note" => ""

    )
);

$used_in_forms = array(
    "adminhtml_customer",
    "checkout_register",
    "customer_account_create",
    "customer_account_edit",
    "adminhtml_checkout"
);

$attribute = Mage::getSingleton("eav/config")->getAttribute("customer", "company_trade_reg_number");
$attribute->setData("used_in_forms", $used_in_forms)
    ->setData("is_used_for_customer_segment", true)
    ->setData("is_system", 0)
    ->setData("is_user_defined", 1)
    ->setData("is_visible", 1)
    ->setData("sort_order", 999);
$attribute->save();

$installer->endSetup();