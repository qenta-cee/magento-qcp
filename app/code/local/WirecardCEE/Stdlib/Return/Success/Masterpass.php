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


/**
 * @name WirecardCEE_Stdlib_Return_Success_Masterpass
 * @category WirecardCEE
 * @package WirecardCEE_Stdlib
 * @subpackage Return_Success
 * @abstract
 */
abstract class WirecardCEE_Stdlib_Return_Success_Masterpass extends WirecardCEE_Stdlib_Return_Success_CreditCard
{
    /**
     * getter for the return parameter masterpassBillingAddressCity
     *
     * @return string
     */
    public function getMasterpassBillingAddressCity()
    {
        return (string)$this->masterpassBillingAddressCity;
    }

    /**
     * getter for the return parameter masterpassBillingAddressCountry
     *
     * @return string
     */
    public function getMasterpassBillingAddressCountry()
    {
        return (string)$this->masterpassBillingAddressCountry;
    }

    /**
     * getter for the return parameter masterpassBillingAddressCountrySubdivision
     *
     * @return string
     */
    public function getMasterpassBillingAddressCountrySubdivision()
    {
        return (string)$this->masterpassBillingAddressCountrySubdivision;
    }

    /**
     * getter for the return parameter masterpassBillingAddressPostalCode
     *
     * @return string
     */
    public function getMasterpassBillingAddressPostalCode()
    {
        return (string)$this->masterpassBillingAddressPostalCode;
    }

    /**
     * getter for the return parameter masterpassBillingAddressAddressLine1
     *
     * @return string
     */
    public function getMasterpassBillingAddressAddressLine1()
    {
        return (string)$this->masterpassBillingAddressAddressLine1;
    }

    /**
     * getter for the return parameter masterpassBillingAddressAddressLine2
     *
     * @return string
     */
    public function getMasterpassBillingAddressAddressLine2()
    {
        return (string)$this->masterpassBillingAddressAddressLine2;
    }

    /**
     * getter for the return parameter masterpassBillingAddressAddressLine3
     *
     * @return string
     */
    public function getMasterpassBillingAddressAddressLine3()
    {
        return (string)$this->masterpassBillingAddressAddressLine3;
    }

    /**
     * getter for the return parameter masterpassShippingAddressRecipientName
     *
     * @return string
     */
    public function getMasterpassShippingAddressRecipientName()
    {
        return (string)$this->masterpassShippingAddressRecipientName;
    }

    /**
     * getter for the return parameter masterpassShippingAddressRecipientPhoneNumber
     *
     * @return string
     */
    public function getMasterpassShippingAddressRecipientPhoneNumber()
    {
        return (string)$this->masterpassShippingAddressRecipientPhoneNumber;
    }

    /**
     * getter for the return parameter masterpassShippingAddressCity
     *
     * @return string
     */
    public function getMasterpassShippingAddressCity()
    {
        return (string)$this->masterpassShippingAddressCity;
    }

    /**
     * getter for the return parameter masterpassShippingAddressCountry
     *
     * @return string
     */
    public function getMasterpassShippingAddressCountry()
    {
        return (string)$this->masterpassShippingAddressCountry;
    }

    /**
     * getter for the return parameter masterpassShippingAddressCountrySubdivision
     *
     * @return string
     */
    public function getMasterpassShippingAddressCountrySubdivision()
    {
        return (string)$this->masterpassShippingAddressCountrySubdivision;
    }

    /**
     * getter for the return parameter masterpassShippingAddressPostalCode
     *
     * @return string
     */
    public function getMasterpassShippingAddressPostalCode()
    {
        return (string)$this->masterpassShippingAddressPostalCode;
    }

    /**
     * getter for the return parameter masterpassShippingAddressAddressLine1
     *
     * @return string
     */
    public function getMasterpassShippingAddressAddressLine1()
    {
        return (string)$this->masterpassShippingAddressAddressLine1;
    }

    /**
     * getter for the return parameter masterpassShippingAddressAddressLine2
     *
     * @return string
     */
    public function getMasterpassShippingAddressAddressLine2()
    {
        return (string)$this->masterpassShippingAddressAddressLine2;
    }

    /**
     * getter for the return parameter masterpassShippingAddressAddressLine3
     *
     * @return string
     */
    public function getMasterpassShippingAddressAddressLine3()
    {
        return (string)$this->masterpassShippingAddressAddressLine3;
    }

}