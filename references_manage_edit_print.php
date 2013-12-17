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

if (isActionAccessible($guid, $connection2, "/modules/Higher Education/references_manage_edit.php")==FALSE) {
	//Acess denied
	print "<div class='error'>" ;
		print "You do not have access to this action." ;
	print "</div>" ;
}
else {
	$role=staffHigherEducationRole($_SESSION[$guid]["gibbonPersonID"], $connection2) ;
	if ($role!="Coordinator") {
		print "<div class='error'>" ;
			print "You do not have access to this action." ;
		print "</div>" ;
	}
	else {
		$higherEducationReferenceID=$_GET["higherEducationReferenceID"];
	
		//Proceed!
		print "<h2 class='top'>" ;
		print "Higher Education Reference" ;
		print "</h2>" ;
	
		if ($higherEducationReferenceID!="") {
			try {
				$data=array("higherEducationReferenceID"=>$higherEducationReferenceID);  
				$sql="SELECT preferredName, surname, higherEducationReference.* FROM higherEducationReference JOIN gibbonPerson ON (higherEducationReference.gibbonPersonID=gibbonPerson.gibbonPersonID) WHERE higherEducationReferenceID=:higherEducationReferenceID AND gibbonPerson.status='Full'" ; 
				$result=$connection2->prepare($sql);
				$result->execute($data); 
			}
			catch(PDOException $e) { 
				print "<div class='error'>" . $e->getMessage() . "</div>" ; 
			}
	
			if ($result->rowCount()!=1) {
				print "<div class='error'>" ;
					print "The selected reference does not exist." ;
				print "</div>" ;
			}
			else {
				//Let's go!
				$row=$result->fetch() ;
				
				print "<div class='linkTop'>" ;
				print "<a href='javascript:window.print()'><img title='Print' src='./themes/" . $_SESSION[$guid]["gibbonThemeName"] . "/img/print.png'/></a>" ;
				print "</div>" ;
				?>
				<table class='smallIntBorder' cellspacing='0' style="width: 100%">	
					<tr>
						<td> 
							<b>Student</b><br/>
						</td>
						<td class="right">
							<input readonly name="student" id="student" maxlength=255 value="<? print formatName("", htmlPrep($row["preferredName"]), htmlPrep($row["surname"]), "Student", false, false) ?>" type="text" style="width: 300px">
						</td>
					</tr>
					<tr>
						<td> 
							<b>Reference Type</b><br/>
						</td>
						<td class="right">
							<input readonly name="type" id="type" maxlength=255 value="<? print $row["type"] ?>" type="text" style="width: 300px">
						</td>
					</tr>
					<?
					try {
						$dataContributions=array("higherEducationReferenceID"=>$row["higherEducationReferenceID"]);  
						$sqlContributions="SELECT higherEducationReferenceComponent.*, preferredName, surname FROM higherEducationReferenceComponent JOIN gibbonPerson ON (higherEducationReferenceComponent.gibbonPersonID=gibbonPerson.gibbonPersonID) WHERE higherEducationReferenceID=:higherEducationReferenceID ORDER BY title" ; 
						$resultContributions=$connection2->prepare($sqlContributions);
						$resultContributions->execute($dataContributions); 
					}
					catch(PDOException $e) { }
					if ($resultContributions->rowCount()<1) {
						print "<tr>" ;
							print "<td colspan=2>" ;
								print "<i>Error: no referees requested, or a system error.</i>" ;
							print "</td>" ;
						print "</tr>" ;
					}
					else {
						while ($rowContributions=$resultContributions->fetch()) {
							print "<tr>" ;
								print "<td colspan=2>" ;
									print "<h4>" ;
										if ($rowContributions["title"]=="") {
											print $rowContributions["type"] . " Comment" ;
											print "<span style='font-size: 75%; font-style: italic'>" ;
												print " . by " . formatName("", $rowContributions["preferredName"], $rowContributions["surname"], "Staff", false, true) ;
											print "</span>" ;
										}
										else {
											print $rowContributions["title"] ;
											print "<span style='font-size: 75%; font-style: italic'>" ;
												print " . " . $rowContributions["type"] . " comment by " . formatName("", $rowContributions["preferredName"], $rowContributions["surname"], "Staff", false, true) ;
											print "</span>" ;
										}
									print "</h4>" ;
									print "<p>" ;
										print $rowContributions["body"] ;
									print "</p>" ;
								print "</td>" ;
							print "</tr>" ;
						}
					}
				print "</table>" ;
			}			
		}
	}
}
?>