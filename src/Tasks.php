<?php

declare (strict_types=1);

use LDAP\Result;

require_once __DIR__ . '/activities.php';

/**
 * Hämtar en lista med alla uppgifter och tillhörande aktiviteter 
 * Beroende på indata returneras en sida eller ett datumintervall
 * @param Route $route indata med information om vad som ska hämtas
 * @return Response
 */
function tasklists(Route $route): Response {
    try {
        if (count($route->getParams()) === 1 && $route->getMethod() === RequestMethod::GET) {
            return hamtaSida($route->getParams()[0]);
        }
        if (count($route->getParams()) === 2 && $route->getMethod() === RequestMethod::GET) {
            return hamtaDatum($route->getParams()[0], $route->getParams()[1]);
        }
    } catch (Exception $exc) {
        return new Response($exc->getMessage(), 400);
    }

    return new Response("Okänt anrop", 400);
}

/**
 * Läs av rutt-information och anropa funktion baserat på angiven rutt
 * @param Route $route Rutt-information
 * @param array $postData Indata för behandling i angiven rutt
 * @return Response
 */
function tasks(Route $route, array $postData): Response {
    try {
        if (count($route->getParams()) === 1 && $route->getMethod() === RequestMethod::GET) {
            return hamtaEnskildUppgift($route->getParams()[0]);
        }
        if (count($route->getParams()) === 0 && $route->getMethod() === RequestMethod::POST) {
            return sparaNyUppgift($postData);
        }
        if (count($route->getParams()) === 1 && $route->getMethod() === RequestMethod::PUT) {
            return uppdateraUppgift( $route->getParams()[0], $postData);
        }
        if (count($route->getParams()) === 1 && $route->getMethod() === RequestMethod::DELETE) {
            return raderaUppgift($route->getParams()[0]);
        }
    } catch (Exception $exc) {
        return new Response($exc->getMessage(), 400);
    }
}

/**
 * Hämtar alla uppgifter för en angiven sida
 * @param string $sida
 * @return Response
 */
function hamtaSida(string $sida, int $posterPerSida=10): Response {

    //Kontrollera indata
    $sidnummer=filter_var($sida, FILTER_VALIDATE_INT);
    if($sidnummer===false || $sidnummer<1) {
        $retur=new stdClass();
        $retur->error=["Bad request", "Felaktigt angivet sidnummer"];
        return new Response($retur, 400);
    }

    //Koppla databas
    $db=connectDb();

    //Hämta poster
    $stmt=$db->query("SELECT COUNT(*) FROM uppgifter");
    $antalPoster=$stmt->fetchColumn();
    if(!$antalPoster) {
        $retur=new stdClass();
        $retur->error=["Inga poster kunde hittas"];
        return new Response($retur, 400);
    }

    $antalSidor=ceil($antalPoster/$posterPerSida);
    if($sidnummer>$antalSidor) {
        $retur=new stdClass();
        $retur->error=["Bad request", "Felaktigt sidnummer", "Det finns bara $antalSidor"];
        return new Response($retur, 400);
    }

    $forstaPost=($sidnummer-1)*$posterPerSida;

    $stmt=$db->query("SELECT u.ID, Datum, Tid, AktivitetID, Namn, Beskrivning "
        . "FROM uppgifter u INNER JOIN aktiviteter a ON AktivitetID=a.ID "
        . "ORDER BY Datum "
        . "LIMIT $forstaPost, $posterPerSida");
    $result=$stmt->fetchAll();
    $uppgifter=[];
    foreach($result as $row) {
        $rad=new stdClass();
        $rad->id=$row["ID"];
        $rad->activityId=$row["AktivitetID"];   
        $rad->date=$row["Datum"];
        $tid=new DateTime($row["Tid"]);
        $rad->time=$tid->format("H:i");
        $rad->activity=$row["Namn"];
        $rad->description=$row["Beskrivning"];
        $uppgifter[]=$rad;
    }

    //Returnera svar
    $retur=new stdClass();
    $retur->pages=$antalSidor;
    $retur->tasks=$uppgifter;
    return new Response($retur);
}

/**
 * Hämtar alla poster mellan angivna datum
 * @param string $from
 * @param string $tom
 * @return Response
 */
function hamtaDatum(string $from, string $tom): Response {

    //Kontrollera indatat
    $fromDate= DateTimeImmutable::createFromFormat("Y-m-d", $from);
    $tomDate= DateTimeImmutable::createFromFormat("Y-m-d", $tom);
    $datumFel=[];

    if($fromDate===false) {
        $datumFel[]="Ogiltigt från datum";
    }

    if($tomDate===false) {
        $datumFel[]="Ogiltigt till datum";
    }

    if($fromDate && $fromDate->format("Y-m-d")!==$from)  {
        $datumFel[]="Ogiltigt angivet från datum";
    }

    if($tomDate && $tomDate->format("Y-m-d")!==$tom)  {
        $datumFel[]="Ogiltigt angivet till datum";
    }

    if($fromDate && $tomDate && $fromDate->format("Y-m-d")>$tomDate->format('Y-m-d'))  {
        $datumFel[]="Från datum får inte vara större än till datum";
    }


    if(count($datumFel)>0) {
        $retur= new stdClass();
        $retur->error=$datumFel;
        array_unshift($retur->error, 'Bad request',);
        return new Response($retur, 400);
        
    }


    //Koppla databas
    $db= connectDb();

    // Exekvera sql
    $stmt=$db->prepare("SELECT u.ID, Datum, Tid, AktivitetID, Namn, Beskrivning "
    . "FROM uppgifter u INNER JOIN aktiviteter a ON AktivitetID=a.ID "
    . " WHERE datum BETWEEN :from AND :to "
    . "ORDER BY Datum ");
    $stmt->execute(['from'=>$fromDate->format("Y-m-d"), 'to'=>$tomDate->format("Y-m-d")]);


    $result=$stmt->fetchAll();
    $uppgifter=[];
    foreach($result as $row) {
        $rad=new stdClass();
        $rad->id=$row["ID"];
        $rad->activityId=$row["AktivitetID"];   
        $rad->date=$row["Datum"];
        $tid=new DateTime($row["Tid"]);
        $rad->time=$tid->format("H:i");
        $rad->activity=$row["Namn"];
        $rad->description=$row["Beskrivning"];
        $uppgifter[]=$rad;
    }

    // Returnera svar
    $retur=new stdClass();
    $retur->tasks=$uppgifter;
    return new Response($retur);
}

/**
 * Hämtar en enskild uppgiftspost
 * @param string $id Id för post som ska hämtas
 * @return Response
 */
function hamtaEnskildUppgift(string $id): Response {
    // Kontrollera indata
    $kontrolleratId=filter_var($id, FILTER_VALIDATE_INT);
    if(!$kontrolleratId) {
        $retur=new stdClass();
        $retur->error=['Bad request', 'Felaktivt angiven id'];
        return new Response ($retur, 400);
    }


    if($kontrolleratId && $kontrolleratId<1) {
        $retur=new stdClass();
        $retur->error=['Bad request', 'Ogiltigt id'];
        return new Response ($retur, 400);
    }
    //Koppla databas
    $db=connectDb();

    //Exekvera SQL
    $stmt=$db->prepare("SELECT u.id, tid, datum, beskrivning, aktivitetid, namn "
                . "FROM uppgifter u INNER JOIN aktiviteter a ON aktivitetId=a.id "
                . "WHERE u.id=:id");
    $stmt->execute(['id'=>$kontrolleratId]);
    //Returnera svar
    if($row=$stmt->fetch()) {
        $retur= new stdClass();
        $retur->id=$row['id'];
        $retur->date=$row['datum'];
        $retur->time=substr($row['tid'], 0,-3);
        $retur->activity=$row['namn'];
        $retur->activityId=$row['aktivitetid'];
        return new Response($retur);
    } else {
        $retur = new stdClass();
        $retur->error=['Hämta misslyckades', 'Kunde inte hitta uppgift med angiven id'];
        return new Response($retur, 400);
    }
}

/**
 * Sparar en ny uppgiftspost
 * @param array $postData indata för uppgiften
 * @return Response
 */
function sparaNyUppgift(array $postdata): Response {
    //kontrollera indata
    $felMeddelande= kontrolleraIndata($postdata);

    if(count($felMeddelande)>0) {
        $retur=new stdClass();
        $retur->error=$felMeddelande;
        array_unshift($retur->error, 'Bad request');
        return new Response($retur, 400);
    }
        

    // Koppla databas
    $db=connectDb();


    // Exekvera databasfråga
    $stmt=$db->prepare("INSERT INTO uppgifter (datum, tid, beskrivning, aktivitetId)"
    . "VALUE (:datum, :tid, :beskrivning, :aktivitetId)");
    $stmt->execute(['datum'=>$postdata['date'], 'tid'=>$postdata['time'],
                    'beskrivning'=> trim(filter_var($postdata['description']??'', FILTER_SANITIZE_SPECIAL_CHARS)),
                    'aktivitetId'=>$postdata['activityId']]);

    // kontrollera svaret
    if($stmt->rowCount()===1) {
        $retur=new stdClass();
        $retur->id=$db->lastInsertId();
        $retur->message=["skapa ny post lyckades", "1 post sparad"];
        return new Response($retur);
    } else {
        $retur=new stdClass();
        $retur->error=['Fel vid databasanrop', 'Kunde inte skapa post'];
        return new Response($retur, 400);
    }

    //Skicka utdata
}

/**
 * Uppdaterar en angiven uppgiftspost med ny information 
 * @param string $id id för posten som ska uppdateras
 * @param array $postData ny data att sparas
 * @return Response
 */
function uppdateraUppgift(string $id, array $postdata): Response {
    // kontrollera indata
    $kontrolleratId=filter_var($id, FILTER_VALIDATE_INT);
    if (!$kontrolleratId) {
        $retur= new stdClass();
        $retur->error=['Bad request'];
        return new Response($retur, 400);
    }
    if($kontrolleratId<1) {
        $retur= new stdClass();
        $retur->error=['Bad request', 'Ogiltigt id'];
        return new Response($retur, 400);
    }

    //kontrollera postdata
    $error = kontrolleraIndata($postdata);
    if(count($error)!==0) {
        $retur= new stdClass();
        $retur->error=$error;
        return new Response($retur, 400);
    }

    // koppla databas
    $db=connectDb();

    //exekvera SQL
    $stmt=$db->prepare("UPDATE uppgifter SET "
                . "datum=:date, tid=:time, aktivitetid=:activityid, beskrivning=:description "
                . "WHERE id=:id");
    $stmt->execute(['date'=>$postdata['date'], 'time'=>$postdata['time'], 'activityid'=>$postdata['activityId'],
        'description'=>$postdata['description']?? "", 'id'=>$kontrolleratId]);

    //returnera svar
    if($stmt->rowCount()===1) {
        $retur=new stdClass();
        $retur->result=true;
        $retur->message=['Uppdatering lyckades', '1 post uppdaterad'];
    } else {
        $retur=new stdClass();
        $retur->result=false;
        $retur->message=['Uppdatering misslyckade', 'Ingen post uppdaterad'];
    }

    return new Response($retur);
}

/**
 * Raderar en uppgiftspost
 * @param string $id Id för posten som ska raderas
 * @return Response
 */
function raderaUppgift(string $id): Response {
    // Kontrollera indata
    $kontrolleratId=filter_var($id, FILTER_VALIDATE_INT);
    if(!$kontrolleratId) {
        $retur=new stdClass();
        $retur->error=['Bad request', 'Felaktivt angiven id'];
        return new Response ($retur, 400);
    }


    if($kontrolleratId && $kontrolleratId<1) {
        $retur=new stdClass();
        $retur->error=['Bad request', 'Ogiltigt id'];
        return new Response ($retur, 400);
    }
    //Koppla databas
    $db=connectDb();


    //exekvera databas
    $stmt=$db->prepare("DELETE FROM uppgifter WHERE id=:id");
    $stmt->execute(['id'=>$kontrolleratId]);

    //returnera svar
    if($stmt->rowCount()===1) {
        $retur=new stdClass();
        $retur->result=true;
        $retur->message=['radering lyckades', '1 post raderad'];
    } else {
        $retur=new stdClass();
        $retur->result=false;
        $retur->message=['radering misslyckade', 'Ingen post raderar'];
    }

    return new Response($retur);

}
