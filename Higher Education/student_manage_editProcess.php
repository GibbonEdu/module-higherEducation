<?php
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


@session_start() ;

//Set timezone from session variable
date_default_timezone_set($_SESSION[$guid]["timezone"]);

$higherEducationStudentID=$_GET["higherEducationStudentID"] ;
$URL=$_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_POST["address"]) . "/student_manage_edit.php&higherEducationStudentID=$higherEducationStudentID" ;

if (isActionAccessible($guid, $connection2, "/modules/Higher Education/student_manage_edit.php")==FALSE) {

	//Fail 0
	$URL=$URL . "&updateReturn=fail0" ;
	header("Location: {$URL}");
}
else {
	//Proceed!
	//Check if school year specified
	if ($higherEducationStudentID=="") {
		//Fail1
		$URL=$URL . "&updateReturn=fail1" ;
		header("Location: {$URL}");
	}
	else {
		try {
			$data=array("higherEducationStudentID"=>$higherEducationStudentID);  
			$sql="SELECT * FROM higherEducationStudent WHERE higherEducationStudentID=:higherEducationStudentID" ;
			$result=$connection2->prepare($sql);
			$result->execute($data);
		}
		catch(PDOException $e) { 
			//Fail2
			$URL=$URL . "&updateReturn=fail2" ;
			header("Location: {$URL}");
			exit() ;
		}
		
		if ($result->rowCount()!=1) {
			//Fail 2
			$URL=$URL . "&updateReturn=fail2" ;
			header("Location: {$URL}");
		}
		else {
			//Validate Inputs
			if ($_POST["gibbonPersonIDAdvisor"]!="") {
				$gibbonPersonIDAdvisor=$_POST["gibbonPersonIDAdvisor"] ;
			}
			else {
				$gibbonPersonIDAdvisor=NULL ;
			}

			//Write to database
			try {
				$data=array("gibbonPersonIDAdvisor"=>$gibbonPersonIDAdvisor, "higherEducationStudentID"=>$higherEducationStudentID);  
				$sql="UPDATE higherEducationStudent SET gibbonPersonIDAdvisor=:gibbonPersonIDAdvisor WHERE higherEducationStudentID=:higherEducationStudentID" ;
				$result=$connection2->prepare($sql);
				$result->execute($data); 
			}
			catch(PDOException $e) { 
				//Fail 2
				$URL=$URL . "&updateReturn=fail2" ;
				header("Location: {$URL}");
				exit() ;
			}
			
			//Success 0
			$URL=$URL . "&updateReturn=success0" ;
			header("Location: {$URL}");
		}
	}
}
?>