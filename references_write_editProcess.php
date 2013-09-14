<?
/*
Gibbon, Flexible & Open School System
Copyright (C) 2010, Ross Parker

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

include "../../functions.php" ;
include "../../config.php" ;

//New PDO DB connection
try {
    $connection2=new PDO("mysql:host=$databaseServer;dbname=$databaseName", $databaseUsername, $databasePassword);
	$connection2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$connection2->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
}
catch(PDOException $e) {
    echo $e->getMessage();
}

session_start() ;

//Module includes
include "./moduleFunctions.php" ;

//Set timezone from session variable
date_default_timezone_set($_SESSION[$guid]["timezone"]);

$higherEducationReferenceComponentID=$_GET["higherEducationReferenceComponentID"] ;
$URL=$_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_POST["address"]) . "/references_write_edit.php&higherEducationReferenceComponentID=$higherEducationReferenceComponentID" ;

if (isActionAccessible($guid, $connection2, "/modules/Higher Education/references_write_edit.php")==FALSE) {
	//Fail 0
	$URL = $URL . "&updateReturn=fail0" ;
	header("Location: {$URL}");
}
else {
	if ($higherEducationReferenceComponentID=="") {
		//Fail1
		$URL = $URL . "&updateReturn=fail1" ;
		header("Location: {$URL}");
	}
	else {
		try {
			$data=array("higherEducationReferenceComponentID"=>$higherEducationReferenceComponentID, "gibbonPersonID"=>$_SESSION[$guid]["gibbonPersonID"]);  
			$sql="SELECT * FROM higherEducationReferenceComponent WHERE higherEducationReferenceComponentID=:higherEducationReferenceComponentID AND gibbonPersonID=:gibbonPersonID" ;
			$result=$connection2->prepare($sql);
			$result->execute($data);
		}
		catch(PDOException $e) { 
			//Fail2
			$URL = $URL . "&updateReturn=fail2" ;
			header("Location: {$URL}");
			break ;
		}

		if ($result->rowCount()!=1) {
			//Fail 2
			$URL = $URL . "&updateReturn=fail2" ;
			header("Location: {$URL}");
		}
		else {
			//Validate Inputs
			$status=$_POST["status"] ;
			$body=$_POST["body"] ;
			
			if ($status=="" OR $body=="") {
				//Fail 3
				$URL = $URL . "&updateReturn=fail3" ;
				header("Location: {$URL}");
			}
			else {
				//Write to database
				try {
					$data=array("status"=>$status, "body"=>$body, "higherEducationReferenceComponentID"=>$higherEducationReferenceComponentID);  
					$sql="UPDATE higherEducationReferenceComponent SET status=:status, body=:body WHERE higherEducationReferenceComponentID=:higherEducationReferenceComponentID" ;
					$result=$connection2->prepare($sql);
					$result->execute($data); 
				}
				catch(PDOException $e) { 
					//Fail 2
					$URL = $URL . "&updateReturn=fail2" ;
					header("Location: {$URL}");
					break ;
				}
				
				//Success 0
				$URL = $URL . "&updateReturn=success0" ;
				header("Location: {$URL}");
			}
		}
	}
}
?>