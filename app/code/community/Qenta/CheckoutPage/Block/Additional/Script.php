<?php
/**
 * Shop System Plugins - Terms of Use
 *
 * The plugins offered are provided free of charge by Qenta Central Eastern Europe GmbH
 * (abbreviated to Qenta CEE) and are explicitly not part of the Qenta CEE range of
 * products and services.
 *
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License Version 2 (GPLv2) and can be used, developed and passed on to third parties under
 * the same terms.
 *
 * However, Qenta CEE does not provide any guarantee or accept any liability for any errors
 * occurring when used in an enhanced, customized shop system configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 *
 * Customers use the plugins at their own risk. Qenta CEE does not guarantee their full
 * functionality neither does Qenta CEE assume liability for any disadvantages related to
 * the use of the plugins. Additionally, Qenta CEE does not guarantee the full functionality
 * for customized shop systems or installed plugins of other vendors of plugins within the same
 * shop system.
 *
 * Customers are responsible for testing the plugin's functionality before starting productive
 * operation.
 *
 * By installing the plugin into the shop system the customer agrees to these terms of use.
 * Please do not use the plugin if you do not agree to these terms of use!
 */

class Qenta_CheckoutPage_Block_Additional_Script extends Mage_Core_Block_Template
{

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('qenta/checkoutpage/additional/invoice.phtml');
    }

    public function isRatepayPaymentProvider()
    {
        $installment = new Qenta_CheckoutPage_Model_Installment();
        $invoice = new Qenta_CheckoutPage_Model_Invoice();

        $payments = Mage::getSingleton('payment/config')->getActiveMethods();
        $methods = array();

        foreach ($payments as $paymentCode=>$paymentModel) {
            $methods[] = $paymentCode;
        }

        $installment_active = in_array('qenta_checkoutpage_installment', $methods);
        $invoice_active = in_array('qenta_checkoutpage_invoice', $methods);


        return (($installment->getConfigData('provider') == "ratepay" && $installment_active) || ($invoice->getConfigData('provider') == "ratepay"  && $invoice_active));
    }

    public function getConsumerDeviceId() {
        $session = Mage::getModel('customer/session');

        if (strlen($session->getData('qenta_cp_consumerDeviceId'))) {
            return $session->getData('qenta_cp_consumerDeviceId');
        }
        else {
            $timestamp = microtime();
            $consumerDeviceId = md5(Mage::helper('qenta_checkoutpage')->getConfigData('settings/customer_id') . "_" . $timestamp);
            $session->setData('qenta_cp_consumerDeviceId', $consumerDeviceId);
            return $consumerDeviceId;
        }
    }
}