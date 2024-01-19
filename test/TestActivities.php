<?php

declare (strict_types=1);
require_once '../src/activities.php';

/**
 * Funktion för att testa alla aktiviteter
 * @return string html-sträng med resultatet av alla tester
 */
function allaActivityTester(): string {
    // Kom ihåg att lägga till alla funktioner i filen!
    $retur = "";
    $retur .= test_HamtaAllaAktiviteter();
    $retur .= test_HamtaEnAktivitet();
    $retur .= test_SparaNyAktivitet();
    $retur .= test_UppdateraAktivitet();
    $retur .= test_RaderaAktivitet();

    return $retur;
}

/**
 * Tester för funktionen hämta alla aktiviteter
 * @return string html-sträng med alla resultat för testerna 
 */
function test_HamtaAllaAktiviteter(): string {
    $retur = "<h2>test_HamtaAllaAktiviteter</h2>";
    try {
        $svar=hamtaAllaAktiviteter();
        if($svar->getStatus()===200) {
            $retur .="<p class='ok'>Hämta alla aktiviteter lyckades " . count($svar->getContent() )
                    ." poster returnerades</>";
        }
        else {
            $retur .="<p class='error'>Hämta alla aktiviteter misslyckades<br>"
                    . $svar->getStatus() . " returnerades</p>";
        }
        $retur .= "<p class='error'>Inga tester implementerade</p>";
    } catch (Exception $ex) {
        $retur .= "<p class='error'>Något gick fel, meddelandet säger:<br> {$ex->getMessage()}</p>";
    }

    return $retur;
}

/**
 * Tester för funktionen hämta enskild aktivitet
 * @return string html-sträng med alla resultat för testerna 
 */
function test_HamtaEnAktivitet(): string {
    $retur = "<h2>test_HamtaEnAktivitet</h2>";
    try {
        $retur .= "<p class='error'>Inga tester implementerade</p>";
    } catch (Exception $ex) {
        $retur .= "<p class='error'>Något gick fel, meddelandet säger:<br> {$ex->getMessage()}</p>";
    }

    return $retur;
}

/**
 * Tester för funktionen spara aktivitet
 * @return string html-sträng med alla resultat för testerna 
 */
function test_SparaNyAktivitet(): string {
    $retur = "<h2>test_SparaNyAktivitet</h2>";

    $nyAktivitet="Aktivitet" . time();

    //koppla databas
    $db=connectDb();

    //starta transaktion
    $db->beginTransaction();

    //spara tom aktivitet misslyckat
    $svar=sparaNyAktivitet('');
    if($svar->getSTatus()===400) {
        $retur .="<p class'ok'> Spara tom aktivitet missltckades, som förväntatat </p>";
    } else {
        $retur .="<p class='error'>Spara tom aktivitet misslyckades, status" . $svar->getStatus()
        . "Returnerades istället som förväntat 400 </p>";
    }
        

    //spara ny aktivitet . lyckat
    $svar= sparaNyAktivitet($nyAktivitet); 
    if ($svar->getStatus()===200) {
        $retur .="<p class'ok'> Spara tom aktivitet lyckades </p>";
    } else {
        $retur .="<p class='error'>Spara tom aktivitet misslyckades, status" . $svar->getStatus()
        . "Returnerades istället som förväntat 400 </p>";
    }
        
    
    //spara ny aktvitet - misslyckat
    $svar= sparaNyAktivitet($nyAktivitet); 
    if ($svar->getStatus()===400) {
        $retur .="<p class'ok'> Spara duplicerad aktivitet misslyckat som förväntat </p>";
    } else {
        $retur .="<p class='error'>Spara duplicerad aktivitet misslyckades, status" . $svar->getStatus()
        . "Returnerades istället som förväntat 400 </p>";
    }
    

    try {
        $retur .= "<p class='error'>Inga tester implementerade</p>";
    } catch (Exception $ex) {
        $retur .= "<p class='error'>Något gick fel, meddelandet säger:<br> {$ex->getMessage()}</p>";
    } finally {
        //återställa databsen
        if($db) {
            $db->rollBack();
        }
    }

    return $retur;
}

/**
 * Tester för uppdatera aktivitet
 * @return string html-sträng med alla resultat för testerna 
 */
function test_UppdateraAktivitet(): string {
    $retur = "<h2>test_UppdateraAktivitet</h2>";

    try {
        $retur .= "<p class='error'>Inga tester implementerade</p>";
    } catch (Exception $ex) {
        $retur .= "<p class='error'>Något gick fel, meddelandet säger:<br> {$ex->getMessage()}</p>";
    }

    return $retur;
}

/**
 * Tester för funktionen radera aktivitet
 * @return string html-sträng med alla resultat för testerna 
 */
function test_RaderaAktivitet(): string {
    $retur = "<h2>test_RaderaAktivitet</h2>";
    try {
        $retur .= "<p class='error'>Inga tester implementerade</p>";
    } catch (Exception $ex) {
        $retur .= "<p class='error'>Något gick fel, meddelandet säger:<br> {$ex->getMessage()}</p>";
    }

    return $retur;
}
