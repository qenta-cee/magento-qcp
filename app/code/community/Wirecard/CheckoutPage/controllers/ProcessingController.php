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

class Wirecard_CheckoutPage_ProcessingController extends Mage_Core_Controller_Front_Action
{
    protected $paymentInst;

    /** @var  Mage_Sales_Model_Order */
    protected $order;

    /**
     * Get singleton of Checkout Session Model
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * checkout Page for IFrame include
     */
    public function checkoutAction()
    {
        $session = $this->getCheckout();

        /** @var  Mage_Sales_Model_Order $order */
        $order = Mage::getModel('sales/order');
        $order->loadByIncrementId($session->getLastRealOrderId());
        if (!$this->_succeeded($order)) {
            $order->setState(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, true, Mage::helper('wirecard_checkoutpage')->__('Customer was redirected to Wirecard Checkout Page.'))->save();
            // Save quote id in session and clean it
            $session->setWirecardCheckoutPageQuoteId($session->getQuoteId());
            $session->getQuote()->setIsActive(false)->save();
            $session->clear();

            /** @var Wirecard_CheckoutPage_Model_Abstract $paymentInst */
            $paymentInst = $order->getPayment()->getMethodInstance();
            try {
                $init = $paymentInst->initPayment();
                if ($this->_useIframe($paymentInst)) {
                    $this->loadLayout();
                    $this->getLayout()->getBlock('wirecard_checkoutpage_checkout')->assign('iframeUrl', $init->getRedirectUrl());
                    $this->renderLayout();
                }
                else {
                    $this->_redirectUrl($init->getRedirectUrl());
                }
            } catch (Exception $e) {
                $quoteId = $this->getCheckout()->getLastQuoteId();
                if ($quoteId) {
                    $quote = Mage::getModel('sales/quote')->load($quoteId);
                    if ($quote->getId()) {
                        $quote->setIsActive(true)->save();
                        $this->getCheckout()->setQuoteId($quoteId);
                    }
                }
                $this->getCheckout()->addNotice($e->getMessage());
                $this->_redirectUrl(Mage::getUrl('checkout/cart/'));
            }
        }
        else {
            $this->norouteAction();
        }
    }

    /**
     * @param Wirecard_CheckoutPage_Model_Abstract $paymentInst
     *
     * @return bool
     */
    protected function _useIframe($paymentInst)
    {
        $detectLayout = new WirecardCEE_QPay_MobileDetect();
        /** @var Wirecard_CheckoutPage_Helper_Data $helper */
        $helper = Mage::helper('wirecard_checkoutpage');
        return ($paymentInst->getConfigData('iframe') &&
            (!$helper->getConfigData('options/mobiledetect')
                || ($helper->getConfigData('options/mobiledetect') && !$detectLayout->isMobile())));
    }

    /**
     * Wirecard Checkout Page returns POST variables to this action
     */
    public function returnAction()
    {
        /** @var Wirecard_CheckoutPage_Helper_Data $helper */
        $helper = Mage::helper('wirecard_checkoutpage');
        try {

            if (!$this->getRequest()->isPost())
                throw new Exception('Not a POST message');

            $data = $this->getRequest()->getPost();

            $helper->log(__METHOD__ . ':' . print_r($data, true));

            $session = $this->getCheckout();
            /** @var Mage_Sales_Model_Order $order */
            $order = Mage::getModel('sales/order');
            $order->load($session->getLastOrderId());
            if (!$order->getId())
                throw new Exception('Order not found');

            $return = WirecardCEE_QPay_ReturnFactory::getInstance($data, $helper->getConfigData('settings/secret'));
            if (!$return->validate())
                throw new Exception('Validation error: invalid response');

            // fallback, if confirm request has not been arrived
            if (!$order->getPayment()->getAdditionalInformation('confirmProcessed')) {
                $helper->log(__METHOD__ . ':order not processed via confirm server2server request, check your packetfilter!', Zend_Log::WARN);

                switch ($return->getPaymentState()) {
                    case WirecardCEE_QPay_ReturnFactory::STATE_SUCCESS:
                    case WirecardCEE_QPay_ReturnFactory::STATE_PENDING:
                        $this->_confirmOrder($order, $return, true);
                        break;

                    case WirecardCEE_QPay_ReturnFactory::STATE_CANCEL:
                        /** @var WirecardCEE_QPay_Return_Cancel $return */
                        $this->_cancelOrder($order);
                        break;

                    case WirecardCEE_QPay_ReturnFactory::STATE_FAILURE:
                        /** @var WirecardCEE_QPay_Return_Failure $return */
                        $msg = $return->getErrors()->getConsumerMessage();
                        if (!$this->_succeeded($order)) {
                            $order->setState(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, true, $helper->__('An error occured during the payment process') . ': ' . $msg)->save();
                            $order->cancel();
                            $payment = $order->getPayment();
                            $payment->setAdditionalInformation('consumerMessage', $msg);
                        }
                        break;

                    default:
                        throw new Exception('Unhandled Wirecard Checkout Page payment state:' . $return->getPaymentState());
                }
                $order->save();
            }

            // the customer has canceled the payment. show cancel message.
            if ($order->isCanceled()) {
                $quoteId = $session->getLastQuoteId();
                if ($quoteId) {
                    $quote = Mage::getModel('sales/quote')->load($quoteId);
                    if ($quote->getId()) {
                        $quote->setIsActive(true)->save();
                        $session->setQuoteId($quoteId);
                    }
                }
                $consumerMessage = $order->getPayment()->getAdditionalInformation('consumerMessage');
                if (!strlen($consumerMessage)) {
                    //fallback message if no consumerMessage has been set
                    $consumerMessage = $helper->__('Order has been canceled.');
                }
                throw new Exception($helper->__($consumerMessage));
            }

            // get sure order status has changed since redirect
            if ($order->getStatus() == Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW)
                throw new Exception($helper->__('Sorry, your payment has not been confirmed by the financial service provider.'));

            if ($order->getStatus() == Mage_Sales_Model_Order::STATE_PENDING_PAYMENT) {
                $msg = $helper->__('Your order will be processed as soon as we receive the payment confirmation from your bank.');
                Mage::getSingleton('checkout/session')->addNotice($msg);
            }

            $this->getCheckout()->setLastSuccessQuoteId($session->getLastQuoteId());
            $this->getCheckout()->setResponseRedirectUrl('checkout/onepage/success');
        } catch (Exception $e) {
            $helper->log(__METHOD__ . ':' . $e->getMessage(), Zend_Log::ERR);
            $this->getCheckout()->addNotice($e->getMessage());
            $this->getCheckout()->setResponseRedirectUrl('checkout/cart/');
        }

        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Process transaction confirm message
     */
    public function confirmAction()
    {
        /** @var Wirecard_CheckoutPage_Helper_Data $helper */
        $helper = Mage::helper('wirecard_checkoutpage');
        try {

            if (!$this->getRequest()->isPost())
                throw new Exception('Not a POST message');

            $data = $this->getRequest()->getPost();

            $helper->log(__METHOD__ . ':' . print_r($data, true));

            if (!isset($data['mage_orderId']))
                throw new Exception('Magent OrderId is missing');

            $return = WirecardCEE_QPay_ReturnFactory::getInstance($data, $helper->getConfigData('settings/secret'));
            if (!$return->validate())
                throw new Exception('Validation error: invalid response');

            /** @var Mage_Sales_Model_Order $order */
            $order = Mage::getModel('sales/order');
            $order->loadByIncrementId($data['mage_orderId']);
            if (!$order->getId())
                throw new Exception('Order not found with Id:' . $data['mage_orderId']);

            /** @var Wirecard_CheckoutPage_Model_Abstract $paymentInst */
            $paymentInst = $order->getPayment()->getMethodInstance();
            $paymentInst->setResponse($data);

            switch ($return->getPaymentState()) {
                case WirecardCEE_QPay_ReturnFactory::STATE_SUCCESS:
                case WirecardCEE_QPay_ReturnFactory::STATE_PENDING:
                    $this->_confirmOrder($order, $return);
                    break;

                case WirecardCEE_QPay_ReturnFactory::STATE_CANCEL:
                    /** @var WirecardCEE_QPay_Return_Cancel $return */
                    $this->_cancelOrder($order);
                    break;

                case WirecardCEE_QPay_ReturnFactory::STATE_FAILURE:
                    /** @var WirecardCEE_QPay_Return_Failure $return */
                    $msg = $return->getErrors()->getConsumerMessage();
                    if (!$this->_succeeded($order)) {
                        $payment = $order->getPayment();
                        $additionalInformation = Array('confirmProcessed' => true, 'consumerMessage' => $msg);
                        $payment->setAdditionalInformation($additionalInformation);
                        $payment->setAdditionalData(serialize($additionalInformation));
                        $payment->save();

                        $order->setState(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, true, $helper->__('An error occured during the payment process') . ': ' . $msg)->save();
                        $order->cancel();
                    }
                    break;

                default:
                    throw new Exception('Unhandled Wirecard Checkout Page payment state:' . $return->getPaymentState());
            }

            $order->save();

            die(WirecardCEE_QPay_ReturnFactory::generateConfirmResponseString());

        } catch (Exception $e) {
            $helper->log(__METHOD__ . ':' . $e->getMessage(), Zend_Log::ERR);

            die(WirecardCEE_QPay_ReturnFactory::generateConfirmResponseString($e->getMessage()));
        }
    }

    /**
     * check if order already has been successfully processed.
     *
     * @param $order Mage_Sales_Model_Order
     *
     * @return bool
     */
    protected function _succeeded($order)
    {
        $history = $order->getAllStatusHistory();
        $paymentInst = $order->getPayment()->getMethodInstance();
        if ($paymentInst) {
            foreach ($history AS $entry) {
                if ($entry->getStatus() == Mage_Sales_Model_Order::STATE_PROCESSING) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Cancel an order
     *
     * @param Mage_Sales_Model_Order $order
     */
    protected function _cancelOrder($order)
    {
        /** @var Wirecard_CheckoutPage_Helper_Data $helper */
        $helper = Mage::helper('wirecard_checkoutpage');

        if (!$this->_succeeded($order)) {
            $payment = $order->getPayment();
            $additionalInformation = Array('confirmProcessed' => true);
            $payment->setAdditionalInformation($additionalInformation);
            $payment->setAdditionalData(serialize($additionalInformation));
            $payment->save();

            if ($order->canUnhold()) {
                $order->unhold();
            }
            $order->setState(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, true, $helper->__('Customer canceled the payment process'))->save();
            $order->cancel();
        }
    }

    /**
     * Confirm the payment of an order
     *
     * @param Mage_Sales_Model_Order $order
     * @param WirecardCEE_Stdlib_Return_ReturnAbstract $return
     * * @param bool $fallback
     */
    protected function _confirmOrder($order, $return, $fallback = false)
    {
        /** @var Wirecard_CheckoutPage_Helper_Data $helper */
        $helper = Mage::helper('wirecard_checkoutpage');

        if (!$this->_succeeded($order)) {
            if ($return->getPaymentState() == WirecardCEE_QPay_ReturnFactory::STATE_PENDING) {
                $order->setState(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, true, $helper->__('The payment authorization is pending.'))->save();
            }
            else {
                $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true, $helper->__('The payment has been successfully completed.'))->save();
                // invoice payment
                if ($order->canInvoice()) {

                    $invoice = $order->prepareInvoice();
                    $invoice->register()->capture();
                    Mage::getModel('core/resource_transaction')
                        ->addObject($invoice)
                        ->addObject($invoice->getOrder())
                        ->save();
                }
                // send new order email to customer
                $order->sendNewOrderEmail();
            }
        }
        $payment = $order->getPayment();
        $additionalInformation = Array();

        foreach ($return->getReturned() as $fieldName => $fieldValue) {
            $additionalInformation[htmlentities($fieldName)] = htmlentities($fieldValue);
        }

        // need to remember whether confirm request was processed
        // check this within returnAction and process order
        // could be if confirm request has bee blocked (firewall)
        if ($fallback)
            $additionalInformation['fallbackUsed'] = $helper->__('Confirm via server2server request is not working!');
        else
            $additionalInformation['confirmProcessed'] = true;

        $payment->setAdditionalInformation($additionalInformation);
        $payment->setAdditionalData(serialize($additionalInformation));
        $payment->save();
    }

}
