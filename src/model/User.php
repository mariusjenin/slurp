<?php

namespace Slurp\Model;

use PDO;
use PDOException;

class User
{
    protected $email;
    protected $pwd;
    protected $nom;
    protected $prenom;
    protected $sexe;
    protected $dateNaiss;
    protected $adresse;
    protected $codepostal;
    protected $ville;

    public function __construct($email,$pwd,$nom=null,$prenom=null,$dateNaiss=null,$adresse=null,$codepostal=null,$ville=null,$sexe=null)
    {
        $this->email=$email;
        $this->pwd=$pwd;
        $this->nom=$nom;
        $this->prenom=$prenom;
        $this->dateNaiss=$dateNaiss;
        $this->adresse=$adresse;
        $this->codepostal=$codepostal;
        $this->ville=$ville;
        $this->sexe=$sexe;
    }

    /**
     * @return mixed|string
     */
    public function getAdresse()
    {
        return $this->adresse;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return mixed|null
     */
    public function getSexe()
    {
        return $this->sexe;
    }

    /**
     * @return mixed|string
     */
    public function getVille()
    {
        return $this->ville;
    }

    /**
     * @param mixed|string $adresse
     */
    public function setAdresse($adresse): void
    {
        $this->adresse = $adresse;
    }

    /**
     * @param mixed|string $dateNaiss
     */
    public function setDateNaiss($dateNaiss): void
    {
        $this->dateNaiss = $dateNaiss;
    }

    /**
     * @param mixed|string $nom
     */
    public function setNom($nom): void
    {
        $this->nom = $nom;
    }

    /**
     * @param mixed|string $prenom
     */
    public function setPrenom($prenom): void
    {
        $this->prenom = $prenom;
    }

    /**
     * @param mixed|string $sexe
     */
    public function setSexe( $sexe): void
    {
        $this->sexe = $sexe;
    }

    /**
     * @param mixed|string $ville
     */
    public function setVille($ville): void
    {
        $this->ville = $ville;
    }

    /**
     * @param mixed $pwd
     */
    public function setPwd($pwd): void
    {
        $this->pwd = $pwd;
    }

    public function create(DataBase $db){
        $pwd_hash = password_hash($this->pwd,PASSWORD_DEFAULT,['cost' => 15]);
        $pdo=$db->getPdo();
        if($pdo==null){
            return false;
        }
        $pdo->beginTransaction();
        try {
            if($this->userExists($db)!=false){
                return false;
            }
            $stm = $pdo->prepare("Insert into User values(:email,:password,:nom,:prenom,:sexe,:datenaiss,:adresse,:codepostal,:ville);");

            $values = [
                ':email' => $this->email,
                ':password' => $pwd_hash,
                ':nom' => $this->nom,
                ':prenom' => $this->prenom,
                ':sexe' => $this->sexe,
                ':datenaiss' => $this->dateNaiss,
                ':adresse' => $this->adresse,
                ':codepostal' => $this->codepostal,
                ':ville' => $this->ville
            ];
            $stm->execute($values);

            $pdo->commit();
            return true;
        } catch(PDOException $e) {

            $pdo->rollBack();
            return false;
        }
    }

    public function modifProfil(DataBase $db,$oldEmail,$oldPassword){
        $pwd_hash = password_hash($this->pwd,PASSWORD_DEFAULT,['cost' => 15]);
        $pdo=$db->getPdo();
        if($pdo==null){
            return false;
        }
        $pdo->beginTransaction();
        try {

            //On vérifie que le nouveau email ne pose pas de problème avec un user déjà existant
            if($this->userExists($db)==false){
                return false;
            }
            $strStatement="UPDATE User SET ";
            $values=[];
            $arrayStatement=[];
            //Si on veut modifier le mot de passe alors on vérifie que l'ancien mot de passe fourni est bon
            if($oldPassword!=null){
                $userTestExist=new User($oldEmail,$oldPassword);
                if($userTestExist->userValid($db)==false){
                    return false;
                }
                $strStatement.="pwd_hash=:password ,";
                $values[':password']=$pwd_hash;
            }
            if($this->email!=null){
                $arrayStatement[]="email=:email";
                $values[':email']=$this->email;
            }
            if($this->nom!=null){
                $arrayStatement[]="nom=:nom";
                $values[':nom']=$this->nom;
            }
            if($this->prenom!=null){
                $arrayStatement[]="prenom=:prenom";
                $values[':prenom']=$this->prenom;
            }
            if($this->sexe!=null){
                $arrayStatement[]="sexe=:sexe";
                $values[':sexe']=$this->sexe;
            }
            if($this->dateNaiss!=null){
                $arrayStatement[]="dateNaiss=:datenaiss";
                $values[':datenaiss']=$this->dateNaiss;
            }
            if($this->adresse!=null){
                $arrayStatement[]="adresse=:adresse";
                $values[':adresse']=$this->adresse;
            }
            if($this->codepostal!=null){
                $arrayStatement[]="codepostal=:codepostal";
                $values[':codepostal']=$this->codepostal;
            }
            if($this->ville!=null){
                $arrayStatement[]="ville=:ville";
                $values[':ville']=$this->ville;
            }
            if(empty($strStatement)){
                return true;
            } else {
                $strStatement.=implode(' , ',$arrayStatement);
            }
            $strStatement.=" WHERE email=:oldemail;";
            $values[':oldemail']=$oldEmail;
            $stm = $pdo->prepare($strStatement);

            $stm->execute($values);

            $pdo->commit();
            return true;
        } catch(PDOException $e) {

            $pdo->rollBack();
            return false;
        }
    }

    public function userExists(DataBase $db)
    {
        if (!$db->isLinked()) {
            throw new PDOException();
        }
        $stm = $db->getPdo()->prepare("SELECT * FROM User where email=?;");
        $stm->bindValue(1, $this->email);
        $stm->execute();

        $user = $stm->fetch(PDO::FETCH_ASSOC);

        if (is_array($user)) {
            return $user;
        } else {
            return false;
        }
    }

    public function userValid(DataBase $db){
        try {
            $user=$this->userExists($db);

            if(isset($user["pwd_hash"]) && password_verify($this->pwd,$user["pwd_hash"])){
                return true;
            } else {
                return false;
            }
        } catch(PDOException $e) {
            return false;
        }
    }
}