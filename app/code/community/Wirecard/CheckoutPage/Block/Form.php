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

class Wirecard_CheckoutPage_Block_Form extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('wirecard/checkoutpage/form.phtml');
    }

    protected function getImageName()
    {
        return preg_replace('/^wirecard_checkoutpage_/', '', $this->getMethodCode());
    }

    public function getMethodLabelAfterHtml()
    {
        $filename = sprintf('images/wirecard/checkoutpage/%s.png', $this->getImageName());
        $filePath = sprintf('%s/frontend/base/default/%s', Mage::getBaseDir('skin'), $filename);
        if (file_exists($filePath)) {
            return sprintf('<img src="%s" title="%s" alt="%s" style="margin-right: 10px;"/>',
                $this->getSkinUrl($filename),
                htmlspecialchars($this->getMethod()->getTitle()),
                htmlspecialchars($this->getMethod()->getTitle()));
        }
        else {
            return '';
        }
    }

    public function hasAdditionalForm()
    {
        return ($this->getAdditionalForm()) ? true : false;
    }

    public function getAdditionalForm()
    {
        $paymentType = strtoupper($this->getMethodCode());
        switch ($paymentType) {
            case 'WIRECARD_CHECKOUTPAGE_INVOICE':
                return 'wirecard_checkoutpage/additional_Invoice';
                break;
            case 'WIRECARD_CHECKOUTPAGE_INSTALLMENT':
                return 'wirecard_checkoutpage/additional_Installment';
                break;
            case 'WIRECARD_CHECKOUTPAGE_INVOICEB2B':
                return 'wirecard_checkoutpage/additional_InvoiceB2b';
                break;
            default:
                return false;
                break;
        }
    }

    public function hasPayolutionTerms()
    {
        if ($this->getMethod()->getConfigData('provider') != 'payolution')
            return false;

        /** @var Wirecard_CheckoutPage_Helper_Data $helper */
        $helper = Mage::helper('wirecard_checkoutpage');
        return $helper->getConfigData('options/payolution_terms');
    }

    public function getPayolutionLink()
    {
        /** @var Wirecard_CheckoutPage_Helper_Data $helper */
        $helper = Mage::helper('wirecard_checkoutpage');
        $mId = base64_encode($helper->getConfigData('options/payolution_mid'));

        if (strlen($mId)) {
            return sprintf('<a href="https://payment.payolution.com/payolution-payment/infoport/dataprivacyconsent?mId=%s" style="float: none; margin: 0;" target="_blank">%s</a>',
                $mId, $helper->__('Consent'));
        } else {
            return $helper->__('Consent');
        }
    }
}