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

$app->get('/creation',function() {
    $app = Slim\Slim::getInstance() ; 
 
            // lecture des params de post
            $par_module = $app->request->get('module');
            $sql = "select codeMatiere FROM matieres WHERE nom='$par_module'";
            $stmt = DB::getModule($sql) ;
            $module = DB::getNext($stmt) ;
            $test = json_encode($module, JSON_PRETTY_PRINT) ;
            $test2 = json_decode($test, true);
            $module = $test2[0];

            $par_date = $app->request->get('date');
            $par_heure = $app->request->get('heure');

       // $sqlcommand = "INSERT INTO seances(dateSeance,heureSeance,dureeSeance,codeEnseignement,commentaire,diffusable)
        //    values ('2017-12-03',1000,200,3201,'',1)";

        $sqlcommand = "INSERT INTO seances(dateSeance,heureSeance,dureeSeance,codeEnseignement,commentaire,diffusable)
        values ('$par_date',$par_heure,200,$module,'',1)";

             /*$sqlcommand = "INSERT INTO seances(heureSeance,dureeSeance,commentaire,diffusable)
             values (1000,200,'',1)";*/
     echo $sqlcommand;
    $stmt = DB::createCour($sqlcommand) ;
      
 
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
            and (:module = '%%' or e.nom like :module) 
            and (:groupe = '' or g.nom = :groupe)
            and (:prof = '' or p.codeProf = :prof)
            and (:salle = '' or l.nom = :salle)
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
        ':module' => "%$par_module%",
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
        $temp["title"] = $row["Enseignement"]."\t".$row["Groupe"]."\t".$row["Prof"]."\t".$row["Salle"]  ;
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
