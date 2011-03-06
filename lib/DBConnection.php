<?php
/**
 * Library to help PHP users of MoIP's API
 *
 * @author Alê Borba <ale.alvesborba@gmail.com>
 * @version 0.1
 * @package MoIP
 *
 * @todo Makes a config file for the connections and adds support for PostgreSQL
 */
/**
 * Management connections class
 *
 * Class to management all conections to database
 */
final class DBConnection{
    /**
     * Method construct
     *
     * Set private to not have instances for the class
     *
     */
    private function  __construct(){}

    /**
     * Method open()
     *
     * Get informations of database and instances the PDO object
     *
     * @param string $hostname The hostname or IP of the server's database
     * @param string $database The database's name
     * @param string $user The database'user
     * @param string $pass The database's password for the user specify
     * @return PDO Object
     * @static
     */
    public static function open($hostname,$database,$user,$pass){
        try {
            //Instance of PDO object
            $con = new PDO("mysql:host={$hostname};port=3306;dbname={$database}", "{$user}", "$pass");

            //Set the erro's attribute for PDO
            $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return $con;

        } catch (Exception $exc) {
            $erro =  $exc->getMessage();
            echo "<pre>";
            print_r($erro);
        }
    }
}

?>
