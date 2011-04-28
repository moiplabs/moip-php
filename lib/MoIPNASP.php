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

    /**
     * Method insertData()
     *
     * Inserts the NASP's data in the database
     *
     * @param array $data The data array from NASP
     * @access public
     */
    public function insertData(array $data) {
        //Verify the connection and insert the data
        if ($con = $this->getConnection()) {

            $sql = "INSERT INTO moip_nasp (
                    id_transacao,
                    valor,
                    status_pagamento,
                    cod_moip,
                    forma_pagamento,
                    tipo_pagamento,
                    email_consumidor)
                    VALUES (
                    '{$data['id_transacao']}',
                    '{$data['valor']}',
                    '{$data['status_pagamento']}',
                    '{$data['cod_moip']}',
                    '{$data['forma_pagamento']}',
                    '{$data['tipo_pagamento']}',
                    '{$data['email_consumidor']}');";

            $result = $con->query($sql);

            if (!$result) {
                $erro = $con->errorCode();
                print_r($erro);
            }
        }
        else{
            throw new Exception("The method setDatabase had to be defined first");
        }
    }

    /**
     * Method createTable()
     *
     * Create the NASP's table in the database
     *
     * @access public
     */
    public function createTable() {
        //Verify the connection and insert the data
        if ($con = $this->getConnection()) {

            $sql = "CREATE TABLE moip_nasp(
                    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    id_transacao VARCHAR(100) NOT NULL,
                    valor INTEGER NOT NULL,
                    `status_pagamento` INTEGER NOT NULL,
                    `cod_moip` INTEGER NOT NULL,
                    `forma_pagamento` INTEGER NOT NULL,
                    tipo_pagamento VARCHAR(100) NOT NULL,
                    email_consumidor VARCHAR(150) NOT NULL
                    );";

            $result = $con->query($sql);

            if (!$result) {
                $erro = $con->errorCode();
                print_r($erro);
            }
        }
        else{
            throw new Exception("The method setDatabase had to be defined first");
        }
    }

    /**
     * Method getData()
     *
     * Gets the NASP's data from the database
     *
     * @param string $transactionID The ID of the transaction
     * @param string $payment_status The status of the payment
     * @param string $cod_moip The MoIP's code of the transaction
     * @param string $payment_way The payment way
     * @param string $payment_type The type of the payment
     * @param string $payer_email The payer's email
     * @return array
     * @access public
     */
    public function getData($transactionID = null, $payment_status = null, $cod_moip = null, $payment_way = null, $payment_type = null, $payer_email = null) {
        //Make the SQL instruction
        $sql = "SELECT * FROM moip_nasp";

        $filtros = "";

        $whereAnd = " WHERE ";

        if(isset ($transactionID)){
            $filtros .= "{$whereAnd} id_transacao = '{$transactionID}'";
            $whereAnd = " AND ";
        }

        if(is_numeric($payment_status)){
            $filtros .= "{$whereAnd} status_pagamento = {$payment_status}";
            $whereAnd = " AND ";
        }

        if(is_numeric($cod_moip)){
            $filtros .= "{$whereAnd} cod_moip = {$cod_moip}";
            $whereAnd = " AND ";
        }

        if(is_numeric($payment_way)){
            $filtros .= "{$whereAnd} forma_pagamento = {$payment_way}";
            $whereAnd = " AND ";
        }

    	if(is_string($payment_type)){
            $filtros .= "{$whereAnd} tipo_pagamento = '{$payment_type}'";
            $whereAnd = " AND ";
        }

    	if( is_string($payer_email)){
            $filtros .= "{$whereAnd} email_consumidor = '{$payer_email}'";
            $whereAnd = " AND ";
        }

        $sql .= $filtros;

        $con = $this->getConnection();

        $query = $con->query($sql);

        foreach ($query as $result) {
            $results[] = $result;
        }

        return $results;
    }
}
?>