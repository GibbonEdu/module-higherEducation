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
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

session_start() ;

//Module includes
include "./modules/" . $_SESSION[$guid]["module"] . "/moduleFunctions.php" ;

if (isActionAccessible($guid, $connection2, "/modules/Higher Education/references_write.php")==FALSE) {
	//Acess denied
	print "<div class='error'>" ;
		print "You do not have access to this action." ;
	print "</div>" ;
}
else {
	//Proceed!
	print "<div class='trail'>" ;
	print "<div class='trailHead'><a href='" . $_SESSION[$guid]["absoluteURL"] . "'>Home</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/" . getModuleEntry($_GET["q"], $connection2, $guid) . "'>" . getModuleName($_GET["q"]) . "</a> > </div><div class='trailEnd'>Write References</div>" ;
	print "</div>" ;
	
	print "<p>" ;
	print "The table below shows all references for which your input is required in the current school year." ;
	print "<p>" ;
	
	//Set pagination variable
	$page=$_GET["page"] ;
	if ((!is_numeric($page)) OR $page<1) {
		$page=1 ;
	}
	
	try {
		$data=array("gibbonSchoolYearID"=>$_SESSION[$guid]["gibbonSchoolYearID"], "gibbonPersonID"=>$_SESSION[$guid]["gibbonPersonID"]); 
		$sql="SELECT higherEducationReference.timestamp, higherEducationReference.type AS typeReference, higherEducationReferenceComponent.*, surname, preferredName FROM higherEducationReferenceComponent JOIN higherEducationReference ON (higherEducationReferenceComponent.higherEducationReferenceID=higherEducationReference.higherEducationReferenceID) JOIN gibbonPerson ON (higherEducationReference.gibbonPersonID=gibbonPerson.gibbonPersonID) WHERE higherEducationReference.gibbonSchoolYearID=:gibbonSchoolYearID AND gibbonPerson.status='Full' AND higherEducationReference.status='In Progress' AND higherEducationReferenceComponent.gibbonPersonID=:gibbonPersonID ORDER BY higherEducationReferenceComponent.status, timestamp DESC" ; 
		$sqlPage= $sql . " LIMIT " . $_SESSION[$guid]["pagination"] . " OFFSET " . (($page-1)*$_SESSION[$guid]["pagination"]) ; 
		$result=$connection2->prepare($sql);
		$result->execute($data);
	}
	catch(PDOException $e) { 
		print "<div class='error'>" . $e->getMessage() . "</div>" ; 
	}
	
	if ($result->rowCount()<1) {
		print "<div class='success'>" ;
		print "There are no reference requests at current." ;
		print "</div>" ;
	}
	else {
		if ($result->rowCount()>$_SESSION[$guid]["pagination"]) {
			printPagination($guid, $result->rowCount(), $page, $_SESSION[$guid]["pagination"], "top") ;
		}
	
		print "<table cellspacing='0' style='width: 100%'>" ;
			print "<tr class='head'>" ;
				print "<th>" ;
					print "Name<br/>" ;
					print "<span style='font-size: 75%; font-style: italic'>Date</span>" ;
				print "</th>" ;
				print "<th colspan=2>" ;
					print "Your Contribution" ;
				print "</th>" ;
				print "<th>" ;
					print "Type" ;
				print "</th>" ;
				print "<th>" ;
					print "Perspective" ;
				print "</th>" ;
				print "<th>" ;
					print "Actions" ;
				print "</th>" ;
			print "</tr>" ;
			
			$count=0;
			$rowNum="odd" ;
			try {
				$resultPage=$connection2->prepare($sqlPage);
				$resultPage->execute($data);
			}
			catch(PDOException $e) { 
				print "<div class='error'>" . $e->getMessage() . "</div>" ; 
			}
			while ($row=$resultPage->fetch()) {
				if ($count%2==0) {
					$rowNum="even" ;
				}
				else {
					$rowNum="odd" ;
				}
				$count++ ;
				
				//Color rows based on start and end date
				if (!($row["dateStart"]=="" OR $row["dateStart"]<=date("Y-m-d")) AND ($row["dateEnd"]=="" OR $row["dateEnd"]>=date("Y-m-d"))) {
					$rowNum="error" ;
				}
				
				print "<tr class=$rowNum>" ;
					print "<td>" ;
						print formatName("", $row["preferredName"], $row["surname"], "Student", true) . "<br/>" ;
						print "<span style='font-size: 75%; font-style: italic'>" . dateConvertBack(substr($row["timestamp"],0,10)) . "</span>" ;
					print "</td>" ;
					print "<td style='width: 25px'>" ;
						if ($row["status"]=="Cancelled") {
							print "<img style='margin-right: 3px; float: left' title='Cancelled' src='./themes/" . $_SESSION[$guid]["gibbonThemeName"] . "/img/iconCross.png'/> " ;
						}
						else if ($row["status"]=="Complete") {
							print "<img style='margin-right: 3px; float: left' title='Complete' src='./themes/" . $_SESSION[$guid]["gibbonThemeName"] . "/img/iconTick.png'/> " ;
						}
						else {
							print "<img style='margin-right: 3px; float: left' title='In Progress' src='./themes/" . $_SESSION[$guid]["gibbonThemeName"] . "/img/iconTick_light.png'/> " ;
						}
					print "</td>" ;
					print "<td>" ;
						print "<b>" . $row["status"] . "</b>" ;
						if ($row["statusNotes"]!="") {
							print "<br/><span style='font-size: 75%; font-style: italic'>" . $row["statusNotes"] . "</span>" ;
						}
					print "</td>" ;
					print "<td>" ;
						print $row["typeReference"] ;
					print "</td>" ;
					print "<td>" ;
						print $row["type"] . "<br/>" ;
						if ($row["title"]!="") {
							print "<span style='font-size: 75%; font-style: italic'>" . $row["title"] . "</span>" ;
						}
					print "</td>" ;
					print "<td>" ;
						print "<a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . $_SESSION[$guid]["module"] . "/references_write_edit.php&higherEducationReferenceComponentID=" . $row["higherEducationReferenceComponentID"] . "'><img title='Edit' src='./themes/" . $_SESSION[$guid]["gibbonThemeName"] . "/img/config.png'/></a> " ;
					print "</td>" ;
				print "</tr>" ;
			}
		print "</table>" ;
		
		if ($result->rowCount()>$_SESSION[$guid]["pagination"]) {
			printPagination($guid, $result->rowCount(), $page, $_SESSION[$guid]["pagination"], "bottom") ;
		}
	}
}
?>