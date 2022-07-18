<?php
namespace Slurp\Utils;

class Crypt
{
    const SALTNOTHASHED = "SelDeLApplicationSlurpPourSecuriserLEncryptionDeDonnées789456123";
    const CRYPTKEYNOTHASHED = "CléDeCryptageDeLApplicationSlurpPourSecuriserLEncryptionDeDonnées321456987";
    private $iv_length;
    private $crypt_iv;
    private $options;
    private $cyphering;
    private $salt;
    private $cryption_key;

    private static $_instance;

//  Constructeur De la Crypt (en private avec le Pattern Singleton)
    private function __construct()
    {
        //Pas d'options
        $this->options=0;
        //Mise en place de l'utilisation d'openssl
        $this->cyphering="bf-cbc";
        $this->crypt_iv=11111111;
        $this->iv_length = openssl_cipher_iv_length( $this->cyphering);

        //Mise en place d'un salage
        $this->salt = openssl_digest(self::SALTNOTHASHED, "MD5", TRUE);

        //Création de la clé
        $this->cryption_key = openssl_digest(self::CRYPTKEYNOTHASHED, "MD5", TRUE);
    }

//  Donne l'instance de la Crypt (Mise en place d'un Pattern Singleton)
    public static function getInstance(): Crypt
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new Crypt();
        }
        return self::$_instance;
    }

    public function encrypt($str)
    {
        if (!is_string($str)) {
            return false;
        }
        // Encryption avec Salage
        return base64_encode(openssl_encrypt(($str . $this->salt), $this->cyphering,
            $this->cryption_key, $this->options,$this->crypt_iv));
    }

    public function decrypt($encrypt)
    {
        if (!is_string($encrypt)) {
            return false;
        }
        // Descryption avec Desalage
        return str_replace($this->salt,'',openssl_decrypt(base64_decode($encrypt), $this->cyphering,
            $this->cryption_key, $this->options,$this->crypt_iv));
    }

    public function test($test){
        echo $test."<br>";
        $test2=self::encrypt($test);
        echo $test2."<br>";
        $test3=self::decrypt($test2);
        echo $test3."<br>";
    }
}
?>