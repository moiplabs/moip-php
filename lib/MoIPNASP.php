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
}
?>
