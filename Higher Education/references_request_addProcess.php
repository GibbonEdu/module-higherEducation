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
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

include "../../functions.php" ;
include "../../config.php" ;

//New PDO DB connection
try {
  	$connection2=new PDO("mysql:host=$databaseServer;dbname=$databaseName;charset=utf8", $databaseUsername, $databasePassword);
	$connection2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$connection2->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
}
catch(PDOException $e) {
  echo $e->getMessage();
}

@session_start() ;

//Module includes
include "./moduleFunctions.php" ;

//Set timezone from session variable
date_default_timezone_set($_SESSION[$guid]["timezone"]);

$URL=$_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_POST["address"]) . "/references_request_add.php" ;

if (isActionAccessible($guid, $connection2, "/modules/Higher Education/references_request_add.php")==FALSE) {
	//Fail 0
	$URL=$URL . "&addReturn=fail0" ;
	header("Location: {$URL}");
}
else {
	//Check for student enrolment
	if (studentEnrolment($_SESSION[$guid]["gibbonPersonID"], $connection2)==FALSE) {
		//Fail 0
		$URL=$URL . "&addReturn=fail0" ;
		header("Location: {$URL}");
	}
	else {
		//Validate Inputs
		$gibbonPersonID=$_SESSION[$guid]["gibbonPersonID"] ;
		$gibbonSchoolYearID=$_SESSION[$guid]["gibbonSchoolYearID"] ;
		$type=$_POST["type"] ;
		$gibbonPersonIDReferee=NULL ;
		if (isset($_POST["gibbonPersonIDReferee"])) {
			$gibbonPersonIDReferee=$_POST["gibbonPersonIDReferee"] ;
		}
		$status="Pending" ;
		$statusNotes="" ;
		$notes=$_POST["notes"] ;
		$timestamp=date("Y-m-d H:i:s") ;
		
		if ($type=="" OR ($type=="US References" AND $gibbonPersonIDReferee=="")) {
			//Fail 3
			$URL=$URL . "&addReturn=fail3" ;
			header("Location: {$URL}");
		}
		else {
			//Write to database
			try {
				$data=array("gibbonPersonID"=>$gibbonPersonID, "gibbonSchoolYearID"=>$gibbonSchoolYearID, "type"=>$type, "status"=>$status, "statusNotes"=>$statusNotes, "notes"=>$notes, "timestamp"=>$timestamp); 
				$sql="INSERT INTO higherEducationReference SET gibbonPersonID=:gibbonPersonID, gibbonSchoolYearID=:gibbonSchoolYearID, type=:type, status=:status, statusNotes=:statusNotes, notes=:notes, timestamp=:timestamp" ;
				$result=$connection2->prepare($sql);
				$result->execute($data);
			}
			catch(PDOException $e) { 
				//Fail 2
				$URL=$URL . "&addReturn=fail2" ;
				header("Location: {$URL}");
				exit() ;
			}
			
			$higherEducationReferenceID=$connection2->lastInsertID() ;
			
			//Set referees based on type of reference
			$partialFail=false ;
			//Get new unit ID
			$higherEducationReferenceIDNew=$connection2->lastInsertID() ;
			if ($type=="Composite Reference") {
				//Get subject teachers
				try {
					$dataClass=array("gibbonSchoolYearID"=>$gibbonSchoolYearID, "gibbonPersonID"=>$gibbonPersonID); 
					$sqlClass="SELECT gibbonCourseClass.gibbonCourseClassID, gibbonCourseClass.nameShort AS class, gibbonCourse.nameShort AS course FROM gibbonCourse JOIN gibbonCourseClass ON (gibbonCourse.gibbonCourseID=gibbonCourseClass.gibbonCourseID) JOIN gibbonCourseClassPerson ON (gibbonCourseClassPerson.gibbonCourseClassID=gibbonCourseClass.gibbonCourseClassID) WHERE gibbonSchoolYearID=:gibbonSchoolYearID AND gibbonPersonID=:gibbonPersonID AND NOT role LIKE '%left' ORDER BY course, class" ;
					$resultClass=$connection2->prepare($sqlClass);
					$resultClass->execute($dataClass);
				}
				catch(PDOException $e) { 
					$partialFail=true ;
				}
				while ($rowClass=$resultClass->fetch()) {
					try {
						$dataTeacher=array("gibbonCourseClassID"=>$rowClass["gibbonCourseClassID"]); 
						$sqlTeacher="SELECT gibbonPersonID FROM gibbonCourseClassPerson WHERE gibbonCourseClassID=:gibbonCourseClassID AND role='Teacher'" ;
						$resultTeacher=$connection2->prepare($sqlTeacher);
						$resultTeacher->execute($dataTeacher);
					}
					catch(PDOException $e) { 
						$partialFail=true ;
					}	
					while ($rowTeacher=$resultTeacher->fetch()) {
						try {
							$dataInsert=array("higherEducationReferenceID"=>$higherEducationReferenceIDNew, "gibbonPersonID"=>$rowTeacher["gibbonPersonID"], "title"=>$rowClass["course"] . "." . $rowClass["class"]); 
							$sqlInsert="INSERT INTO higherEducationReferenceComponent SET higherEducationReferenceID=:higherEducationReferenceID, gibbonPersonID=:gibbonPersonID, status='Pending', type='Academic', title=:title" ;
							$resultInsert=$connection2->prepare($sqlInsert);
							$resultInsert->execute($dataInsert);
						}
						catch(PDOException $e) { 
							$partialFail=true ;
						}	
					}	
				}
				
				//Get tutors
				try {
					$dataForm=array("gibbonSchoolYearID"=>$gibbonSchoolYearID, "gibbonPersonID"=>$gibbonPersonID); 
					$sqlForm="SELECT gibbonRollGroup.* FROM gibbonRollGroup JOIN gibbonStudentEnrolment ON (gibbonStudentEnrolment.gibbonRollGroupID=gibbonRollGroup.gibbonRollGroupID) WHERE gibbonStudentEnrolment.gibbonSchoolYearID=:gibbonSchoolYearID AND gibbonPersonID=:gibbonPersonID" ;
					$resultForm=$connection2->prepare($sqlForm);
					$resultForm->execute($dataForm);
				}
				catch(PDOException $e) { 
					$partialFail=true ;
				}
				if ($resultForm->rowCount()==1) {
					$rowForm=$resultForm->fetch() ;
					if ($rowForm["gibbonPersonIDTutor"]!="") {
						try {
							$dataInsert=array("higherEducationReferenceID"=>$higherEducationReferenceIDNew, "gibbonPersonID"=>$rowForm["gibbonPersonIDTutor"], "title"=>$rowForm["nameShort"]); 
							$sqlInsert="INSERT INTO higherEducationReferenceComponent SET higherEducationReferenceID=:higherEducationReferenceID, gibbonPersonID=:gibbonPersonID, status='Pending', type='Pastoral', title=:title" ;
							$resultInsert=$connection2->prepare($sqlInsert);
							$resultInsert->execute($dataInsert);
						}
						catch(PDOException $e) { 
							$partialFail=true ;
						}	
					}
					if ($rowForm["gibbonPersonIDTutor2"]!="") {
						try {
							$dataInsert=array("higherEducationReferenceID"=>$higherEducationReferenceIDNew, "gibbonPersonID"=>$rowForm["gibbonPersonIDTutor2"], "title"=>$rowForm["nameShort"]); 
							$sqlInsert="INSERT INTO higherEducationReferenceComponent SET higherEducationReferenceID=:higherEducationReferenceID, gibbonPersonID=:gibbonPersonID, status='Pending', type='Pastoral', title=:title" ;
							$resultInsert=$connection2->prepare($sqlInsert);
							$resultInsert->execute($dataInsert);
						}
						catch(PDOException $e) { 
							$partialFail=true ;
						}	
					}
					if ($rowForm["gibbonPersonIDTutor3"]!="") {
						try {
							$dataInsert=array("higherEducationReferenceID"=>$higherEducationReferenceIDNew, "gibbonPersonID"=>$rowForm["gibbonPersonIDTutor3"], "title"=>$rowForm["nameShort"]); 
							$sqlInsert="INSERT INTO higherEducationReferenceComponent SET higherEducationReferenceID=:higherEducationReferenceID, gibbonPersonID=:gibbonPersonID, status='Pending', type='Pastoral', title=:title" ;
							$resultInsert=$connection2->prepare($sqlInsert);
							$resultInsert->execute($dataInsert);
						}
						catch(PDOException $e) { 
							$partialFail=true ;
						}	
					}
				}
			}
			if ($type=="US Reference") {
				if ($gibbonPersonIDReferee!="") {
					try {
						$dataInsert=array("higherEducationReferenceID"=>$higherEducationReferenceIDNew, "gibbonPersonID"=>$gibbonPersonIDReferee); 
						$sqlInsert="INSERT INTO higherEducationReferenceComponent SET higherEducationReferenceID=:higherEducationReferenceID, gibbonPersonID=:gibbonPersonID, status='Pending', type='General', title=''" ;
						$resultInsert=$connection2->prepare($sqlInsert);
						$resultInsert->execute($dataInsert);
					}
					catch(PDOException $e) { 
						$partialFail=true ;
					}	
				}
			}
			
			//Attempt to notify coordinators
			try {
				$dataNotify=array();  
				$sqlNotify="SELECT gibbonPerson.gibbonPersonID FROM higherEducationStaff JOIN gibbonPerson ON (higherEducationStaff.gibbonPersonID=gibbonPerson.gibbonPersonID) WHERE status='Full' AND role='Coordinator'" ; 
				$resultNotify=$connection2->prepare($sqlNotify);
				$resultNotify->execute($dataNotify); 
			}
			catch(PDOException $e) { }
			while ($rowNotify=$resultNotify->fetch()) {
				$notificationText=sprintf(__($guid, 'Someone has created a new Higher Education reference request.')) ;
				setNotification($connection2, $guid, $rowNotify["gibbonPersonID"], $notificationText, "Higher Education", "/index.php?q=/modules/Higher Education/references_manage.php") ;
			}
			
			if ($partialFail==true) {
				//Fail 5
				$URL=$URL . "&addReturn=fail5" ;
				header("Location: {$URL}");
			}
			else {
				//Success 0
				$URL=$URL . "&addReturn=success0" ;
				header("Location: {$URL}");
			}
		}
	}
}
?>