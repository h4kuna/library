<?php

namespace Invoice;
/**
 * IParticipant - part of Invoice control plugin for Nette Framework.
 *
 * @copyright  Copyright (c) 2009 Ondřej Brejla
 * @license    New BSD License
 * @link       http://github.com/OndrejBrejla/Nette-InvoiceControl
 * @package    Nette\Extras
 * @version    0.3.1
 */
interface IParticipant {

    /**
     * Returns the name of participant.
     *
     * @return string
     */
    public function getName();

    /**
     * Sets the street of participant.
     *
     * @param string $street
     * @return void
     */
    public function getStreet();

    /**
     * Returns the house number of participant.
     *
     * @return string
     */
    public function getHouseNumber();

    /**
     * Returns the city of participant.
     *
     * @return string
     */
    public function getCity();

    /**
     * Returns the zip of participant.
     *
     * @return string
     */
    public function getZip();

    /**
     * Returns the identification number of participant.
     *
     * @return string
     */
    public function getIn();

    /**
     * Returns the tax identification number of participant.
     *
     * @return string
     */
    public function getTin();

    /**
     * Returns the account number of participant.
     *
     * @return string
     */
    public function getAccountNumber();

}

