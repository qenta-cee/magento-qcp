<?php
/*
* Die vorliegende Software ist Eigentum von Wirecard CEE und daher vertraulich
* zu behandeln. Jegliche Weitergabe an dritte, in welcher Form auch immer, ist
* unzulaessig.
*
* Software & Service Copyright (C) by
* Wirecard Central Eastern Europe GmbH,
* FB-Nr: FN 195599 x, http://www.wirecard.at
*/

/**
 * @name WirecardCEE_Stdlib_Basket_Item
 * @category WirecardCEE
 * @package WirecardCEE_Stdlib
 * @subpackage Basket
 * @version 3.2.0
 */
class WirecardCEE_Stdlib_Basket_Item {

    /**
     * Constants - text holders
     * @var string
     */
    const ITEM_ARTICLE_NUMBER     = 'articleNumber';
    const ITEM_UNIT_GROSS_AMOUNT  = 'unitGrossAmount';
    const ITEM_UNIT_NET_AMOUNT    = 'unitNetAmount';
    const ITEM_DESCRIPTION        = 'description';
    const ITEM_NAME               = 'name';
    const ITEM_UNIT_TAX_AMOUNT    = 'unitTaxAmount';
    const ITEM_UNIT_TAX_RATE      = 'unitTaxRate';

    /**
     * Data holder
     *
     * @var Array
     */
    protected $_itemData;

    /**
     * Constructor
     * @param mixed(string|integer) optional $mArticleNumber
     */
    public function __construct($mArticleNumber = null) {
        if(!is_null($mArticleNumber)) {
            $this->setArticleNumber($mArticleNumber);
        }
    }

    /**
     * Sets the item tax amount
     *
     * @param mixed(integer|float) $fTaxAmount
     * @return WirecardCEE_Stdlib_Basket_Item
     */
    public function setUnitTaxAmount($fTaxAmount) {
        $this->_setField(self::ITEM_UNIT_TAX_AMOUNT, $fTaxAmount);
        return $this;
    }

    /**
     * Returns the tax amount
     *
     * @return mixed(integer|float)
     */
    public function getUnitTaxAmount() {
        return $this->_itemData[self::ITEM_UNIT_TAX_AMOUNT];
    }

    /**
     * Sets the item tax rate
     *
     * @param mixed(integer|float) $fTaxRate
     * @return WirecardCEE_Stdlib_Basket_Item
     */
    public function setUnitTaxRate($fTaxRate) {
        $this->_setField(self::ITEM_UNIT_TAX_RATE, $fTaxRate);
        return $this;
    }

    /**
     * Returns the tax rate
     *
     * @return mixed(integer|float)
     */
    public function getUnitTaxRate() {
        return $this->_itemData[self::ITEM_UNIT_TAX_RATE];
    }

    /**
     * Sets the article number for an item
     *
     * @param mixed(string|integer) $mArticleNumber
     * @return WirecardCEE_Stdlib_Basket_Item
     */
    public function setArticleNumber($mArticleNumber) {
        $this->_setField(self::ITEM_ARTICLE_NUMBER, $mArticleNumber);
        return $this;
    }

    /**
     * Returns the article number of an item
     *
     * @return mixed(string|integer)
     */
    public function getArticleNumber() {
        return $this->_itemData[self::ITEM_ARTICLE_NUMBER];
    }

    /**
     * Sets the gross amount for a unit
     *
     * @param mixed(integer|float) $fAmount
     * @return WirecardCEE_Stdlib_Basket_Item
     */
    public function setUnitGrossAmount($fAmount) {
        $this->_setField(self::ITEM_UNIT_GROSS_AMOUNT, $fAmount);
        return $this;
    }

    /**
     * Returns the gross amount for a unit
     *
     * @return mixed(integer|float)
     */
    public function getUnitGrossAmount() {
        return $this->_itemData[self::ITEM_UNIT_GROSS_AMOUNT];
    }

    /**
     * Sets the net amount for a unit
     *
     * @param mixed(integer|float) $fAmount
     * @return WirecardCEE_Stdlib_Basket_Item
     */
    public function setUnitNetAmount($fAmount) {
        $this->_setField(self::ITEM_UNIT_NET_AMOUNT, $fAmount);
        return $this;
    }

    /**
     * Returns the net amount for a unit
     *
     * @return mixed(integer|float)
     */
    public function getUnitNetAmount() {
        return $this->_itemData[self::ITEM_UNIT_NET_AMOUNT];
    }

    /**
     * Sets the item description
     *
     * @param string $sDescription
     * @return WirecardCEE_Stdlib_Basket_Item
     */
    public function setDescription($sDescription) {
        $this->_setField(self::ITEM_DESCRIPTION, (string) $sDescription);
        return $this;
    }

    /**
     * Retuns the item description
     *
     * @return string
     */
    public function getDescription() {
        return (string) $this->_itemData[self::ITEM_DESCRIPTION];
    }

    /**
     * Sets the item name
     *
     * @param string $sName
     * @return WirecardCEE_Stdlib_Basket_Item
     */
    public function setName($sName) {
        $this->_setField(self::ITEM_NAME, (string) $sName);
        return $this;
    }

    /**
     * Retuns the item name
     *
     * @return string
     */
    public function getName() {
        return (string) $this->_itemData[self::ITEM_NAME];
    }

    /**
     * Destructor
     */
    public function __destruct() {
        unset($this);
    }

    /***************************************
     *         PROTECTED METHODS           *
     ***************************************/

    /**
     * Field setter
     *
     * @param string $sName
     * @param mixed $mValue
     */
    protected function _setField($sName, $mValue) {
        $this->_itemData[$sName] = $mValue;
    }
}