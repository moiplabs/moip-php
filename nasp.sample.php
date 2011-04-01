<?php
/**
 * Template to use with service MoIP NASP
 *
 * @author Alê Borba <ale.alvesborba@gmail.com>
 * @version 0.0.1
 * @license <a href="http://www.opensource.org/licenses/bsd-license.php">BSD License</a>
 */

//Include the autoload function
include_once 'autoload.inc.php';

//Uses the MoIPNASP class to record the informations in a file

//Instance new object MoIPNASP()
$nasp = new MoIPNASP();

//Set the path and filename
$nasp->setFile("your_path","your_filename");

//Set the array of contents
$nasp->setContent($_POST);

//Write the informations
$nasp->write();

?>
