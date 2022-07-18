<?php

namespace Slurp\Model;

use PDO;
use PDOException;

class DataBase
{
    protected $pdo;
    protected $linked;


    public function __construct()
    {
        try {
            // DBHOST DBNAME DBUSER & DBPWD sont défini dans le fichier config/config.php
            $this->pdo = new PDO("mysql:host=".DBHOST.";dbname=".DBNAME, DBUSER, DBPWD);
            // set the PDO error mode to exception
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $this->linked=true;
        } catch(PDOException $e) {
            $this->linked=false;
        }
    }

    /**
     * @return bool
     */
    public function isLinked(): bool
    {
        return $this->linked;
    }

    /**
     * @return PDO
     */
    public function getPdo()
    {
        return $this->pdo;
    }

}

?>