<?php

class DB {
    static $connection ;
    public static function init() {
        try {

            static::$connection = new PDO('mysql:host=localhost;dbname=vt_2016;charset=utf8', 'root', '');
            static::$connection->exec("SET CHARACTER SET LATIN1");
            static::$connection->exec("SET CLIENT ENCODING 'LATIN1'");
            static::$connection->exec("SET NAMES LATIN1");
            static::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION) ;
        } catch (Exception $ex) {
            echo "<h4>Erreur PDO</h4>" ;
            echo $ex->getFile().":".$ex->getLine()."<br>" ;
            echo $ex->getCode()."<br>" ;
            echo $ex->getMessage()."<br>\n" ;
        }
    }
    public static function lastInsertId($seq) {
        return static::$connection->lastInsertId($seq) ;
    }
    public static function begin_transaction() {
        return static::$connection->beginTransaction() ;
    }
    public static function transaction_commit() {
        return static::$connection->commit() ;
    }
    public static function transaction_rollback() {
        return static::$connection->rollback() ;
    }
    public static function query($request, $parms) {
        $stmt = static::$connection->prepare($request) ;
        $stmt->execute($parms) ;
        return $stmt ;
    }
    public static function execute($request, $parms) {
        $stmt = static::$connection->prepare($request) ;
        foreach($parms as $key => $value) {
            $stmt->bindValue($key, $value) ;
        }
        $stmt->execute() ;
        return $stmt ;
    }
    public static function queryScrollable($request, $parms) {
        $stmt = static::$connection->prepare($request, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL)) ;
        $stmt->execute($parms) ;
        return $stmt ;
    }
    public static function getNext($stmt) {
        $row = $stmt->fetch(PDO::FETCH_BOTH) ;
        return $row ;
    }
    public static function getAll($stmt) {
        $rows = $stmt->fetchAll(PDO::FETCH_BOTH) ;
        return $rows ;
    }
    public static function getFirst($stmt) {
        $row = $stmt->fetch(PDO::FETCH_BOTH, PDO::FETCH_ORI_FIRST) ;
        return $row ;
    }

  public function createCour($sqlcommand,$par_module, $par_date, $par_heure) {
 
 
        // Vérifiez d'abord si l'utilisateur existe déjà dans db
          

            // requete d'insertion
          //  $stmt = static::$connection->prepare("INSERT INTO seances(dateSeance,heureSeance,dureeSeance,
        //codeEnseignement,commentaire,diffusable) values ('"+$par_date+"',"+$par_heure+",200,"+$par_module+",'',1");
          $stmt = static::$connection->prepare($sqlcommand);

            $stmt->execute();
 
            
            
    }

    public static function error($exc, $file, $line) {
        if (defined(DEBUG)) {
            throw ($exc) ;
        } else {
            echo "<h4>Erreur PDO</h4>" ;
            echo $exc->getFile().":".$exc->getLine()."<br>" ;
            echo $exc->getCode()."<br>" ;
            echo $exc->getMessage()."<br>\n" ;
        }
    }
}
DB::init() ;
?>
