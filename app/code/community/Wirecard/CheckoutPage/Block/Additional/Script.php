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

class Wirecard_CheckoutPage_Block_Additional_Script extends Mage_Core_Block_Template
{

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('wirecard/checkoutpage/additional/invoice.phtml');
    }

    public function isRatepayPaymentProvider()
    {
        $installment = new Wirecard_CheckoutPage_Model_Installment();
        $invoice = new Wirecard_CheckoutPage_Model_Invoice();
        $payments = Mage::getSingleton('payment/config')->getActiveMethods();


        return (($installment->getConfigData('provider') == "ratepay" && in_array("installment", $payments))|| ($invoice->getConfigData('provider') == "ratepay"  && in_array("invoice", $payments)));
    }

    public function getConsumerDeviceId() {
        $session = Mage::getModel('customer/session');

        if (strlen($session->getData('wirecard_cp_consumerDeviceId'))) {
            return $session->getData('wirecard_cp_consumerDeviceId');
        }
        else {
            $timestamp = microtime();
            $consumerDeviceId = md5(Mage::helper('wirecard_checkoutpage')->getConfigData('settings/customer_id') . "_" . $timestamp);
            $session->setData('wirecard_cp_consumerDeviceId', $consumerDeviceId);
            return $consumerDeviceId;
        }
    }
}