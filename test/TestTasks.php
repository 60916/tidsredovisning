<?php

declare (strict_types=1);
require_once __DIR__ . '/../src/tasks.php';

/**
 * Funktion för att testa alla aktiviteter
 * @return string html-sträng med resultatet av alla tester
 */
function allaTaskTester(): string {
// Kom ihåg att lägga till alla testfunktioner
    $retur = "<h1>Testar alla uppgiftsfunktioner</h1>";
    $retur .= test_HamtaEnUppgift();
    $retur .= test_HamtaUppgifterSida();
    $retur .= test_RaderaUppgift();
    $retur .= test_SparaUppgift();
    $retur .= test_UppdateraUppgifter();
    return $retur;
}

/**
 * Tester för funktionen hämta uppgifter för ett angivet sidnummer
 * @return string html-sträng med alla resultat för testerna 
 */
function test_HamtaUppgifterSida(): string {
    $retur="<h2>test_HamtaUppgfiterSida</h2>";
    try {
    //Misslyckas med att hämta sida -1
    $svar=hamtaSida("-1");
    if($svar->getStatus()===400) {
        $retur .="<p class='ok'>Misslyckades med att hämta sida -1, som förväntat</p>";
    }
    else {
        $retur .="<p class='error'>Misslyckades med att hämta sida -1<br>"
        . $svar->getStatus() . " returnerades istället</p>";
    }

    //Misslyckas med att hämta sida 0
    $svar=hamtaSida("0");
    if($svar->getStatus()===400) {
        $retur .="<p class='ok'>Misslyckades med att hämta sida 0, som förväntat</p>";
    }
    else {
        $retur .="<p class='error'>Misslyckades med att hämta sida 0<br>"
        . $svar->getStatus() . " returnerades istället</p>";
    }

    //Misslyckas med att hämta sida sju
    $svar=hamtaSida("sju");
    if($svar->getStatus()===400) {
        $retur .="<p class='ok'>Misslyckades med att hämta sida sju, som förväntat</p>";
    }
    else {
        $retur .="<p class='error'>Misslyckades med att hämta sida <i>sju<i><br>"
        . $svar->getStatus() . " returnerades istället</p>";
    }

    //Lyckas med att hämta sida 1
    $svar=hamtaSida("1",2);
    if($svar->getStatus()===200) {
        $retur .="<p class='ok'>Lyckades med att hämta sida 1</p>";
        $sista=$svar->getContent()->pages;
    }
    else {
        $retur .="<p class='error'>Misslyckades med att hämta sida 1<br>"
        . $svar->getStatus() . " returnerades istället för förväntat 200</p>";
    }

    //Misslyckas med att hämta sida > antal sidor
    if(isset($sista)) {
        $sista++;
        $svar=hamtaSida("$sista",2);
        if($svar->getStatus()===400) {
            $retur .="<p class='ok'>Misslyckades med att hämta sida > antal sidor</p>";
        }
        else {
            $retur .="<p class='error'>Misslyckat test att hämta sida > antal sidor<br>"
            . $svar->getStatus() . " returnerades istället för förväntat 200</p>";
        }
    }

    } 
    catch (Exception $ex) {
        $retur .= "<p class='error'>Något gick fel, meddelandet säger:<br> {$ex->getMessage()}</p>";
    }

    return $retur;
}

/**
 * Test för funktionen hämta uppgifter mellan angivna datum
 * @return string html-sträng med alla resultat för testerna
 */
function test_HamtaAllaUppgifterDatum(): string {
    $retur = "<h2>test_HamtaAllaUppgifterDatum</h2>";
    try {
        //mysslyckas med från=igår till=2024-01-01
        $svar=hamtaDatum('igår', '2024-01-01');
        if($svar->getStatus()===400) {
            $retur .="<p class='ok'> Misslyckades med att hämta poster mellan <i>igår</i> och 2024-01-01</p>";
        } else {
            $retur .="<p class='error'> Misslyckades test med att hämta poster mellan <i>igår</i> och 2024-01-01<br>"
            . $svar->getStatus() . "returnerades istället för förväntat 400 </p>";
        }

        //mysslyckas med från=2024-01-01 till=imorgon
        $svar= hamtaDatum('2024-01-01', 'imorgon');
        if($svar->getStatus()===400) {
            $retur .="<p class='ok'> Misslyckades med att hämta poster mellan 2024-01-01 och <i>imorgon</i> som förväntat</p>";
        } else {
            $retur .="<p class='error'> Misslyckades test med att hämta poster mellan 2024-01-01 och <i>imorgon</i><br>"
            . $svar->getStatus() . "returnerades istället för förväntat 400 </p>";
        }

        //mysslyckas med från=2024-12-37 till=2024-01-01
        $svar= hamtaDatum('2024-12-37', '2024-01-01');
        if($svar->getStatus()===400) {
            $retur .="<p class='ok'> Misslyckades med att hämta poster mellan 2024-12-37 och 2024-01-01 som förväntat</p>";
        } else {
            $retur .="<p class='error'> Misslyckades test med att hämta poster mellan 2024-12-37 och 2024-01-01 <br>"
            . $svar->getStatus() . "returnerades istället för förväntat 400 </p>";
        }

        //mysslyckas med från=2024-01-01 till=2024-01-37
        $svar= hamtaDatum('2024-01-01', '2024-01-37');
        if($svar->getStatus()===400) {
            $retur .="<p class='ok'> Misslyckades med att hämta poster mellan 2024-01-01 och 2024-01-37 som förväntat</p>";
        } else {
            $retur .="<p class='error'> Misslyckades test med att hämta poster mellan 2024-01-01 och 2024-01-37 <br>"
            . $svar->getStatus() . "returnerades istället för förväntat 400 </p>";
        }

        //mysslyckas med från=2024-01-01 till=2023-01-01
        $svar= hamtaDatum('2024-01-01', '2023-01-01');
        if($svar->getStatus()===400) {
            $retur .="<p class='ok'> Misslyckades med att hämta poster mellan 2024-01-01 och 2023-01-01 som förväntat</p>";
        } else {
            $retur .="<p class='error'> Misslyckades test med att hämta poster mellan 2024-01-01 och 2023-01-01 <br>"
            . $svar->getStatus() . "returnerades istället för förväntat 400 </p>";
        }

        //Lyckas med korrekta datum
        //Leta upp månad med poster
        $db=connectDb();
        $stmt=$db->query("SELECT YEAR(Datum), MONTH(Datum), COUNT(*) AS antal "
                . " FROM uppgifter "
                . " GROUP BY YEAR(Datum), MONTH(Datum) " 
                . " ORDER BY antal DESC "
                . " LIMIT 0,1");
            $row=$stmt->fetch();
            $ar= $row [0];
            $manad= substr("0$row[1]",-2);
            $antal= $row [2];

            //hämta alla poster från denna månad
            $svar= hamtaDatum("$ar-$manad-01", date('Y-m-d', strtotime("Last day of $ar-$manad")));
            if($svar->getStatus()===200 && count($svar->getContent()->tasks)===$antal) {
                $retur .="<p class='ok'> Lyckades hämta $antal poster för denna månad $ar-$manad</p>";
            } else {
                $retur .="<p class='error'> misslyckades hämta $antal poster för denna månad $ar-$manad<br>"
                . $svar->getStatus() . "returnerade istället förväntat 200<br>"
                    . print_r($svar->getContent(), true) . "</p>";
            }

    } catch (Exception $ex) {
        $retur .= "<p class='error'>Något gick fel, meddelandet säger:<br> {$ex->getMessage()}</p>";
    }

    return $retur;
    
}

/**
 * Test av funktionen hämta enskild uppgift
 * @return string html-sträng med alla resultat för testerna
 */
function test_HamtaEnUppgift(): string {
    $retur = "<h2>test_HamtaEnUppgift</h2>";

    try {
        // Misslyckas med att hämta id=0
        $svar=hamtaEnskildUppgift("0");
        if($svar->getStatus()===400) {
            $retur .="<p class='ok'> Misslyckades med att hämta uppgift med id=0, som förväntat</p>";
        } else {
            $retur .="<p class='ok'> Misslyckades med att hämta uppgift med id=0, som förväntat"
                    . $svar->getStatus() . "returnerade istället förväntat 400<br>"
                    . print_r($svar->getContent(), true) . "</p>";
        }

        // Misslyckas med att hämta id=sju
        $svar=hamtaEnskildUppgift("sju");
        if($svar->getStatus()===400) {
            $retur .="<p class='ok'> Misslyckades med att hämta uppgift med id=sju, som förväntat</p>";
        } else {
            $retur .="<p class='ok'> Misslyckades med att hämta uppgift med id=sju, som förväntat"
                    . $svar->getStatus() . "returnerade istället förväntat 400<br>"
                    . print_r($svar->getContent(), true) . "</p>";
        }
        // Misslyckas med att hämta id=3.14
        $svar=hamtaEnskildUppgift("3.14");
        if($svar->getStatus()===400) {
            $retur .="<p class='ok'> Misslyckades med att hämta uppgift med id=3.14, som förväntat</p>";
        } else {
            $retur .="<p class='ok'> Misslyckades med att hämta uppgift med id=3.14, som förväntat"
                    . $svar->getStatus() . "returnerade istället förväntat 400<br>"
                    . print_r($svar->getContent(), true) . "</p>";
        }
        // Lyckas hämta id som finns



        //Koppla databas - skapa transaktion
        $db=connectDb();
        $db->beginTransaction();
        //förbered data
        $content= hamtaAllaAktiviteter()->getContent();
        $aktiviteter=$content['activities'];
        $aktivitetId=$aktiviteter[0]->id;
        $postdata=["date"=>date("Y-m-d"),
                    "time"=>"01:00",
                    "description"=>"Testpost",
                    "activityId"=>"$aktivitetId"];
        // Skapa post
        $svar= sparaNyUppgift($postdata);
        $taskId=$svar->getContent()->id;
                
        //Hämta nyss skapad post
        $svar = hamtaEnskildUppgift("$taskId");
        if($svar->getStatus()===200) {
            $retur .="<p class='ok'> Lyckades hämta en uppgift</p>";
        } else {
            $retur .="<p class='error'> Misslyckades hämta nyskapad uppgift"
                    . $svar->getStatus() . "returnerade istället förväntat 200<br>"
                    . print_r($svar->getContent(), true) . "</p>";
        }
        // misslyckas med att hämta id som inte finns
        $taskId++;
        $svar = hamtaEnskildUppgift("$taskId");
        if($svar->getStatus()===400) {
            $retur .="<p class='ok'> misslyckades hämta en uppgift som inte finns</p>";
        } else {
            $retur .="<p class='error'> Misslyckades hämta uppgift som inte finns"
                    . $svar->getStatus() . "returnerade istället förväntat 200<br>"
                    . print_r($svar->getContent(), true) . "</p>";
        }
    } catch (Exception $ex) {
        $retur .= "<p class='error'>Något gick fel, meddelandet säger:<br> {$ex->getMessage()}</p>";
    } finally {
        if($db) {
            $db->rollBack();
        }
    }
        

    return $retur;
}

/**
 * Test för funktionen spara uppgift
 * @return string html-sträng med alla resultat för testerna
 */
function test_SparaUppgift(): string {
    $retur = "<h2>test_SparaUppgift</h2>";

    try {
        $db=connectDb();
        // skapa en transaction sp att vi slipper skräp i databsen
        $db->beginTransaction();
        // misslyckas med att spara pga saknad aktivitetId
        $postdata=['time'=>'01:00',
            'date'=>'2023-12-31',
            'description'=>'Detta är en testpost'];

            $svar=sparaNyUppgift($postdata);
            if($svar->getStatus()===400) {
                $retur .="<p class='ok'> Misslyckades med att spara en post utan aktivitetid, som förväntat</p>";
            } else {
                "<p class='error'> misslyckades med att spara en post utan aktivitetid<br>"
                . $svar->getStatus() . "returnerades istället för förväntat 400<br>"
                    . print_r($svar->getContent(), true) . "</p>";
            }
        // Lyckas med att spara post utan beskrivning 
        //förbered data
        $content= hamtaAllaAktiviteter()->getContent();
        $aktiviteter=$content['activities'];
        $aktivitetId=$aktiviteter[0]->id;
        $postdata=['time'=>'01:00',
            'date'=>'2023-12-31',
            'activityId'=>"$aktivitetId"];

        //test
        $svar= sparaNyUppgift($postdata);
        if($svar->getStatus()===200) {
            $retur .="<p class='ok'> Lyckades med att spara uppgiften utan beskrivning</p>";
        } else {
            $retur .="<p class='error'> Misslyckades med arr spara uppgift utan beskrivning<br>"
            . $svar->getStatus() . "Returnerades istället för förväntat 200<br>"
            . print_r($svar->getContent(), true) . "</p>";
        }
        // Lyckas spara post med alla uppgifter
        $postdata=['time'=>'01:00',
            'date'=>'2023-12-31',
            'activityId'=>"$aktivitetId", 
            'description'=>'Detta är en testpost'];

            $svar=sparaNyUppgift($postdata);
            if($svar->getStatus()===200) {
                $retur .="<p class='ok'> Lyckas med att spara post med alla uppgifter, som förväntat</p>";
            } else {
                "<p class='error'> misslyckades med att spara en post med alla uppgifter<br>"
                . $svar->getStatus() . "returnerades istället för förväntat 200<br>"
                    . print_r($svar->getContent(), true) . "</p>";
            }
    

    } catch (Exception $ex) {
        $retur .= "<p class='error'>Något gick fel, meddelandet säger:<br> {$ex->getMessage()}</p>";
    } finally {
        if($db) {
            $db->rollBack();
        }
    }
    

    return $retur;
}

/**
 * Test för funktionen uppdatera befintlig uppgift
 * @return string html-sträng med alla resultat för testerna
 */
function test_UppdateraUppgifter(): string {
    $retur = "<h2>test_UppdateraUppgifter</h2>";

    try {
        // Koppla databas och starta transaktion 
        $db=connectDb();
        $db->beginTransaction();


        //Hämta postdata
    $svar= hamtaSida("1");
        if($svar->getStatus()!==200) {
            throw new Exception('Kunde inte hämta poster för test av Uppdatera uppgift');
        }
        $aktiviteter=$svar->getContent()->tasks;

        // misslyckas med ogiltigt id=0
        $postdata=get_object_vars($aktiviteter['0']); //gör en stdClass till en array
        $svar=uppdateraUppgift('0', $postdata);
        if($svar->getStatus()===400) {
            $retur .="<p class='ok'>Misslyckade med att hämta post med id=0, som förväntat</p>";
        } else {
            $retur .="<p class='error'>Misslyckade test med att hämta post med id=0<br>"
                . $svar->getStatus() . "Returnerades istället för förväntat 400<br>"
                . print_r($svar->getContent(), true) . "</p>";
        }



        //misslyckas med ogiltigt id=sju
        $svar=uppdateraUppgift('sju', $postdata);
        if($svar->getStatus()===400) {
            $retur .="<p class='ok'>Misslyckade med att hämta post med id=sju, som förväntat</p>";
        } else {
            $retur .="<p class='error'>Misslyckade test med att hämta post med id=sju<br>"
                . $svar->getStatus() . "Returnerades istället för förväntat 400<br>"
                . print_r($svar->getContent(), true) . "</p>";

        }

        //Misslyckas med ogiltigt id=3.14
        $svar=uppdateraUppgift('3.14', $postdata);
        if($svar->getStatus()===400) {
            $retur .="<p class='ok'>Misslyckade med att hämta post med id=3.14, som förväntat</p>";
        } else {
            $retur .="<p class='error'>Misslyckade test med att hämta post med id=3.14<br>"
                . $svar->getStatus() . "Returnerades istället för förväntat 400<br>"
                . print_r($svar->getContent(), true) . "</p>";
        }
        
        //Lyckas med id som finns
        $id=$postdata['id'];
        $postdata['activityId']=(string) $postdata['activityId'];
        $postdata['description'] = $postdata['description'] . "(Uppdaterad)";
        $svar= uppdateraUppgift("$id", $postdata);
        if($svar->getStatus()===200 && $svar->getContent()->result===true) {
            $retur .="<p class='ok'>Uppdatera uppgift lyckades, som förväntat</p>";
        } else {
            $retur .="<p class='error'>Uppdatera uppgift misslyckades<br>"
                . $svar->getStatus() . "returnerades istället för förväntat 200<br>"
                . print_r($svar->getContent(), true) . "</p>";
        }
        
        // misslyckas med samma data
        $svar=uppdateraUppgift("$id", $postdata);
        if($svar->getStatus()===200 && $svar->getContent()->result===false) {
            $retur .="<p class='ok'>Uppdatera uppgift misslyckades, som förväntat</p>";
        } else {
            $retur .="<p class='error'>Uppdatera uppgift misslyckades<br>"
                . $svar->getStatus() . "returnerades istället för förväntat 200<br>"
                . print_r($svar->getContent(), true) . "</p>";
        }

        //misslyckas med felaktig indata
        $postdata['time'] ='09:70';
        $svar= uppdateraUppgift("$id", $postdata);
        if($svar->getStatus()===400) {
            $retur .="<p class='ok'>Misslyckade med att uppdatera post med felaktik indata, som förväntat</p>";
        } else {
            $retur .="<p class='error'>Uppdatera uppgift med felaktif indata misslyckades<br>"
                . $svar->getStatus() . "returnerades istället för förväntat 400<br>"
                . print_r($svar->getContent(), true) . "</p>";
        }


        //lyckas med saknad beskrivning
        $postdata['time'] ='01:30';
        unset($postdata['description']);
        $svar= uppdateraUppgift("$id", $postdata);
        if($svar->getStatus()===200) {
            $retur .="<p class='ok'>Uppdatera post med saknad description lyckades</p>";
        } else {
            $retur .="<p class='error'>Uppdatera uppgift utan description misslyckades<br>"
                . $svar->getStatus() . "returnerades istället för förväntat 400<br>"
                . print_r($svar->getContent(), true) . "</p>";
        }


    } catch (Exception $ex) {
        $retur .= "<p class='error'>Något gick fel, meddelandet säger:<br> {$ex->getMessage()}</p>";
    } finally {
        if($db) {
            $db->rollback();
        }
    }

    return $retur;
}

function test_KontrolleraIndata(): string {
    $retur = "<h2>test_KontrolleraIndata</h2>";

    try {
        // Kontrollera datum
        $content= hamtaAllaAktiviteter()->getContent();
        $aktiviteter=$content['activities'];
        $aktivitetId=$aktiviteter[0]->id;
        $postdata=['time'=>'01:00',
            'date'=>'2023-12-31',
            'activityId'=>"$aktivitetId"];
        $svar = kontrolleraIndata($postdata);
        if (empty($svar)) {
            $retur .= "<p class='ok'>Lyckades med att skicka korrekt data</p>";
        } else {
            $retur .= "<p class='error'>Misslyckades med att skicka korrekt data<br>"
                . "Fel: " . implode(", ", $svar) . "</p>";
        

            }   
            $content= hamtaAllaAktiviteter()->getContent();
            $aktiviteter=$content['activities'];
            $aktivitetId=$aktiviteter[0]->id;
            $postdata=['time'=>'ogiltigt_datum',
                'date'=>'2023-12-31',
                'activityId'=>"$aktivitetId"];
            $svar = kontrolleraIndata($postdata);
            if (count($svar) === 1 && strpos($svar[0], 'Ogiltigt angivet datum') !== false) {
                $retur .= "<p class='ok'>Lyckades med att att skicka fel data</p>";
            } else {
                $retur .= "<p class='error'>Misslyckades med att skicka fel data data<br>"
                    . "Fel: " . implode(", ", $svar) . "</p>";
            
    
                }   
        

        //kontrollera tid

        //Kontrollera 


        $retur .= "<p class='error'>Inga tester implementerade</p>";
    } catch (Exception $ex) {
        $retur .= "<p class='error'>Något gick fel, meddelandet säger:<br> {$ex->getMessage()}</p>";
    }

    return $retur;
}

/**
 * Test för funktionen radera uppgift
 * @return string html-sträng med alla resultat för testerna
 */
function test_RaderaUppgift(): string {
    $retur = "<h2>test_RaderaUppgift</h2>";

    try {
        $retur .= "<p class='error'>Inga tester implementerade</p>";
    } catch (Exception $ex) {
        $retur .= "<p class='error'>Något gick fel, meddelandet säger:<br> {$ex->getMessage()}</p>";
    }

    return $retur;
}
