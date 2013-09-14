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

$gibbonSchoolYearID=$_GET["gibbonSchoolYearID"];
$higherEducationReferenceID=$_GET["higherEducationReferenceID"] ;
$URL=$_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_POST["address"]) . "/references_manage_edit.php&higherEducationReferenceID=$higherEducationReferenceID&gibbonSchoolYearID=$gibbonSchoolYearID" ;

if (isActionAccessible($guid, $connection2, "/modules/Higher Education/references_manage_edit.php")==FALSE) {
	//Fail 0
	$URL = $URL . "&updateReturn=fail0" ;
	header("Location: {$URL}");
}
else {
	$role=staffHigherEducationRole($_SESSION[$guid]["gibbonPersonID"], $connection2) ;
	if ($role!="Coordinator") {
		//Fail 0
		$URL = $URL . "&addReturn=fail0" ;
		header("Location: {$URL}");
	}
	else {	
		//Proceed!
		if ($higherEducationReferenceID=="" OR $gibbonSchoolYearID=="") {
			//Fail1
			$URL = $URL . "&updateReturn=fail1" ;
			header("Location: {$URL}");
		}
		else {
			try {
				$data=array("higherEducationReferenceID"=>$higherEducationReferenceID);  
				$sql="SELECT higherEducationReference.*, preferredName, surname FROM higherEducationReference JOIN gibbonPerson ON (higherEducationReference.gibbonPersonID=gibbonPerson.gibbonPersonID) WHERE higherEducationReferenceID=:higherEducationReferenceID" ;
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
				$row=$result->fetch() ;
				
				//Validate Inputs
				$status=$_POST["status"] ;
				$statusNotes=$_POST["statusNotes"] ;
				$alertsSent=$_POST["alertsSent"] ;
				if ($alertsSent!="Y") {
					$alertsSent=="N" ;
				}
				$alertsSend="N" ;
				if ($alertsSent=="Y") {
					$alertsSend="Y" ;
				}
				
				if ($status=="") {
					//Fail 3
					$URL = $URL . "&updateReturn=fail3" ;
					header("Location: {$URL}");
				}
				else {
					//Sent alerts
					$partialFail=false ;
					if ($status=="In Progress" AND $alertsSent=="N") {
						$alertsSend="Y" ;
						
						try {
							$dataEmail=array("higherEducationReferenceID"=>$higherEducationReferenceID);  
							$sqlEmail="SELECT preferredName, email FROM higherEducationReferenceComponent JOIN gibbonPerson ON (higherEducationReferenceComponent.gibbonPersonID=gibbonPerson.gibbonPersonID) WHERE higherEducationReferenceID=:higherEducationReferenceID" ;
							$resultEmail=$connection2->prepare($sqlEmail);
							$resultEmail->execute($dataEmail);
						}
						catch(PDOException $e) { 
							$partialFail=true ;
						}
						while ($rowEmail=$resultEmail->fetch()) {
							$to = $rowEmail["email"];
							$subject = $_SESSION[$guid]["organisationNameShort"] . " Reference Contribution Notification";
							$body = "Dear " . $rowEmail["preferredName"] . ",<br/><br/>" ;
							$body = $body . "Your input in creating a higher education reference for " . formatName("", $row["preferredName"], $row["surname"], "Student", false) . " is requested. Please log in to Gibbon and go to Other > Higher Education > Write References. Your assistance is most appreciated. Regards,<br/><br/>" ;
							$body = $body . $_SESSION[$guid]["preferredName"] . " " . $_SESSION[$guid]["surname"] ;
							$headers = "From: " . $_SESSION[$guid]["email"] . "\r\n";
							$headers .= "MIME-Version: 1.0\r\n";
							$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n" ;
				
							if (mail($to, $subject, $body, $headers)==false) {
								$partialFail=true ;
							}
						}
					}
					
					//Write to database
					try {
						$data=array("status"=>$status, "statusNotes"=>$statusNotes, "alertsSent"=>$alertsSend, "higherEducationReferenceID"=>$higherEducationReferenceID);  
						$sql="UPDATE higherEducationReference SET status=:status, statusNotes=:statusNotes, alertsSent=:alertsSent WHERE higherEducationReferenceID=:higherEducationReferenceID" ;
						$result=$connection2->prepare($sql);
						$result->execute($data); 
					}
					catch(PDOException $e) { 
						//Fail 2
						$URL = $URL . "&updateReturn=fail2" ;
						header("Location: {$URL}");
						break ;
					}
					
					if ($partialFail==true) {
						//Fail 5
						$URL = $URL . "&updateReturn=fail5" ;
						header("Location: {$URL}");
					}
					else {
						//Success 0
						$URL = $URL . "&updateReturn=success0" ;
						header("Location: {$URL}");
					}
				}
			}
		}
	}
}
?>