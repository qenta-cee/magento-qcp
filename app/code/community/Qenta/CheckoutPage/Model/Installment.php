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

class Qenta_CheckoutPage_Model_Installment extends Qenta_CheckoutPage_Model_Abstract
{
    /**
     * unique internal payment method identifier
     *
     * @var string [a-z0-9_]
     **/
    protected $_code = 'qenta_checkoutpage_installment';
    protected $_paymentMethod = QentaCEE_Stdlib_PaymentTypeAbstract::INSTALLMENT;

    protected $_forceSendAdditionalData = true;

    /**
     * Assign data to info model instance
     *
     * @param   mixed $data
     *
     * @return  Mage_Payment_Model_Info
     */
    public function assignData($data)
    {
        $result = parent::assignData($data);
        $key = 'qenta_checkoutpage_installment_dob';
        if (is_array($data)) {
            $this->getInfoInstance()->setAdditionalInformation($key, isset($data[$key]) ? $data[$key] : null);
        }
        elseif ($data instanceof Varien_Object) {
            $this->getInfoInstance()->setAdditionalInformation($key, $data->getData($key));
        }
        $this->getInfoInstance()->save();
        return $result;
    }

    /**
     * @see Qenta_CheckoutPage_Model_Abstract::getCustomerDob()
     */
    public function getCustomerDob()
    {
        $additionalInfo = $this->getInfoInstance();
        if ($additionalInfo->hasAdditionalInformation('qenta_checkoutpage_installment_dob')) {
            $dob = $additionalInfo->getAdditionalInformation('qenta_checkoutpage_installment_dob');
            if ($dob) {
                return new DateTime($dob);
            }
        }
        else {
            return parent::getCustomerDob();
        }
    }

    /**
     * @param Mage_Sales_Model_Quote|null $quote
     *
     * @return bool
     * @see Mage_Payment_Model_Method_Abstract::isAvailable()
     */
    public function isAvailable($quote = null)
    {
        //NOTE: NEVER return true in here. the parent check should do this!
        if ($quote == null) {
            $quote = $this->_getQuote();
        }

        if ($this->getConfigData('provider') == 'ratepay') {
            return $this->_isAvailableRatePay($quote);
        }
        elseif ($this->getConfigData('provider') == 'payolution') {
            return $this->_isAvailablePayolution($quote);
        }

        return false;
    }

}