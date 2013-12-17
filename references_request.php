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

@session_start() ;

//Module includes
include "./modules/" . $_SESSION[$guid]["module"] . "/moduleFunctions.php" ;

if (isActionAccessible($guid, $connection2, "/modules/Higher Education/references_request.php")==FALSE) {
	//Acess denied
	print "<div class='error'>" ;
		print "You do not have access to this action." ;
	print "</div>" ;
}
else {
	
	//Proceed!
	print "<div class='trail'>" ;
	print "<div class='trailHead'><a href='" . $_SESSION[$guid]["absoluteURL"] . "'>Home</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/" . getModuleEntry($_GET["q"], $connection2, $guid) . "'>" . getModuleName($_GET["q"]) . "</a> > </div><div class='trailEnd'>Request References</div>" ;
	print "</div>" ;
	
	if (isset($_GET["deleteReturn"])) { $deleteReturn=$_GET["deleteReturn"] ; } else { $deleteReturn="" ; }
	$deleteReturnMessage ="" ;
	$class="error" ;
	if (!($deleteReturn=="")) {
		if ($deleteReturn=="success0") {
			$deleteReturnMessage ="Delete was successful." ;	
			$class="success" ;
		}
		print "<div class='$class'>" ;
			print $deleteReturnMessage;
		print "</div>" ;
	} 
	
	if (studentEnrolment($_SESSION[$guid]["gibbonPersonID"], $connection2)==FALSE) {
		print "<div class='error'>" ;
			print "You have not been enrolled for higher education applications." ;
		print "</div>" ;
	}
	else {
		print "<p>" ;
		print "Use the form below to request references for particular purposes, and then track the writing and completion of the reference. Please remember that your reference is a complex document written by several people, and so make take some time to create." ;
		print "</p>" ;
		
		try {
			$data=array("gibbonPersonID"=>$_SESSION[$guid]["gibbonPersonID"]); 
			$sql="SELECT higherEducationReference.* FROM higherEducationReference WHERE higherEducationReference.gibbonPersonID=:gibbonPersonID ORDER BY timestamp" ; 
			$result=$connection2->prepare($sql);
			$result->execute($data);
		}
		catch(PDOException $e) { 
			print "<div class='error'>" . $e->getMessage() . "</div>" ; 
		}
		
		print "<div class='linkTop'>" ;
		print "<a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . $_SESSION[$guid]["module"] . "/references_request_add.php'><img title='New' src='./themes/" . $_SESSION[$guid]["gibbonThemeName"] . "/img/page_new.gif'/></a>" ;
		print "</div>" ;
		
		if ($result->rowCount()<1) {
			print "<div class='error'>" ;
			print "There are no reference requests to display." ;
			print "</div>" ;
		}
		else {
			print "<table cellspacing='0' style='width: 100%'>" ;
				print "<tr class='head'>" ;
					print "<th>" ;
						print "Date<br/>" ;
						print "<span style='font-size: 75%; font-style: italic'>Time</span>" ;
					print "</th>" ;
					print "<th>" ;
						print "Type" ;
					print "</th>" ;
					print "<th colspan=2>" ;
						print "Status<br/>" ;
						print "<span style='font-size: 75%; font-style: italic'>Notes</span>" ;
					print "</th>" ;
					print "<th>" ;
						print "Referees" ;
					print "</th>" ;
				print "</tr>" ;
				
				$count=0;
				$rowNum="odd" ;
				while ($row=$result->fetch()) {
					if ($count%2==0) {
						$rowNum="even" ;
					}
					else {
						$rowNum="odd" ;
					}
					
					//COLOR ROW BY STATUS!
					print "<tr class=$rowNum>" ;
						print "<td>" ;
							print "<b>" . dateConvertBack(substr($row["timestamp"],0,10)) . "</b><br/>" ;
							print "<span style='font-size: 75%; font-style: italic'>" . substr($row["timestamp"],11,5) . "</span>" ;
						print "</td>" ;
						print "<td>" ;
							print $row["type"] ;
						print "</td>" ;
						print "<td style='width: 25px'>" ;
							if ($row["status"]=="Cancelled") {
								print "<img style='margin-right: 3px; float: left' title='Cancelled' src='./themes/" . $_SESSION[$guid]["gibbonThemeName"] . "/img/iconCross.png'/> " ;
							}
							else if ($row["status"]=="Complete") {
								print "<img style='margin-right: 3px; float: left' title='Complete' src='./themes/" . $_SESSION[$guid]["gibbonThemeName"] . "/img/iconTick.png'/> " ;
							}
							else {
								print "<img style='padding-bottom: 3px; margin-right: 3px; float: left' title='In Progress' src='./themes/" . $_SESSION[$guid]["gibbonThemeName"] . "/img/iconTick_light.png'/> " ;
							}
						print "</td>" ;
						print "<td>" ;
							print "<b>" . $row["status"] . "</b>" ;
							if ($row["statusNotes"]!="") {
								print "<br/><span style='font-size: 75%; font-style: italic'>" . $row["statusNotes"] . "</span>" ;
							}
						print "</td>" ;
						print "<td>" ;
							try {
								$dataReferee=array("higherEducationReferenceID"=>$row["higherEducationReferenceID"]); 
								$sqlReferee="SELECT DISTINCT gibbonPerson.title, surname, preferredName FROM higherEducationReferenceComponent JOIN gibbonPerson ON (higherEducationReferenceComponent.gibbonPersonID=gibbonPerson.gibbonPersonID) WHERE higherEducationReferenceID=:higherEducationReferenceID ORDER BY surname, preferredName" ; 
								$resultReferee=$connection2->prepare($sqlReferee);
								$resultReferee->execute($dataReferee);
							}
							catch(PDOException $e) { 
								print "<div class='error'>" . $e->getMessage() . "</div>" ; 
							}
							while ($rowReferee=$resultReferee->fetch()) {
								print formatName(htmlPrep($rowReferee["title"]), htmlPrep($rowReferee["preferredName"]), htmlPrep($rowReferee["surname"]), "Staff", false) . "<br/>" ;
							}
						print "</td>" ;
					print "</tr>" ;
					
					$count++ ;
				}
			print "</table>" ;
		}
	}
}
?>