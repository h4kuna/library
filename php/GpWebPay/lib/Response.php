<?php

namespace Pay3dSecure;
use Nette\Environment, Nette\Debug;

require_once 'IResponse.php';

/**
 * Description of Response
 *
 * @author Milan Matějček
 */
class Response implements IResponse
{
    // <editor-fold defaultstate="collapsed" desc="primary return code">
    static protected $returnPrimary = array(
0  => 'OK',
// OK
1  => 'Field too long',
// Pole je příliš dlouhé
2  => 'Field too short',
// Pole je příliš krátké
3  => 'Incorrect content of field',
// Chybný obsah pole
4  => 'Field is null',
// Pole je prázdné
5  => 'Missing required field',
// Chybí povinné pole
11 => 'Unknown merchant',
// Neznámý obchodník
14 => 'Duplicate order number',
// Duplikátní číslo objednávky
15 => 'Object not found',
// Objekt nenalezen
17 => 'Amount to deposit exceeds approved amount',
// Částka k úhradě překročila autorizovanou částku
18 => 'Total sum of credited amounts exceeded deposited amount',
//Součet kreditovaných částek překročil uhrazenou částku
20 => 'Object not in valid state for operation',
/* Objekt není ve stavu odpovídajícím této operaci
Info: Pokud v případě vytváření objednávky
(CREATE_ORDER) obdrží obchodník tento
návratový kód, vytvoření objednávky již proběhlo
a objednávka je v určitém stavu – tento návratový
kód je zapříčiněn aktivitou držitele karty
(například pokusem o přechod zpět, použití
refresh…).*/
26 => 'Technical problem in connection to authorization center',
// Technický problém při spojení s autorizačním centrem
27 => 'Incorrect order type',
// Chybný typ objednávky
28 => 'Declined in 3D',
// Zamítnuto v 3D, Info: důvod zamítnutí udává SRCODE
30 => 'Declined in AC',
// Zamítnuto v autorizačním centru, Info: Důvod zamítnutí udává SRCODE
31 => 'Wrong digest',
// Chybný podpis Wrong digest
1000 => 'Technical problem',
// Technický problém
    );
    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="secondary return code">
    static protected $returnSecondary = array(
0  => 'OK',
1  => 'ORDERNUMBER',
2  => 'MERCHANTNUMBER',
6  => 'AMOUNT',
7  => 'CURRENCY',
8  => 'DEPOSITFLAG',
10 => 'MERORDERNUM',
11 => 'CREDITNUMBER',
12 => 'OPERATION',
18 => 'BATCH',
22 => 'ORDER',
24 => 'URL',
25 => 'MD',
26 => 'DESC',
34 => 'DIGEST',
// V případě PRCODE 28 se mohou vrátit následující SRCODE
3000 => 'Cardholder not authenticated in 3D. Contact your card issuer.',
// Neúspěšné ověření držitele karty. Kontaktujte vydavatele karty.
3001 => 'Authenticated.',
// Držitel karty ověřen
3002 => 'Issuer or Cardholder not participating in 3D. Contact your card issuer.',
// Vydavatel karty nebo karta není zapojena do 3D. Kontaktujte vydavatele karty.
3004 => 'Issuer not participating or Cardholder not enrolled. Contact your card issuer.',
// Vydavatel karty není zapojen do 3D nebo karta nebyla aktivována. Kontaktujte vydavatele karty.
3005 => 'Technical problem during Cardholder authentication. Contact your card.',
// Technický problém při ověření držitele karty. Kontaktujte vydavatele karty.
3006 => 'Technical problem during Cardholder authentication.',
// Technický problém při ověření držitele karty.
3007 => 'Acquirer technical problem. Contact the merchant.',
// Technický problém v systému zúčtující banky. Kontaktujte obchodníka.
3008 => 'Unsupported card product. Contact your card issuer.',
// Použit nepodporovaný karetní produkt. Kontaktujte vydavatele karty.

// V případě PRCODE 30 se mohou vrátit následující SRCODE
1001 => 'Unsuccessful authorization – blocked card.',
// Neúspěšná autorizace – karta blokovaná
1002 => 'Authorization declined.',
// Autorizace zamítnuta
1003 => 'Unsuccessful authorization – Card problem. Contact your card issuer.',
// Neúspěšná autorizace – problém karty. Kontaktujte vydavatele karty.
1004 => 'Unsuccessful authorization – technical problem in authorization process.',
//Neúspěšná autorizace – technický problém v autorizačním centru
1005 => 'Unsuccessful authorization – Account problem. Contact your card issuer.',
// Neúspěšná autorizace – problém účtu. Kontaktujte vydavatele karty.
    );
    // </editor-fold>

    protected $primaryReturnCode;
    protected $secondaryReturnCode;
    protected $ok;
    protected $requestId;

    public function getVerify()
    {
        return array($this->primaryReturnCode, $this->secondaryReturnCode);
    }

    /**
     * vrací textový řetězec chybové zprávy
     * @return string
     */
    public function getPrimaryCode()
    {
        return self::$returnPrimary[$this->primaryReturnCode];
    }

    /**
     * @return string
     */
    public function getSecondaryCode()
    {
        return self::$returnSecondary[$this->secondaryReturnCode];
    }

    /**
     * @return float
     */
    public function getRequestId()
    {
        return $this->requestId;
    }

    /**
     * @return bool
     */
    public function check()
    {
			Ndebug::dump($this->primaryReturnCode == 0 && $this->secondaryReturnCode == 0);
        if($this->primaryReturnCode == 0 && $this->secondaryReturnCode == 0)
        //if($this->ok)
        {
            return TRUE;
        }

        $e2 = new GpWebPayException($this->getSecondaryCode(), $this->secondaryReturnCode);
        $e = new GpWebPayException($this->getPrimaryCode(), $this->primaryReturnCode, $e2);
        //@TODO vyhazovat nebo nevyhazovat vyjimku?, nyni pro devel jsem nevyhazoval pac bych se nikam nedostal
        Debug::log($e);
        return FALSE;
    }

}
