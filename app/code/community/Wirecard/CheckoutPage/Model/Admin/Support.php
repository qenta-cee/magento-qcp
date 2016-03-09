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

class Wirecard_CheckoutPage_Model_Admin_Support extends Mage_Core_Model_Abstract
{

    public function sendEmail($postObject)
    {
        /** @var Wirecard_CheckoutPage_Helper_Data $helper */
        $helper = Mage::helper('wirecard_checkoutpage');

        $mail = new Zend_Mail();
        $mail->setSubject('Support request via magento online shop');

        if (!Zend_Validate::is(trim($postObject->getData('to')), 'EmailAddress')) {
            Mage::getSingleton('core/session')->addError('Please enter a valid e-mail address.');
            return false;
        }
        $mail->addTo(trim($postObject->getData('to')));

        if (strlen(trim($postObject->getData('replyto')))) {
            if (!Zend_Validate::is(trim($postObject->getData('replyto')), 'EmailAddress')) {
                Mage::getSingleton('core/session')->addError('Please enter a valid e-mail address (reply to).');
                return false;
            }
            $mail->setReplyTo(trim($postObject->getData('replyto')));
        }

        $fromName = Mage::getStoreConfig('trans_email/ident_general/name');
        $fromEmail = Mage::getStoreConfig('trans_email/ident_general/email');
        if (!strlen($fromEmail)) {
            Mage::getSingleton('core/session')->addError('Please set your shop e-mail address!');
            return false;
        }
        $mail->setFrom($fromEmail, $fromName);


        $modules = array_keys((array)Mage::getConfig()->getNode('modules')->children());
        $modules = array_filter($modules, function ($e) { return !preg_match('/^Mage_/', $e); });

        $body = $postObject->getData('description');

        $payments = Mage::getSingleton('payment/config')->getActiveMethods();

        $foreign = array();
        $mine = array();
        foreach ($payments as $paymentCode => $paymentModel) {

            /** @var Mage_Payment_Model_Method_Abstract $paymentModel */

            $method = array(
                'label'  => $paymentModel->getTitle(),
                'value'  => $paymentCode,
                'config' => Mage::getStoreConfig('payment/' . $paymentCode)
            );

            if (preg_match('/^wirecard_checkoutpage_/', $paymentCode)) {
                $mine[$paymentCode] = $method;
            }
            else {
                $foreign[$paymentCode] = $method;
            }
        }

        $body .= sprintf("\n\n%s:\n\n", $helper->__('Configuration'));
        $body .= $helper->getConfigString();

        $body .= sprintf("\n\n%s:\n\n", $helper->__('Active payment methods'));

        foreach ($mine as $paymentCode => $payment) {
            $body .= sprintf("%s:\n", $payment['label']);
            foreach ($payment['config'] as $k => $v) {
                if ($k == 'model' || $k == 'title')
                    continue;
                $body .= sprintf("%s:%s\n", $k, $v);
            }
            $body .= "\n";
        }

        $body .= sprintf("\n%s:\n\n", $helper->__('Foreign payment methods'));
        foreach ($foreign as $paymentCode => $payment) {
            $body .= sprintf("%s\n", $payment['label']);
        }

        $body .= sprintf("\n\n%s:\n\n", $helper->__('Installed Modules'));
        $body .= implode("\n", $modules);

        $mail->setBodyText($body);

        try {
            $mail->send();
            Mage::getSingleton('core/session')->addSuccess('Support request sent successfully!');
        } catch (Exception $e) {
            Mage::getSingleton('core/session')->addError('Unable to send email:' . $e->getMessage());
            return false;
        }

        return true;
    }
}