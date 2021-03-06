<?php

require_once 'DB.php' ;

require_once 'Slim/Slim.php' ;

Slim\Slim::registerAutoloader() ;

$app = new Slim\Slim(array(
    ));
$app->log->setEnabled(true) ;

function get_seances($sqlcommand, $arguments) {
    DB::begin_transaction() ;
    $stmt = DB::execute($sqlcommand, $arguments) ;
    $items = DB::getAll($stmt) ;
    DB::transaction_commit() ;
    return $items ;
}

function additionHeure($heure,$duree)
{
    $m= substr($duree,-2);
    $h= substr($duree,0,-2);
    $min=$m;
    $heur=$h;
    $m= substr($heure,-2);
    $h= substr($heure,0,-2);
    $min+=$m;
    $heur+=$h;
    $hsup=$min/60;
    $hsup=floor($hsup);
    $heur+=$hsup;
    $mrest=$min%60;
    if ($mrest==0) 
    {
        $mrest="00";
    }
    $heure=$heur.=$mrest;
    return $heure;
}

function soustractionHeure($heure,$duree)
{
    $m= substr($heure,-2);
    $h= substr($heure,0,-2);
    $min=$m;
    $heur=$h;
    $m= substr($duree,-2);
    $h= substr($duree,0,-2);
    $min-=$m;
    $heur-=$h;
    $hsup=$min/60;
    $hsup=floor($hsup);
    $heur-=$hsup;
    $mrest=$min%60;
    if ($mrest==0) 
    {
        $mrest="00";
    }
    $heure=$heur.=$mrest;
    return $heure;
}

$app->get('/cours', function() {
    $app = Slim\Slim::getInstance() ;
    $module = $app->request->get('module');

            $sql = "select codeMatiere FROM matieres where nom='$module'";
            $stmt = DB::getModule($sql) ;
            $module = DB::getNext($stmt) ;
            $test = json_encode($module, JSON_PRETTY_PRINT) ;
            $test2 = json_decode($test, true);
            $module = $test2[0];

    $response = $app->response() ;
    $response->setStatus(200) ;
    $response->headers->set('Content-Type', 'application/json');

    DB::begin_transaction() ;
    $sql = "select * ".
        "from enseignements e  ".
        "where (e.deleted = 0)  ".
        "     and e.codeProprietaire  BETWEEN 3000 AND 3099 and e.codeMatiere=$module ".
        "order by  e.nom ;" ;
    $stmt = DB::execute($sql, array()) ;
    $items = DB::getAll($stmt) ;
    DB::transaction_commit() ;
    echo json_encode($items, JSON_PRETTY_PRINT) ;
}) ;

$app->get('/modules', function() {
    $app = Slim\Slim::getInstance() ;
    $response = $app->response() ;
    $response->setStatus(200) ;
    $response->headers->set('Content-Type', 'application/json');

    DB::begin_transaction() ;
    $sql = "select * ".
        "from matieres m  ".
        "where (m.deleted = 0)  ".
        "     and m.codeProprietaire  BETWEEN 3000 AND 3099 ".
        "order by  m.nom ;" ;
    $stmt = DB::execute($sql, array()) ;
    $items = DB::getAll($stmt) ;
    DB::transaction_commit() ;
    echo json_encode($items, JSON_PRETTY_PRINT) ;
}) ;

$app->get('/version', function() {
    $app = Slim\Slim::getInstance() ;
    $response = $app->response() ;
    $response->setStatus(200) ;
    $response->headers->set('Content-Type', 'application/json');
    $version = "0.9.00" ;
    echo json_encode($version, JSON_PRETTY_PRINT) ;
}) ;

$app->get('/groupes', function() {
    $app = Slim\Slim::getInstance() ;
    $response = $app->response() ;
    $response->setStatus(200) ;
    $response->headers->set('Content-Type', 'application/json');

    DB::begin_transaction() ;
    $sql = "select * ".
        "from ressources_groupes g  ".
        "where (g.deleted = 0)  ".
        "     and g.codeProprietaire BETWEEN 3000 AND 3100 ".
        "order by  g.nom ;" ;
    $stmt = DB::execute($sql, array()) ;
    $items = DB::getAll($stmt) ;
    DB::transaction_commit() ;
    echo json_encode($items, JSON_PRETTY_PRINT) ;
}) ;

$app->get('/profs', function() {
    $app = Slim\Slim::getInstance() ;
    $response = $app->response() ;
    $response->setStatus(200) ;
    $response->headers->set('Content-Type', 'application/json');

    DB::begin_transaction() ;
    $sql = "select * ".
        "from ressources_profs p  ".
        "where (p.deleted = 0)  ".
        "and (p.codeProf in (select codeProf from VIEW_PROFS_INFO)) ".
        "order by  p.nom ;" ;
    $stmt = DB::execute($sql, array()) ;
    $items = DB::getAll($stmt) ;
    DB::transaction_commit() ;
    echo json_encode($items, JSON_PRETTY_PRINT) ;
}) ;

$app->get('/salle', function() {
    $app = Slim\Slim::getInstance() ;
    $response = $app->response() ;
    $response->setStatus(200) ;
    $response->headers->set('Content-Type', 'application/json');

    DB::begin_transaction() ;
    $sql = "select codeSalle,nom ".
        "from ressources_salles s  ;" ;
    $stmt = DB::execute($sql, array()) ;
    $items = DB::getAll($stmt) ;
    DB::transaction_commit() ;
    echo json_encode($items, JSON_PRETTY_PRINT) ;
}) ;

$app->get('/verrificationTempsTotal', function() {
    $app = Slim\Slim::getInstance() ;
    $response = $app->response() ;
    $response->setStatus(200) ;
    $response->headers->set('Content-Type', 'application/json');

    $seance = $app->request->get('seance');

    $sql = "SELECT codeEnseignement FROM seances WHERE codeSeance=$seance";
            $stmt = DB::getModule($sql) ;
            $tuple = DB::getNext($stmt) ;
            $test = json_encode($tuple, JSON_PRETTY_PRINT) ;
            $test2 = json_decode($test, true);
            $codeEnseignement = $test2[0]; 

    $sql = "SELECT dureeTotale FROM enseignements where codeEnseignement=$codeEnseignement";
            $stmt = DB::getModule($sql) ;
            $tuple = DB::getNext($stmt) ;
            $test = json_encode($tuple, JSON_PRETTY_PRINT) ;
            $test2 = json_decode($test, true);
            $dureeTotale = $test2[0];

    $sql ="SELECT SUM(s.dureeSeance) FROM seances s JOIN seances_groupes sg ON s.codeSeance = sg.codeSeance WHERE codeEnseignement=$codeEnseignement And s.deleted =0 ";
            $stmt = DB::getModule($sql) ;
            $tuple = DB::getNext($stmt) ;
            $test = json_encode($tuple, JSON_PRETTY_PRINT) ;
            $test2 = json_decode($test, true);
            $SumDuree = $test2[0];

    /*DB::begin_transaction();
    $sql = "select codeSalle,nom ".
        "from ressources_salles s  ;" ;
    $stmt = DB::execute($sql, array()) ;
    $items = DB::getAll($stmt) ;
    DB::transaction_commit() ;
    echo json_encode($SumDuree, JSON_PRETTY_PRINT) ;*/
}) ;

$app->get('/verrificationTempsTotal2', function() {
    $app = Slim\Slim::getInstance() ;
    $response = $app->response() ;
    $response->setStatus(200) ;
    $response->headers->set('Content-Type', 'application/json');

    $cours = $app->request->get('cours');
    $groupe = $app->request->get('groupe');

    $sql = "SELECT dureeTotale FROM enseignements where codeEnseignement=$cours";
            $stmt = DB::getModule($sql) ;
            $tuple = DB::getNext($stmt) ;
            $test = json_encode($tuple, JSON_PRETTY_PRINT) ;
            $test2 = json_decode($test, true);
            $dureeTotale = $test2[0];

    $sql ="SELECT dureeSeance FROM enseignements WHERE codeEnseignement=$cours";
            $stmt = DB::getModule($sql) ;
            $tuple = DB::getNext($stmt) ;
            $test = json_encode($tuple, JSON_PRETTY_PRINT) ;
            $test2 = json_decode($test, true);
            $duree = $test2[0];

            $sql ="SELECT s.dureeSeance FROM seances s JOIN seances_groupes sg ON s.codeSeance = sg.codeSeance WHERE codeEnseignement=$cours And codeRessource=$groupe And s.deleted =0 ";
            $stmt = DB::getModule($sql) ;
            $tuple = DB::getAll($stmt) ;
            $min=0;
            $heur=0;
            foreach ($tuple as $row) 
            {
                $m = substr($row["dureeSeance"], -2);
                $h = substr($row["dureeSeance"], 0,-2); 
                $min+=$m;
                $heur+=$h;
            }

            $m= substr($duree, -2);
            $h= substr($duree, 0,-2);
            $min+=$m;
            $heur+=$h;
            $hsup=$min/60;
            $hsup=floor($hsup);
            $heur+=$hsup;
            $mrest=$min%60;
            if ($mrest==0) 
            {
                $mrest="00";
            }
            $SumDuree=$heur.=$mrest;

    if($dureeTotale<$SumDuree)
    {
        echo json_encode("Depassement de Total enseignement : ".$SumDuree."/".$dureeTotale, JSON_PRETTY_PRINT) ;
    }
}) ;

$app->get('/verrificationDispoSalle', function() {
    $app = Slim\Slim::getInstance() ;
    $response = $app->response() ;
    $response->setStatus(200) ;
    $response->headers->set('Content-Type', 'application/json');

    $seance = $app->request->get('seance');

    $sql = "SELECT codeRessource FROM seances_salles WHERE codeSeance=$seance";
            $stmt = DB::getModule($sql) ;
            $tuple = DB::getNext($stmt) ;
            $test = json_encode($tuple, JSON_PRETTY_PRINT) ;
            $test2 = json_decode($test, true);
            $codeSalle = $test2[0]; 
            

    $sql = "SELECT dateSeance,heureSeance,dureeSeance FROM seances WHERE codeSeance=$seance";
            $stmt = DB::getModule($sql) ;
            $tuple = DB::getNext($stmt) ;
            $test = json_encode($tuple, JSON_PRETTY_PRINT) ;
            $test2 = json_decode($test, true);
            $date = $test2[0];
            $heure = $test2[1];
            $duree = $test2[2]; 
            

    $sql ="SELECT COUNT(*)as num  FROM seances s JOIN seances_salles ss ON s.codeSeance = ss.codeSeance WHERE codeRessource=$codeSalle AND s.dateSeance='$date' AND s.heureSeance BETWEEN $heure AND $heure+$duree AND s.deleted =0 ";
            $stmt = DB::getModule($sql) ;
            $tuple = DB::getNext($stmt) ;
            $test = json_encode($tuple, JSON_PRETTY_PRINT) ;
            $test2 = json_decode($test, true);
            $numero = $test2[0];
    /*if($numero>1)
    {DB::begin_transaction();
    $sql = "SELECT *  FROM seances s JOIN seances_salles ss ON s.codeSeance = ss.codeSeance WHERE codeRessource=$codeSalle AND s.dateSeance='$date' AND s.heureSeance BETWEEN $heure AND $heure+$duree AND s.deleted =0 " ;
    $stmt = DB::execute($sql, array()) ;
    $items = DB::getAll($stmt) ;
    DB::transaction_commit() ;
    echo json_encode($items, JSON_PRETTY_PRINT) ;}  */     

}) ;

$app->get('/verrificationDispoSalle2', function() {
    $app = Slim\Slim::getInstance() ;
    $response = $app->response() ;
    $response->setStatus(200) ;
    $response->headers->set('Content-Type', 'application/json');

    $cours = $app->request->get('cours');
    $date = $app->request->get('date');
    $heure = $app->request->get('heure');
    $salle = $app->request->get('salle');

    $sql ="SELECT dureeSeance FROM enseignements WHERE codeEnseignement=$cours";
            $stmt = DB::getModule($sql) ;
            $tuple = DB::getNext($stmt) ;
            $test = json_encode($tuple, JSON_PRETTY_PRINT) ;
            $test2 = json_decode($test, true);
            $duree = $test2[0];

            $heureFin=additionHeure($heure,$duree);
            $heureDeb=soustractionHeure($heure,$duree);

    $sql ="SELECT COUNT(*)as num  FROM seances s JOIN seances_salles ss ON s.codeSeance = ss.codeSeance WHERE codeRessource=$salle AND s.dateSeance='$date' AND s.heureSeance BETWEEN $heureDeb AND $heureFin AND s.deleted =0 ";
            $stmt = DB::getModule($sql) ;
            $tuple = DB::getNext($stmt) ;
            $test = json_encode($tuple, JSON_PRETTY_PRINT) ;
            $test2 = json_decode($test, true);
            $numero = $test2[0];
    if($numero>=1)
    {
    echo json_encode("Salle deja utilise", JSON_PRETTY_PRINT) ;
    } 

}) ;

$app->get('/verrificationDispoProf', function() {
    $app = Slim\Slim::getInstance() ;
    $response = $app->response() ;
    $response->setStatus(200) ;
    $response->headers->set('Content-Type', 'application/json');

    $seance = $app->request->get('seance');

    $sql = "SELECT codeRessource FROM seances_profs WHERE codeSeance=$seance";
            $stmt = DB::getModule($sql) ;
            $tuple = DB::getNext($stmt) ;
            $test = json_encode($tuple, JSON_PRETTY_PRINT) ;
            $test2 = json_decode($test, true);
            $codeProf = $test2[0]; 
            

    $sql = "SELECT dateSeance,heureSeance,dureeSeance FROM seances WHERE codeSeance=$seance";
            $stmt = DB::getModule($sql) ;
            $tuple = DB::getNext($stmt) ;
            $test = json_encode($tuple, JSON_PRETTY_PRINT) ;
            $test2 = json_decode($test, true);
            $date = $test2[0];
            $heure = $test2[1];
            $duree = $test2[2]; 
            

    $sql ="SELECT COUNT(*)as num  FROM seances s JOIN seances_profs ss ON s.codeSeance = ss.codeSeance WHERE codeRessource=$codeProf AND s.dateSeance='$date' AND s.heureSeance BETWEEN $heure AND $heure+$duree AND s.deleted =0 ";
            $stmt = DB::getModule($sql) ;
            $tuple = DB::getNext($stmt) ;
            $test = json_encode($tuple, JSON_PRETTY_PRINT) ;
            $test2 = json_decode($test, true);
            $numero = $test2[0];
    /*if($numero>1)
    {DB::begin_transaction();
    $sql = "SELECT *  FROM seances s JOIN seances_profs ss ON s.codeSeance = ss.codeSeance WHERE codeRessource=$codeProf AND s.dateSeance='$date' AND s.heureSeance BETWEEN $heure AND $heure+$duree AND s.deleted =0 " ;
    $stmt = DB::execute($sql, array()) ;
    $items = DB::getAll($stmt) ;
    DB::transaction_commit() ;
    echo json_encode($items, JSON_PRETTY_PRINT) ;}  */     

}) ;

$app->get('/verrificationDispoProf2', function() {
    $app = Slim\Slim::getInstance() ;
    $response = $app->response() ;
    $response->setStatus(200) ;
    $response->headers->set('Content-Type', 'application/json');

    $cours = $app->request->get('cours');
    $date = $app->request->get('date');
    $heure = $app->request->get('heure');
    $prof = $app->request->get('prof');

     $sql ="SELECT dureeSeance FROM enseignements WHERE codeEnseignement=$cours";
            $stmt = DB::getModule($sql) ;
            $tuple = DB::getNext($stmt) ;
            $test = json_encode($tuple, JSON_PRETTY_PRINT) ;
            $test2 = json_decode($test, true);
            $duree = $test2[0];   

    $heureFin=additionHeure($heure,$duree);
    $heureDeb=soustractionHeure($heure,$duree);       

    $sql ="SELECT COUNT(*)as num  FROM seances s JOIN seances_profs ss ON s.codeSeance = ss.codeSeance WHERE codeRessource=$prof AND s.dateSeance='$date' AND s.heureSeance BETWEEN $heureDeb AND $heureFin AND s.deleted =0 ";
            $stmt = DB::getModule($sql) ;
            $tuple = DB::getNext($stmt) ;
            $test = json_encode($tuple, JSON_PRETTY_PRINT) ;
            $test2 = json_decode($test, true);
            $numero = $test2[0];
     if($numero>=1)
    {
    echo json_encode("Professeur deja en cours", JSON_PRETTY_PRINT) ;
    }      

}) ;

$app->get('/verrificationDispoGroupe', function() {
    $app = Slim\Slim::getInstance() ;
    $response = $app->response() ;
    $response->setStatus(200) ;
    $response->headers->set('Content-Type', 'application/json');

    $seance = $app->request->get('seance');

    $sql = "SELECT codeRessource FROM seances_groupes WHERE codeSeance=$seance";
            $stmt = DB::getModule($sql) ;
            $tuple = DB::getNext($stmt) ;
            $test = json_encode($tuple, JSON_PRETTY_PRINT) ;
            $test2 = json_decode($test, true);
            $codeGroupe = $test2[0]; 
            

    $sql = "SELECT dateSeance,heureSeance,dureeSeance FROM seances WHERE codeSeance=$seance";
            $stmt = DB::getModule($sql) ;
            $tuple = DB::getNext($stmt) ;
            $test = json_encode($tuple, JSON_PRETTY_PRINT) ;
            $test2 = json_decode($test, true);
            $date = $test2[0];
            $heure = $test2[1];
            $duree = $test2[2]; 
            

    $sql ="SELECT COUNT(*)as num  FROM seances s JOIN seances_groupes ss ON s.codeSeance = ss.codeSeance WHERE codeRessource=$codeGroupe AND s.dateSeance='$date' AND s.heureSeance BETWEEN $heure AND $heure+$duree AND s.deleted =0 ";
            $stmt = DB::getModule($sql) ;
            $tuple = DB::getNext($stmt) ;
            $test = json_encode($tuple, JSON_PRETTY_PRINT) ;
            $test2 = json_decode($test, true);
            $numero = $test2[0];
    /*if($numero>1)
    {DB::begin_transaction();
    $sql = "SELECT * FROM seances s JOIN seances_groupes ss ON s.codeSeance = ss.codeSeance WHERE codeRessource=$codeGroupe AND s.dateSeance='$date' AND s.heureSeance BETWEEN $heure AND $heure+$duree AND s.deleted =0 " ;
    $stmt = DB::execute($sql, array()) ;
    $items = DB::getAll($stmt) ;
    DB::transaction_commit() ;
    echo json_encode($items, JSON_PRETTY_PRINT) ;}  */     

}) ;

$app->get('/verrificationDispoGroupe2', function() {
    $app = Slim\Slim::getInstance() ;
    $response = $app->response() ;
    $response->setStatus(200) ;
    $response->headers->set('Content-Type', 'application/json');

    $cours = $app->request->get('cours');
    $date = $app->request->get('date');
    $heure = $app->request->get('heure');
    $groupe = $app->request->get('groupe');

     $sql ="SELECT dureeSeance FROM enseignements WHERE codeEnseignement=$cours";
            $stmt = DB::getModule($sql) ;
            $tuple = DB::getNext($stmt) ;
            $test = json_encode($tuple, JSON_PRETTY_PRINT) ;
            $test2 = json_decode($test, true);
            $duree = $test2[0];

    $heureFin=additionHeure($heure,$duree);
    $heureDeb=soustractionHeure($heure,$duree); 

    $sql ="SELECT COUNT(*)as num  FROM seances s JOIN seances_groupes ss ON s.codeSeance = ss.codeSeance WHERE codeRessource=$groupe AND s.dateSeance='$date' AND s.heureSeance BETWEEN $heureDeb AND $heureFin AND s.deleted =0 ";
            $stmt = DB::getModule($sql) ;
            $tuple = DB::getNext($stmt) ;
            $test = json_encode($tuple, JSON_PRETTY_PRINT) ;
            $test2 = json_decode($test, true);
            $numero = $test2[0];

         if($numero>=1)
    {
    echo json_encode("Groupe deja en cours", JSON_PRETTY_PRINT) ;
    }      
}) ;

$app->get('/verrificationEnseignementProf', function() {
    $app = Slim\Slim::getInstance() ;
    $response = $app->response() ;
    $response->setStatus(200) ;
    $response->headers->set('Content-Type', 'application/json');

    $seance = $app->request->get('seance');

    $sql = "SELECT codeEnseignement FROM seances WHERE codeSeance=$seance";
            $stmt = DB::getModule($sql) ;
            $tuple = DB::getNext($stmt) ;
            $test = json_encode($tuple, JSON_PRETTY_PRINT) ;
            $test2 = json_decode($test, true);
            $codeEnseignement = $test2[0];

    $sql = "SELECT codeRessource FROM seances_profs where codeSeance=$seance";
            $stmt = DB::getModule($sql) ;
            $tuple = DB::getNext($stmt) ;
            $test = json_encode($tuple, JSON_PRETTY_PRINT) ;
            $test2 = json_decode($test, true);
            $prof = $test2[0];

    $sql = "SELECT count(*) as num FROM enseignements_profs WHERE codeEnseignement=$codeEnseignement AND codeRessource=$prof";
            $stmt = DB::getModule($sql) ;
            $tuple = DB::getNext($stmt) ;
            $test = json_encode($tuple, JSON_PRETTY_PRINT) ;
            $test2 = json_decode($test, true);
            $num = $test2[0];
           
    /*if($num=0)
    {   echo $num;  }*/
}) ;

$app->get('/verrificationEnseignementProf2', function() {
    $app = Slim\Slim::getInstance() ;
    $response = $app->response() ;
    $response->setStatus(200) ;
    $response->headers->set('Content-Type', 'application/json');

    $cours = $app->request->get('cours');
    $prof = $app->request->get('prof');

    $sql = "SELECT count(*) as num FROM enseignements_profs WHERE codeEnseignement=$cours AND codeRessource=$prof AND deleted =0 ";
            $stmt = DB::getModule($sql) ;
            $tuple = DB::getNext($stmt) ;
            $test = json_encode($tuple, JSON_PRETTY_PRINT) ;
            $test2 = json_decode($test, true);
            $num = $test2[0];
           
    if($num < 1)
    {
    echo json_encode("Ce professeur n'enseigne pas ce cours", JSON_PRETTY_PRINT) ;
    }
});

$app->get('/verrificationEnseignementGroupe', function() {
    $app = Slim\Slim::getInstance() ;
    $response = $app->response() ;
    $response->setStatus(200) ;
    $response->headers->set('Content-Type', 'application/json');

    $seance = $app->request->get('seance');

    $sql = "SELECT codeEnseignement FROM seances WHERE codeSeance=$seance";
            $stmt = DB::getModule($sql) ;
            $tuple = DB::getNext($stmt) ;
            $test = json_encode($tuple, JSON_PRETTY_PRINT) ;
            $test2 = json_decode($test, true);
            $codeEnseignement = $test2[0];

    $sql = "SELECT codeRessource FROM seances_groupes where codeSeance=$seance";
            $stmt = DB::getModule($sql) ;
            $tuple = DB::getNext($stmt) ;
            $test = json_encode($tuple, JSON_PRETTY_PRINT) ;
            $test2 = json_decode($test, true);
            $groupe = $test2[0];

    $sql = "SELECT count(*) as num FROM enseignements_groupes WHERE codeEnseignement=$codeEnseignement AND codeRessource=$groupe";
            $stmt = DB::getModule($sql) ;
            $tuple = DB::getNext($stmt) ;
            $test = json_encode($tuple, JSON_PRETTY_PRINT) ;
            $test2 = json_decode($test, true);
            $num = $test2[0];
           
    /*if($num=0)
    {   echo $num;  }*/
}) ;

$app->get('/verrificationEnseignementGroupe2', function() {
    $app = Slim\Slim::getInstance() ;
    $response = $app->response() ;
    $response->setStatus(200) ;
    $response->headers->set('Content-Type', 'application/json');

    $cours = $app->request->get('cours');
    $groupe = $app->request->get('groupe');

    $sql = "SELECT count(*) as num FROM enseignements_groupes WHERE codeEnseignement=$cours AND codeRessource=$groupe AND deleted=0";
            $stmt = DB::getModule($sql) ;
            $tuple = DB::getNext($stmt) ;
            $test = json_encode($tuple, JSON_PRETTY_PRINT) ;
            $test2 = json_decode($test, true);
            $num = $test2[0];
           
    if($num < 1)
    {
    echo json_encode("Ce groupe ne participe pas ce cours", JSON_PRETTY_PRINT) ;
    }
}) ;

$app->put('/suppression',function() {
        $app = Slim\Slim::getInstance() ; 
            $module = $app->request->get('module');
            $sql = "select codeMatiere FROM matieres where nom='$module'";
            $stmt = DB::getModule($sql) ;
            $module = DB::getNext($stmt) ;
            $test = json_encode($module, JSON_PRETTY_PRINT) ;
            $test2 = json_decode($test, true);
            $module = $test2[0];
                
            $code2 = $app->request->get('id');

            $sqlcommand = "UPDATE seances set deleted=1 ,dateModif=now() where codeSeance=$code2" ;
            $stmt = DB::createCour($sqlcommand) ;
            $sqlcommand = "UPDATE seances_salles set deleted=1 , dateModif=now() where codeSeance=$code2";
            $stmt = DB::createCour($sqlcommand) ;
            $sqlcommand = "UPDATE seances_profs set deleted=1 , dateModif=now() where codeSeance=$code2";
            $stmt = DB::createCour($sqlcommand) ;

            $sqlcommand = "UPDATE seances_groupes set deleted=1 , dateModif=now() where codeSeance=$code2";

            $stmt = DB::createCour($sqlcommand) ;

            $response = $app->response() ;
            $response->setStatus(200) ;
            $response->headers->set('Content-Type', 'application/json');


    });

$app->post('/creation',function() {
    $app = Slim\Slim::getInstance() ; 
 
            // lecture des params de post
            $module = $app->request->get('module');
            $sql = "select codeMatiere FROM matieres where nom='$module'";
            $stmt = DB::getModule($sql) ;
            $module = DB::getNext($stmt) ;
            $test = json_encode($module, JSON_PRETTY_PRINT) ;
            $test2 = json_decode($test, true);
            $module = $test2[0];

            $par_prof = $app->request->get('prof');
            $par_cours = $app->request->get('cours');

            $sql = "select dureeSeance FROM enseignements where codeEnseignement=$par_cours";
            $stmt = DB::getModule($sql) ;
            $module = DB::getNext($stmt) ;
            $test = json_encode($module, JSON_PRETTY_PRINT) ;
            $test2 = json_decode($test, true);
            $duree = $test2[0];

            $par_salle = $app->request->get('salle');
            $par_groupe = $app->request->get('groupe');
            $par_date = $app->request->get('date');
            $par_heure = $app->request->get('heure');

        $sqlcommand = "INSERT INTO seances(dateSeance,heureSeance,dureeSeance,codeEnseignement,dateModif,codeProprietaire,commentaire,dateCreation)
        values ('$par_date',$par_heure,$duree,$par_cours,now(),3001,'',now())";
        $stmt = DB::createCour($sqlcommand) ;

        $sql = "select max(codeSeance) FROM seances ";
            $stmt = DB::getModule($sql) ;
            $module = DB::getNext($stmt) ;
            $test = json_encode($module, JSON_PRETTY_PRINT) ;
            $test2 = json_decode($test, true);
            $max = $test2[0];

        $sqlcommand2 = "INSERT INTO seances_profs(codeSeance,codeRessource, dateModif, deleted,codeProprietaire,dateCreation)
        values ($max,$par_prof,now(),0,3001,now())";

    $stmt = DB::createRessource($sqlcommand2) ;
      

        $sqlcommand3 = "INSERT INTO seances_salles(codeSeance,codeRessource, dateModif, deleted,codeProprietaire,dateCreation)
        values ($max,$par_salle,now(),0,3001,now())";

    $stmt = DB::createRessource($sqlcommand3) ;

        $sqlcommand4 = "INSERT INTO seances_groupes(codeSeance,codeRessource, dateModif, deleted,codeProprietaire,dateCreation)
        values ($max,$par_groupe,now(),0,3001,now())";

    $stmt = DB::createRessource($sqlcommand4) ;
 
    });

$app->put('/modif',function() {
    $app = Slim\Slim::getInstance() ; 
 
            // lecture des params de post
            $module = $app->request->get('module');
            $codeSeance = $app->request->get('id');

            $sql = "select codeMatiere FROM matieres where nom='$module'";
            $stmt = DB::getModule($sql) ;
            $module = DB::getNext($stmt) ;
            $test = json_encode($module, JSON_PRETTY_PRINT) ;
            $test2 = json_decode($test, true);
            $module = $test2[0];

            $par_prof = $app->request->get('prof');

            $par_cours = $app->request->get('cours');

            $sql = "select dureeSeance FROM enseignements where codeEnseignement=$par_cours";
            $stmt = DB::getModule($sql) ;
            $module = DB::getNext($stmt) ;
            $test = json_encode($module, JSON_PRETTY_PRINT) ;
            $test2 = json_decode($test, true);
            $duree = $test2[0];

            $par_salle = $app->request->get('salle');

            $par_groupe = $app->request->get('groupe');

            $par_date = $app->request->get('date');

            $par_heure = $app->request->get('heure');

        $sqlcommand = "UPDATE seances set dateSeance='$par_date', heureSeance=$par_heure ,dureeSeance=$duree,codeEnseignement=$par_cours,dateModif=now()
         where codeSeance=$codeSeance" ;

        $stmt = DB::createCour($sqlcommand) ;


        $sqlcommand2 = "UPDATE seances_profs set codeRessource=$par_prof, dateModif=now()
          where codeSeance=$codeSeance ";

    $stmt = DB::createRessource($sqlcommand2) ;


        $sqlcommand3 = "UPDATE seances_salles set codeRessource=$par_salle, dateModif=now()
         where codeSeance=$codeSeance";

    $stmt = DB::createRessource($sqlcommand3) ;

        $sqlcommand4 = "UPDATE seances_groupes set codeRessource='$par_groupe', dateModif=now()
         where codeSeance=$codeSeance";

    $stmt = DB::createRessource($sqlcommand4) ;

    });



$app->get('/seances/search', function() {
    $app = Slim\Slim::getInstance() ;

    $sqlcommand = <<<'EOT'
        select 
            s.codeSeance AS codeSeance, 
            s.dateSeance AS dateSeance, 
            s.heureSeance AS heureSeance, 
            s.dureeSeance AS dureeSeance, 
            s.diffusable AS diffusable, 
            s.dateModif AS dateModif, 
            e.alias AS Enseignement, 
            e.nom AS ens_id,
            (select group_concat(l.nom separator ',')   
                from (seances_salles sl  
                    left join ressources_salles l on((sl.codeRessource = l.codeSalle)))  
                where ((s.codeSeance = sl.codeSeance) and (sl.deleted = 0))) AS Salle, 
            (select group_concat(g.alias separator ',')   
                from (seances_groupes sg  
                    left join ressources_groupes g on((sg.codeRessource = g.codeGroupe)))  
                    where ((s.codeSeance = sg.codeSeance) and (sg.deleted = 0))) AS Groupe, 
            (select group_concat(p.alias separator ',')   
                from (seances_profs sp  
                    left join ressources_profs p on((sp.codeRessource = p.codeProf)))  
                    where ((s.codeSeance = sp.codeSeance) and (sp.deleted = 0))) AS Prof,  
            e.couleurFond AS couleurFond, 
            e.couleurPolice AS couleurPolice  
        from seances s  
            left join enseignements e  on s.codeEnseignement = e.codeEnseignement 
            left join matieres m  on m.codeMatiere = e.codeMatiere  
            left join seances_groupes sg on s.codeSeance = sg.codeSeance
            left join ressources_groupes g on sg.codeRessource = g.codeGroupe
            left join seances_salles sl on s.codeSeance = sl.codeSeance
            left join ressources_salles l on sl.codeRessource = l.codeSalle
            left join seances_profs sp on s.codeSeance = sp.codeSeance
            left join ressources_profs p on sp.codeRessource = p.codeProf
        where (s.deleted = 0)  
            and ((sg.deleted = 0) or (sg.deleted is null))
            and ((sl.deleted = 0) or (sl.deleted is null))
            and ((sp.deleted = 0) or (sp.deleted is null))
            and ((g.deleted = 0) or (g.deleted is null))
            and ((l.deleted = 0) or (l.deleted is null))
            and ((p.deleted = 0) or (p.deleted is null))
            /* and s.codeProprietaire between 3000 and 3099 */
            and (:module = '' or m.nom  = :module) 
            and (:groupe = '' or g.codeGroupe = :groupe)
            and (:prof = '' or p.codeProf = :prof)
            and (:salle = '' or l.codeSalle = :salle)
        group by  
            s.codeSeance, 
            s.dateSeance, 
            s.heureSeance, 
            s.dureeSeance, 
            s.diffusable, 
            s.dateModif, 
            e.couleurPolice, 
            e.couleurFond  
        order by
            s.dateSeance, s.heureSeance
EOT;

    $response = $app->response() ;
    $response->setStatus(200) ;
    $response->headers->set('Content-Type', 'application/json');

    $par_groupe = $app->request()->get('groupe') ;
    $par_module = $app->request()->get('module') ;
    $par_prof = $app->request()->get('prof') ;
    $par_salle = $app->request()->get('salle') ;

    if ($par_groupe == NULL) $par_groupe = "" ;
    if ($par_prof == NULL) $par_prof = "" ;
    if ($par_module == NULL) $par_module = "" ;
    if ($par_salle == NULL) $par_salle = "" ;

    $arguments = array(
        ':module' => "$par_module",
        ':groupe' => "$par_groupe",
        ':prof' => "$par_prof",
        ':salle' => "$par_salle"
    ) ;

    $items = get_seances($sqlcommand, $arguments) ;

    $json = array() ;
    $cumul_horaire = array() ;
    foreach ($items as $row) {
        $temp = array() ;
        $start_date = DateTime::createFromFormat("Y-m-d", $row["dateSeance"]) ;
        $h = $row["heureSeance"] / 100 ;
        $m = ($row["heureSeance"] % 100) ;
        $start_date->setTime($h,$m) ;

        $end_date = DateTime::createFromFormat("Y-m-d", $row["dateSeance"]) ;
        $m = $m + $row["dureeSeance"] %100 ;
        $h += $row["dureeSeance"] / 100 ;
        $h += $m / 60 ;
        $m = $m % 60 ;
        $end_date->setTime($h, $m) ;

        $duration = $end_date->diff($start_date)->h ;

        $ens = $row["ens_id"] ;
        if (array_key_exists($ens, $cumul_horaire)) {
            $cumul_horaire[$ens] += $duration ;
        } else {
            $cumul_horaire[$ens] = $duration ;
        }

        $temp["start"] = $start_date->format(DateTime::ATOM) ;
        $temp["end"] = $end_date->format(DateTime::ATOM) ;
        $temp["title"] = $row["Enseignement"]."\t".$row["Groupe"]."\t".$row["Prof"]."\t".$row["Salle"]."\t".$row["codeSeance"]  ;
        $r = ($row["couleurFond"]) & 0xFF ;
        $g = ($row["couleurFond"] >> 8) & 0xFF ;
        $b = ($row["couleurFond"] >> 16) & 0xFF ;
        $r = floor(($r + 256)/2) ;
        $g = floor(($g + 256)/2) ;
        $b = floor(($b + 256)/2) ;
        $temp["ens_id"] = $ens ;
        $temp["cumul_h"] = $cumul_horaire[$ens] ;
        $temp["color"] = "rgb($r,$g,$b)" ;
        $temp['textColor'] = 'black' ;
//        $temp["description"] = $row["Enseignement"]."\n" ;
//        $temp["description"] .= $row["Groupe"]."\n" ;
//        $temp["description"] .= $row["Prof"]."\n" ;
//        $temp["description"] .= $row["Salle"]."\n" ;
        $temp["description"] = $ens.", Cumul:  ".$cumul_horaire[$ens]." H\n" ;
        $json[] = (object) $temp ;
    }
    echo json_encode($json, JSON_PRETTY_PRINT) ;
}) ;

$app->log->setEnabled(true);
$app->log->setLevel(Slim\Log::DEBUG) ;

$app->run() ;

?>
