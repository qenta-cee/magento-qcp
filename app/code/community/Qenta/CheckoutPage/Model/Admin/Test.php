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

class Qenta_CheckoutPage_Model_Admin_Test extends Mage_Core_Model_Abstract
{

    public function testconfig()
    {
        /** @var Qenta_CheckoutPage_Helper_Data $helper */
        $helper = Mage::helper('qenta_checkoutpage');

        $returnUrl = Mage::getUrl('qenta_checkoutpage/processing/return', array('_secure' => true, '_nosid' => true));

        try {
            $init = new QentaCEE_QPay_FrontendClient($helper->getConfigArray());
            $init->setPluginVersion($helper->getPluginVersion());

            $init->setOrderReference('Configtest #' . uniqid());

            if ($helper->getConfigData('sendconfirmemail')) {
                $init->setConfirmMail(Mage::getStoreConfig('trans_email/ident_general/email'));
            }

            $consumerData = new QentaCEE_Stdlib_ConsumerData();
            $consumerData->setIpAddress(Mage::app()->getRequest()->getServer('REMOTE_ADDR'));
            $consumerData->setUserAgent(Mage::app()->getRequest()->getHeader('User-Agent'));

            $init->setAmount(10)
                ->setCurrency('EUR')
                ->setPaymentType(QentaCEE_QPay_PaymentType::SELECT)
                ->setOrderDescription('Configtest #' . uniqid())
                ->setSuccessUrl($returnUrl)
                ->setPendingUrl($returnUrl)
                ->setCancelUrl($returnUrl)
                ->setFailureUrl($returnUrl)
                ->setConfirmUrl(Mage::getUrl('qenta_checkoutpage/processing/confirm', array('_secure' => true, '_nosid' => true)))
                ->setServiceUrl($helper->getConfigData('options/serviceurl'))
                ->setConsumerData($consumerData);

            if (strlen($helper->getConfigData('options/bgcolor')))
                $init->setBackgroundColor($helper->getConfigData('options/bgcolor'));

            if (strlen($helper->getConfigData('options/displaytext')))
                $init->setDisplayText($helper->getConfigData('options/displaytext'));

            if (strlen($helper->getConfigData('options/imageurl')))
                $init->setImageUrl($helper->getConfigData('options/imageurl'));

            $initResponse = $init->initiate();
        } catch (Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
            return false;
        }

        if ($initResponse->getStatus() == QentaCEE_QPay_Response_Initiation::STATE_FAILURE) {
            $msg = $initResponse->getError()->getConsumerMessage();
            if (!strlen($msg))
                $msg = $initResponse->getError()->getMessage();
            Mage::getSingleton('core/session')->addError($msg);
            return false;
        }

        Mage::getSingleton('core/session')->addSuccess($helper->__('Configuration test ok'));
        return true;
    }
}