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
 * @name WirecardCEE_Stdlib_Return_Success_SepaDD
 * @category WirecardCEE
 * @package WirecardCEE_QMore
 * @subpackage Return_Success
 * @version 3.2.0
 */
abstract class WirecardCEE_Stdlib_Return_Success_SepaDD extends WirecardCEE_Stdlib_Return_Success {

    /**
     * getter for the return parameter creditorId
     *
     * @return string
     */
    public function getCreditorId() {
        return $this->creditorId;
    }

    /**
     * getter for the return parameter dueDate
     *
     * @return string
     */
    public function getDueDate() {
        return $this->dueDate;
    }

    /**
     * getter for the return parameter mandateId
     *
     * @return string
     */
    public function getMandateId() {
        return $this->mandateId;
    }

    /**
     * getter for the return parameter mandateSignatureDate
     *
     * @return string
     */
    public function getMandateSignatureDate() {
        return $this->mandateSignatureDate;
    }

}