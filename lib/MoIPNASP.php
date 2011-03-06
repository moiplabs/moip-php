<?php
/**
 * MoIP's NASP management class
 *
 * @author Alê Borba <ale.alvesborba@gmail.com>
 * @version 0.0.1
 * @license <a href="http://www.opensource.org/licenses/bsd-license.php">BSD License</a>
 * @package MoIP
 * 
 * @todo Implements get contents from the defined file
 * @todo Implements support for database storage
 */
class MoIPNASP{
    /**
     * The file resource
     *
     * @var resource
     * @access private
     */
    private $file;
    /**
     * The content to be write
     *
     * @var string
     * @access private
     */
    private $file_content;
    /**
     * @staticvar Store the PDO Object
     * @access private
     */
    private static $conn;

    /**
     * Method setFile()
     *
     * Define the file's path and name
     *
     * @param string $path The file's path
     * @param string $filename The file's name
     * @access public
     */
    public function setFile($path = null, $filename = null) {

        //Open a resource and set the variable
        if (isset ($path) && isset ($filename)) {
            $this->file = fopen($path.$filename, 'a');
        }
        else {
            throw new Exception("The required params not be defined");
        }
        
    }

    /**
     * Method setContent()
     *
     * Define the content to be writen
     *
     * @param array $content The data array sended by MOIP NASP
     * @access public
     */
    public function setContent(array $content) {
        
        //Get the data array and implode in a string glued with ;
        if (isset ($this->file)) {
            $this->file_content = implode(';', $content);
        }
        else {
            throw new Exception("The method setFile had to be defined first");
        }
    }

    /**
     * Method write()
     *
     * Write the informations in the archive
     *
     * @access public
     */
    public function write() {

        //Write the information
        fwrite($this->file, "{$this->file_content}\n");
        //Close the file
        fclose($this->file);
    }

    /**
     * Method getContent()
     *
     * Method to return NASP data stored in a file
     *
     * @param string $path The path's file
     * @param string $filename The name's file
     * @access public
     */
    public function getContent($path = null, $filename = null) {

        //Get the file's data em send to array
        if (isset ($path) && isset ($filename)) {
            $transactions = file($path.$filename);
            foreach ($transactions as $transaction) {
                $infos[] = explode(';', $transaction);
            }

            return $infos;
        }
        else {
            throw new Exception("The required params not be defined");
        }
    }

    /**
     * Method setDatabase()
     * 
     * Define the database for store the NASP data
     * 
     * @param string $hostname The hostname or IP of the server's database
     * @param string $database The database's name
     * @param string $user The database'user
     * @param string $pass The database's password for the user specify
     * @access public
     */
    public function setDatabase($hostname = null,$database = null,$user = null,$pass = null) {
        //Makes the connection
        self::$conn = DBConnection::open($hostname,$database,$user,$pass);
    }

    /**
     * Method getConnection()
     *
     * Returns the active connection
     *
     * @return Active PDO Object
     * @access private
     */
    private function getConnection(){
        return self::$conn;
    }

    public function insertData(array $data) {
        if ($con = $this->getConnection()) {
            $sql = "INSERT INTO tbl_moip_nasp (id_transacao, valor, status_pagamento, cod_moip, forma_pagamento, tipo_pagamento, email_consumidor) VALUES ('{$data['id_transacao']}','{$data['valor']}','{$data['status_pagamento']}','{$data['cod_moip']}','{$data['forma_pagamento']}','{$data['tipo_pagamento']}','{$data['email_consumidor']}')";
            $result = $con->query($sql);
            if (!$result) {
                $erro = $con->errorCode();
                print_r($erro);
            }
        }
    }
}
?>