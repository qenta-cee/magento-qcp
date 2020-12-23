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
abstract class Qenta_CheckoutPage_Model_Abstract extends Mage_Payment_Model_Method_Abstract {
	/**
	 * unique internal payment method identifier
	 *
	 * @var string [a-z0-9_]
	 **/
	protected $_code = 'qenta_checkoutpage_abstract';

	protected $_isGateway = false;
	protected $_canAuthorize = true;
	protected $_canCapture = true;
	protected $_canCapturePartial = false;
	protected $_canRefund = false;
	protected $_canVoid = false;
	protected $_canUseInternal = false;
	protected $_canUseCheckout = true;
	protected $_canUseForMultishipping = false;

	protected $_paymentMethod = QentaCEE_QPay_PaymentType::SELECT;
	protected $_defaultLocale = 'en';

	protected $_order;
	protected $_pluginVersion = '4.2.6';
	protected $_pluginName = 'Qenta/CheckoutPage';

	protected $_formBlockType = 'qenta_checkoutpage/form';
	protected $_infoBlockType = 'qenta_checkoutpage/info';

	protected $_forceSendAdditionalData = false;

	/**
	 * @var Mage_Sales_Model_Quote
	 */
	protected $_quote = false;

	/**
	 * translate method title shown in payment selection
	 * the methode code is the key, if no transaltion found
	 * use the title setting
	 *
	 * @return string
	 */
	public function getTitle() {
		/** @var Qenta_CheckoutPage_Helper_Data $helper */
		$helper = Mage::helper( 'qenta_checkoutpage' );

		$translated = $helper->__( $this->_paymentMethod );
		if ( $translated == $this->_paymentMethod ) {
			return parent::getTitle();
		}

		return $translated;
	}

	public function getOrderPlaceRedirectUrl() {
		Mage::getSingleton( 'core/session' )->unsQentaCheckoutPageRedirectUrl();

		return Mage::getUrl( 'qenta_checkoutpage/processing/checkout', array( '_secure' => true ) );
	}

	public function capture( Varien_Object $payment, $amount ) {
		$payment->setStatus( self::STATUS_APPROVED )
		        ->setLastTransId( $this->getTransactionId() );

		return $this;
	}

	public function cancel( Varien_Object $payment ) {
		$payment->setStatus( self::STATUS_DECLINED )
		        ->setLastTransId( $this->getTransactionId() );

		return $this;
	}

	/**
	 * Return payment method type string
	 *
	 * @return string
	 */
	public function getPaymentMethodType() {
		return $this->_paymentMethod;
	}

	public function initPayment() {
		$order = $this->getOrder();
		/** @var Qenta_CheckoutPage_Helper_Data $helper */
		$helper = Mage::helper( 'qenta_checkoutpage' );
        $session = Mage::getModel('customer/session');

		$precision = 2;

		$returnUrl = Mage::getUrl( 'qenta_checkoutpage/processing/return', array(
			'_secure' => true,
			'_nosid'  => true
		) );

		$init = new QentaCEE_QPay_FrontendClient( $helper->getConfigArray() );

		$init->setPluginVersion( $helper->getPluginVersion() );

		$init->setConfirmUrl( Mage::getUrl( 'qenta_checkoutpage/processing/confirm', array(
			'_secure' => true,
			'_nosid'  => true
		) ) );
		$init->setOrderReference( $this->getOrderReference() );

		if ( $helper->getConfigData( 'sendconfirmemail' ) ) {
			$init->setConfirmMail( Mage::getStoreConfig( 'trans_email/ident_general/email' ) );
		}

		$paymenttype = $this->_paymentMethod;

		$init->setAmount( round( $this->getOrder()->getBaseGrandTotal(), 2 ) )
		     ->setCurrency( $this->getOrder()->getBaseCurrencyCode() )
		     ->setPaymentType( $paymenttype )
		     ->setOrderDescription( $this->getUserDescription() )
		     ->setSuccessUrl( $returnUrl )
		     ->setPendingUrl( $returnUrl )
		     ->setCancelUrl( $returnUrl )
		     ->setFailureUrl( $returnUrl )
		     ->setServiceUrl( $helper->getConfigData( 'options/serviceurl' ) )
		     ->createConsumerMerchantCrmId( $this->getOrder()->getCustomerEmail() )
		     ->setConsumerData( $this->_getConsumerData() );

        if (strlen($session->getData('qenta_cp_consumerDeviceId'))) {
            $init->consumerDeviceId = $session->getData('qenta_cp_consumerDeviceId');
            $session->unsetData('qenta_cp_consumerDeviceId');
        }

        if ($paymenttype == QentaCEE_Stdlib_PaymentTypeAbstract::MASTERPASS) {
            $init->setShippingProfile('NO_SHIPPING');
        }
		$init->setMaxRetries( $helper->getConfigData( 'options/maxretries' ) );

		$init->mage_orderId = $this->getOrder()->getRealOrderId();

		$init->generateCustomerStatement( $helper->getConfigData( 'options/shopname' ) );

		if ( $helper->getConfigData( 'options/sendbasketinformation' )
		     || ( $this->_paymentMethod == QentaCEE_Stdlib_PaymentTypeAbstract::INSTALLMENT && $this->getConfigData( 'provider' ) == 'ratepay' )
		     || ( $this->_paymentMethod == QentaCEE_Stdlib_PaymentTypeAbstract::INVOICE && $this->getConfigData( 'provider' ) == 'ratepay' )
		) {
			$basket = new QentaCEE_Stdlib_Basket();

			foreach ( $order->getAllVisibleItems() as $item ) {
				/** @var Mage_Sales_Model_Order_Item $item */
				$bitem = new QentaCEE_Stdlib_Basket_Item();
				$bitem->setDescription( $item->getProduct()->getName() );
				$bitem->setArticleNumber( $item->getSku() );
				$bitem->setUnitGrossAmount( number_format( $item->getPriceInclTax(), $precision, '.', '' ) );
				$bitem->setUnitNetAmount( number_format( $item->getPrice(), $precision, '.', '' ) );
				$bitem->setUnitTaxAmount( number_format( $item->getTaxAmount(), $precision, '.', '' ) );
				$bitem->setUnitTaxRate( number_format( $item->getTaxPercent(), $precision, '.', '' ) );
				$bitem->setName( $item->getProduct()->getName() );
				$basket->addItem( $bitem, (int) $item->getQtyOrdered() );
				$helper->log( print_r( $bitem, true ) );
			}
			$bitem = new QentaCEE_Stdlib_Basket_Item();
			$bitem->setArticleNumber( 'shipping' );
			$bitem->setUnitGrossAmount( number_format( $order->getShippingInclTax(), $precision, '.', '' ) );
			$bitem->setUnitNetAmount( number_format( $order->getShippingAmount(), $precision, '.', '' ) );
			$bitem->setUnitTaxAmount( number_format( $order->getShippingTaxAmount(), $precision, '.', '' ) );
            if ( $bitem->getUnitNetAmount() > 0 ) {
                $bitem->setUnitTaxRate( ( ( $bitem->getUnitGrossAmount() / $bitem->getUnitNetAmount() ) - 1 ) * 100 );
            } else {
                $bitem->setUnitTaxRate( 0 );
            }
			$bitem->setDescription( $order->getShippingDescription() );
			$bitem->setName( 'shipping' );
			$basket->addItem( $bitem );

			$init->setBasket( $basket );
		}

		if ( $helper->getConfigData( 'options/sendconfirmationemail' ) ) {
			$init->setConfirmMail( Mage::getStoreConfig( 'trans_email/ident_general/email' ) );
		}

		if ( strlen( $helper->getConfigData( 'options/bgcolor' ) ) ) {
			$init->setBackgroundColor( $helper->getConfigData( 'options/bgcolor' ) );
		}

		if ( strlen( $helper->getConfigData( 'options/displaytext' ) ) ) {
			$init->setDisplayText( $helper->getConfigData( 'options/displaytext' ) );
		}

		if ( strlen( $helper->getConfigData( 'options/imageurl' ) ) ) {
			$init->setImageUrl( $helper->getConfigData( 'options/imageurl' ) );
		}

		if ( strlen( $helper->getConfigData( 'options/layout' ) ) ) {
			$init->setLayout( $helper->getConfigData( 'options/layout' ) );
		}

        if (strlen($this->getFinancialInstitution())) {
            $init->setFinancialInstitution($this->getFinancialInstitution());
        }

		$helper->log( __METHOD__ . ':' . print_r( $init->getRequestData(), true ), Zend_Log::INFO );

		try {
			$initResponse = $init->initiate();
		} catch ( Exception $e ) {
			$helper->log( __METHOD__ . ':' . $e->getMessage(), Zend_Log::ERR );
			$message = $helper->__( 'An error occurred during the payment process' );
			throw new Exception( $message );
		}

		if ( $initResponse->getStatus() == QentaCEE_QPay_Response_Initiation::STATE_FAILURE ) {
			$error   = $initResponse->getError();
			$message = 'init failed';
			if ( $error !== false ) {
				$message = $error->getConsumerMessage();
				if ( ! strlen( $message ) ) {
					// dont show technical error to consumer
					$message = $helper->__( 'An error occurred during the payment process' );
				}

				$helper->log( __METHOD__ . ':' . $error->getMessage(), Zend_Log::ERR );
			}


			throw new Exception( $message );
		}

		return $initResponse;
	}

	/**
	 * Get order model
	 *
	 * @return Mage_Sales_Model_Order
	 */
	public function getOrder() {
		if ( ! $this->_order ) {
			$paymentInfo  = $this->getInfoInstance();
			$this->_order = Mage::getModel( 'sales/order' )
			                    ->loadByIncrementId( $paymentInfo->getOrder()->getRealOrderId() );
		}

		return $this->_order;
	}

	/**
	 * Returns order reference
	 *
	 * @return string
	 */
	protected function getOrderReference() {
		return sprintf( '%010d', $this->getOrder()->getRealOrderId() );
	}

	/**
	 * Returns description of customer - will be displayed in Qenta backend
	 *
	 * @return string
	 */
	protected function getUserDescription() {
		$orderDescription = trim( sprintf( '%s %s %s', $this->getOrder()->getCustomerEmail(),
		                                   $this->getOrder()->getCustomerFirstname(), $this->getOrder()->getCustomerLastname() ) );

		if ( ! strlen( $orderDescription ) ) {
			$orderDescription = $this->getOrderReference();
		}

		return $orderDescription;
	}

	/**
	 * @return QentaCEE_Stdlib_ConsumerData
	 * @throws Zend_Controller_Request_Exception
	 */
	protected function _getConsumerData() {
		$consumerData = new QentaCEE_Stdlib_ConsumerData();
		$consumerData->setIpAddress( Mage::app()->getRequest()->getServer( 'REMOTE_ADDR' ) );
		$consumerData->setUserAgent( Mage::app()->getRequest()->getHeader( 'User-Agent' ) );

		$deliveryAddress = $this->getOrder()->getShippingAddress();
		$billingAddress  = $this->getOrder()->getBillingAddress();
		$dob             = $this->getCustomerDob();

		if ( $this->_forceSendAdditionalData || $this->_getHelper()->getConfigData( 'options/sendadditionaldata' ) ) {

			$consumerData->setEmail( $this->getOrder()->getCustomerEmail() );
			if ( $dob !== false ) {
				$consumerData->setBirthDate( $dob );
			}
			$consumerData->addAddressInformation( $this->_getAddress( $billingAddress, 'billing' ) );
			$consumerData->addAddressInformation( $this->_getAddress( $deliveryAddress, 'shipping' ) );
		}

		return $consumerData;
	}

	/**
	 * getter for customers birthDate
	 *
	 * @return DateTime|boolean
	 */
	public function getCustomerDob() {
		$order = $this->getOrder();
		$dob   = $order->getCustomerDob();
		if ( $dob ) {
			return new DateTime( $dob );
		}

		return false;
	}

	/**
	 * @return Qenta_CheckoutPage_Helper_Data
	 */
	protected function _getHelper() {
		return Mage::helper( 'qenta_checkoutpage' );
	}

	/**
	 * @param Mage_Sales_Model_Order_Address $source
	 * @param string $type
	 *
	 * @return QentaCEE_Stdlib_ConsumerData_Address
	 */
	protected function _getAddress( $source, $type = 'billing' ) {
		switch ( $type ) {
			case 'shipping':
				$address = new QentaCEE_Stdlib_ConsumerData_Address( QentaCEE_Stdlib_ConsumerData_Address::TYPE_SHIPPING );
				break;

			default:
				$address = new QentaCEE_Stdlib_ConsumerData_Address( QentaCEE_Stdlib_ConsumerData_Address::TYPE_BILLING );
				break;
		}

		$address->setFirstname( $source->getFirstname() );
		$address->setLastname( $source->getLastname() );
		$address->setAddress1( $source->getStreet1() );
		$address->setAddress2( $source->getStreet2() );
		$address->setZipCode( $source->getPostcode() );
		$address->setCity( $source->getCity() );
		$address->setCountry( $source->getCountry() );
		$address->setState( $source->getRegionCode() );
		$address->setPhone( $source->getTelephone() );
		$address->setFax( $source->getFax() );

		return $address;
	}

	/**
	 *
	 * Getter for the plugin version variable
	 *
	 * @return string  The plugin version
	 */
	public function getPluginVersion() {
		return $this->_pluginVersion;
	}

	/**
	 *
	 * Getter for the plugin name variable
	 *
	 * @return string  The plugin name
	 */
	public function getPluginName() {
		return $this->_pluginName;
	}

	public function getFinancialInstitution() {
		return null;
	}

	/**
	 * Return checkout quote object
	 *
	 * @return Mage_Sales_Model_Quote
	 */
	protected function _getQuote() {
		if ( ! $this->_quote ) {
			$this->_quote = $this->_getCheckoutSession()->getQuote();
		}

		return $this->_quote;
	}

	/**
	 * Return checkout session object
	 *
	 * @return Mage_Checkout_Model_Session
	 */
	private function _getCheckoutSession() {
		return Mage::getSingleton( 'checkout/session' );
	}

	/**
	 * @param Mage_Sales_Model_Quote $quote
	 *
	 * @return bool
	 */
	protected function _isAvailablePayolution( $quote ) {
		$dob = $quote->getCustomerDob();
		//we only need to check the dob if it's set. Else we ask for dob on payment selection page.
		if ( $dob ) {
			$dobObject      = new DateTime( $dob );
			$currentYear    = date( 'Y' );
			$currentMonth   = date( 'm' );
			$currentDay     = date( 'd' );
			$ageCheckDate   = ( $currentYear - 17 ) . '-' . $currentMonth . '-' . $currentDay;
			$ageCheckObject = new DateTime( $ageCheckDate );
			if ( $ageCheckObject < $dobObject ) {
				//customer is younger than 18 years. Installment not available
				return false;
			}
		}

		if ( $quote->hasVirtualItems() ) {
			return false;
		}

		if ( ! $this->compareAddresses( $quote ) ) {
			return false;
		}

		return parent::isAvailable( $quote );
	}

	/**
	 * @param Mage_Sales_Model_Quote $quote
	 *
	 * @return bool
	 */
	protected function compareAddresses( $quote ) {
		$billingAddress = $quote->getBillingAddress();

		$shippingAddress = $quote->getShippingAddress();

		if ( ! $shippingAddress->getSameAsBilling() ) {
			if ( $billingAddress->getCustomerAddressId() == null || $billingAddress->getCustomerAddressId() != $shippingAddress->getCustomerAddressId() ) {
				if ( //new line because it's easier to remove this way
					$billingAddress->getName() != $shippingAddress->getName() ||
					$billingAddress->getCompany() != $shippingAddress->getCompany() ||
					$billingAddress->getCity() != $shippingAddress->getCity() ||
					$billingAddress->getPostcode() != $shippingAddress->getPostcode() ||
					$billingAddress->getCountryId() != $shippingAddress->getCountryId() ||
					$billingAddress->getTelephone() != $shippingAddress->getTelephone() ||
					$billingAddress->getFax() != $shippingAddress->getFax() ||
					$billingAddress->getEmail() != $shippingAddress->getEmail() ||
					$billingAddress->getCountry() != $shippingAddress->getCountry() ||
					$billingAddress->getRegion() != $shippingAddress->getRegion() ||
					$billingAddress->getStreet() != $shippingAddress->getStreet()
				) {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * @param Mage_Sales_Model_Quote $quote
	 *
	 * @return bool
	 */
	protected function _isAvailableRatePay( $quote ) {
		$currencies = explode( ',', $this->getConfigData( 'currencies' ) );
		if ( ! in_array( $quote->getQuoteCurrencyCode(), $currencies ) ) {
			return false;
		}

		$dob    = $quote->getCustomerDob();

		//we only need to check the dob if it's set. Else we ask for dob on payment selection page.
		if ( $dob ) {
			$dobObject      = new DateTime( $dob );
			$currentYear    = date( 'Y' );
			$currentMonth   = date( 'm' );
			$currentDay     = date( 'd' );
			$ageCheckDate   = ( $currentYear - 18 ) . '-' . $currentMonth . '-' . $currentDay;
			$ageCheckObject = new DateTime( $ageCheckDate );
			if ( $ageCheckObject < $dobObject ) {
				return false;
			}
		}

		if ( $quote->hasVirtualItems() ) {
			return false;
		}

		if ( ! $this->compareAddresses( $quote ) ) {
			return false;
		}

		return parent::isAvailable( $quote );
	}
}
