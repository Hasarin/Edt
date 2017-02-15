<?php

require_once 'DB.php' ;

require_once 'Slim/Slim.php' ;

Slim\Slim::registerAutoloader() ;

$app = new Slim\Slim(array(
//    'log.enable' => true,
//    'log.level' => Slim_Log::DEBUG,
//    'log.writer' => new Log_FileWriter(),
    'debug' => true
    ));
$app->log->setEnabled(true) ;
//$loggerSettings = $app->getLog() ; 
//print_r($loggerSettings) ;
//echo "***".(get_resource_type($loggerSettings->resource))."***" ;

function get_seances($sqlcommand, $arguments) {
    DB::begin_transaction() ;
    $stmt = DB::execute($sqlcommand, $arguments) ;
    $items = DB::getAll($stmt) ;
    DB::transaction_commit() ;
    return $items ;
}

$app->get('/hello/:name', function($name) {
    echo "<h1>Hello, $name</h1>\n" ;
}) ;

$app->get('/modules', function() {
    $app = Slim\Slim::getInstance() ;
    $response = $app->response() ;
    $response->setStatus(200) ;
    $response->headers->set('Content-Type', 'application/json');

    DB::begin_transaction() ;
    $stmt = DB::execute("SELECT * FROM matieres WHERE codeProprietaire = 3001 AND deleted = 0 ; ", array()) ;
    $item = DB::getAll($stmt) ;
    DB::transaction_commit() ;
    echo json_encode($item, JSON_PRETTY_PRINT) ;
}) ;

$app->get('/modules/:module', function($module) {
    $app = Slim\Slim::getInstance() ;
    $response = $app->response() ;
    $response->setStatus(200) ;
    $response->headers->set('Content-Type', 'application/json');

    $sqlcommand = <<<'EOT'
        select 
            s.codeSeance AS codeSeance, 
            s.dateSeance AS dateSeance, 
            s.heureSeance AS heureSeance, 
            s.dureeSeance AS dureeSeance, 
            s.diffusable AS diffusable, 
            s.dateModif AS dateModif, 
            e.alias AS Enseignement, 
            (select group_concat(l.nom separator ',')   
                from (seances_salles sl  
                    left join ressources_salles l  
                    on((sl.codeRessource = l.codeSalle)))  
                where ((s.codeSeance = sl.codeSeance)  
                    and (sl.deleted = 0))) AS Salle, 
            (select group_concat(g.alias separator ',')   
                from (seances_groupes sg  
                    left join ressources_groupes g  
                    on((sg.codeRessource = g.codeGroupe)))  
                where ((s.codeSeance = sg.codeSeance)  
                    and (sg.deleted = 0))) AS Groupe, 
            (select group_concat(p.alias separator ',')   
                from (seances_profs sp  
                    left join ressources_profs p  
                        on((sp.codeRessource = p.codeProf)))  
                    where ((s.codeSeance = sp.codeSeance)  
                        and (sp.deleted = 0))) AS Prof, 
            (select group_concat(g.nom separator ',')   
                from (seances_groupes sg  
                    left join ressources_groupes g  
                        on((sg.codeRessource = g.codeGroupe)))  
                    where ((s.codeSeance = sg.codeSeance)  
                        and (sg.deleted = 0))) AS GroupeId, 
            e.couleurFond AS couleurFond, 
            e.couleurPolice AS couleurPolice  
        from (seances s  
            left join enseignements e  
            on((s.codeEnseignement = e.codeEnseignement)))  
        where (s.deleted = 0)  
             and e.nom like ? 
        group by  
            s.codeSeance, 
            s.dateSeance, 
            s.heureSeance, 
            s.dureeSeance, 
            s.diffusable, 
            s.dateModif, 
            e.couleurPolice, 
            e.couleurFond ; 
EOT;
    $arguments = array("%$module%") ;
    $items = get_seances($sqlcommand, $arguments) ;

    $json = array() ;
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

        $temp["start"] = $start_date->format(DateTime::ATOM) ;
        $temp["end"] = $end_date->format(DateTime::ATOM) ;
        $temp["title"] = $row["Enseignement"]."\t".$row["Groupe"]."\t".$row["Prof"]."\t".$row["Salle"]  ;
        $r = ($row["couleurFond"]) & 0xFF ;
        $g = ($row["couleurFond"] >> 8) & 0xFF ;
        $b = ($row["couleurFond"] >> 16) & 0xFF ;
        $r = floor(($r + 256)/2) ;
        $g = floor(($g + 256)/2) ;
        $b = floor(($b + 256)/2) ;
        $temp["color"] = "rgb($r,$g,$b)" ;
        $temp['textColor'] = 'black' ;
        $temp["description"] = $row["Enseignement"]."\n".$row["Groupe"]."\n".$row["Prof"]."\n".$row["Salle"] ;
        $json[] = (object) $temp ;
    }
    echo json_encode($json, JSON_PRETTY_PRINT) ;
}) ;


$app->get('/groupes/:groupe', function($groupe) {
    $app = Slim\Slim::getInstance() ;
    $response = $app->response() ;
    $response->setStatus(200) ;
    $response->headers->set('Content-Type', 'application/json');

    $sqlcommand = <<<'EOT'
        select 
            s.codeSeance AS codeSeance, 
            s.dateSeance AS dateSeance, 
            s.heureSeance AS heureSeance, 
            s.dureeSeance AS dureeSeance, 
            s.diffusable AS diffusable, 
            s.dateModif AS dateModif, 
            e.alias AS Enseignement, 
            (select group_concat(l.nom separator ',')   
                from (seances_salles sl  
                    left join ressources_salles l  
                    on((sl.codeRessource = l.codeSalle)))  
                where ((s.codeSeance = sl.codeSeance)  
                    and (sl.deleted = 0))) AS Salle, 
            (select group_concat(g.alias separator ',')   
                from (seances_groupes sg  
                    left join ressources_groupes g  
                    on((sg.codeRessource = g.codeGroupe)))  
                where ((s.codeSeance = sg.codeSeance)  
                    and (sg.deleted = 0))) AS Groupe, 
            (select group_concat(p.alias separator ',')   
                from (seances_profs sp  
                    left join ressources_profs p  
                        on((sp.codeRessource = p.codeProf)))  
                    where ((s.codeSeance = sp.codeSeance)  
                        and (sp.deleted = 0))) AS Prof, 
            (select group_concat(g.nom separator ',')   
                from (seances_groupes sg  
                    left join ressources_groupes g  
                        on((sg.codeRessource = g.codeGroupe)))  
                    where ((s.codeSeance = sg.codeSeance)  
                        and (sg.deleted = 0))) AS GroupeId, 
            e.couleurFond AS couleurFond, 
            e.couleurPolice AS couleurPolice  
        from seances s  
            left join seances_groupes sg  on (s.codeSeance = sg.codeSeance) 
            left join ressources_groupes g  on (sg.codeRessource = g.codeGroupe) 
            left join enseignements e on (s.codeEnseignement = e.codeEnseignement)  
        where (s.deleted = 0)  
             and g.nom = ? 
        group by  
            s.codeSeance, 
            s.dateSeance, 
            s.heureSeance, 
            s.dureeSeance, 
            s.diffusable, 
            s.dateModif, 
            e.couleurPolice, 
            e.couleurFond ; ;
EOT;
    $arguments = array("$groupe") ;
    $items = get_seances($sqlcommand, $arguments) ;

    $json = array() ;
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

        $temp["start"] = $start_date->format(DateTime::ATOM) ;
        $temp["end"] = $end_date->format(DateTime::ATOM) ;
        $temp["title"] = $row["Enseignement"]."\t".$row["Groupe"]."\t".$row["Prof"]."\t".$row["Salle"]  ;
        $r = ($row["couleurFond"]) & 0xFF ;
        $g = ($row["couleurFond"] >> 8) & 0xFF ;
        $b = ($row["couleurFond"] >> 16) & 0xFF ;
        $r = floor(($r + 256)/2) ;
        $g = floor(($g + 256)/2) ;
        $b = floor(($b + 256)/2) ;
        $temp["color"] = "rgb($r,$g,$b)" ;
        $temp['textColor'] = 'black' ;
        $temp["description"] = $row["Enseignement"]."\n".$row["Groupe"]."\n".$row["Prof"]."\n".$row["Salle"] ;
        $json[] = (object) $temp ;
    }
    echo json_encode($json, JSON_PRETTY_PRINT) ;
}) ;


$app->get('/tester', function() {
    $app = Slim\Slim::getInstance() ;
    $response = $app->response() ;
    $response->setStatus(200) ;
    $response->headers->set('Content-Type', 'application/json');
    $sqlcommand = <<<'EOT'
        select 
            s.codeSeance AS codeSeance, 
            s.dateSeance AS dateSeance, 
            s.heureSeance AS heureSeance, 
            s.dureeSeance AS dureeSeance, 
            s.diffusable AS diffusable, 
            s.dateModif AS dateModif, 
            e.alias AS Enseignement, 
            (select group_concat(l.nom separator ',')   
                from (seances_salles sl  
                    left join ressources_salles l  
                    on((sl.codeRessource = l.codeSalle)))  
                where ((s.codeSeance = sl.codeSeance)  
                    and (sl.deleted = 0))) AS Salle, 
            (select group_concat(g.alias separator ',')   
                from (seances_groupes sg  
                    left join ressources_groupes g  
                    on((sg.codeRessource = g.codeGroupe)))  
                where ((s.codeSeance = sg.codeSeance)  
                    and (sg.deleted = 0))) AS Groupe, 
            (select group_concat(p.alias separator ',')   
                from (seances_profs sp  
                    left join ressources_profs p  
                        on((sp.codeRessource = p.codeProf)))  
                    where ((s.codeSeance = sp.codeSeance)  
                        and (sp.deleted = 0))) AS Prof, 
            (select group_concat(g.nom separator ',')   
                from (seances_groupes sg  
                    left join ressources_groupes g  
                        on((sg.codeRessource = g.codeGroupe)))  
                    where ((s.codeSeance = sg.codeSeance)  
                        and (sg.deleted = 0))) AS GroupeId, 
            e.couleurFond AS couleurFond, 
            e.couleurPolice AS couleurPolice  
        from seances s  
            left join seances_groupes sg  on (s.codeSeance = sg.codeSeance) 
            left join ressources_groupes g  on (sg.codeRessource = g.codeGroupe) 
            left join enseignements e on (s.codeEnseignement = e.codeEnseignement)  
        where (s.deleted = 0)  
             and g.nom = 'I-N3P1-L' 
        group by  
            s.codeSeance, 
            s.dateSeance, 
            s.heureSeance, 
            s.dureeSeance, 
            s.diffusable, 
            s.dateModif, 
            e.couleurPolice, 
            e.couleurFond ; ;
EOT;
    echo $sqlcommand ;
}) ;

$app->get('/phpinfo', function() {
    phpinfo() ;
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
        "order by  g.alias ;" ;
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
        "order by  p.alias ;" ;
    $stmt = DB::execute($sql, array()) ;
    $items = DB::getAll($stmt) ;
    DB::transaction_commit() ;
    echo json_encode($items, JSON_PRETTY_PRINT) ;
}) ;

$app->log->setEnabled(true);
$app->log->setLevel(Slim\Log::DEBUG) ;

$app->run() ;

?>
