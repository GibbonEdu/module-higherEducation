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

@session_start() ;

//Module includes
include "./modules/" . $_SESSION[$guid]["module"] . "/moduleFunctions.php" ;

if (isActionAccessible($guid, $connection2, "/modules/Higher Education/references_manage.php")==FALSE) {
	//Acess denied
	print "<div class='error'>" ;
		print "You do not have access to this action." ;
	print "</div>" ;
}
else {
	$role=staffHigherEducationRole($_SESSION[$guid]["gibbonPersonID"], $connection2) ;
	if ($role==FALSE) {
		//Acess denied
		print "<div class='error'>" ;
			print "You are not enroled in the Higher Education programme." ;
		print "</div>" ;
	}
	else {
		if ($role!="Coordinator") {
			//Acess denied
			print "<div class='error'>" ;
				print "You do not have permission to access this page." ;
			print "</div>" ;
		}
		else {
			//Proceed!
			print "<div class='trail'>" ;
			print "<div class='trailHead'><a href='" . $_SESSION[$guid]["absoluteURL"] . "'>Home</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/" . getModuleEntry($_GET["q"], $connection2, $guid) . "'>" . getModuleName($_GET["q"]) . "</a> > </div><div class='trailEnd'>Manage References</div>" ;
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
			
			$gibbonSchoolYearID=NULL ;
			if (isset($_GET["gibbonSchoolYearID"])) {
				$gibbonSchoolYearID=$_GET["gibbonSchoolYearID"] ;
			}
			if ($gibbonSchoolYearID=="") {
				$gibbonSchoolYearID=$_SESSION[$guid]["gibbonSchoolYearID"] ;
				$gibbonSchoolYearName=$_SESSION[$guid]["gibbonSchoolYearName"] ;
			}
			if (isset($_GET["gibbonSchoolYearID"])) {
				try {
					$data=array("gibbonSchoolYearID"=>$_GET["gibbonSchoolYearID"]); 
					$sql="SELECT * FROM gibbonSchoolYear WHERE gibbonSchoolYearID=:gibbonSchoolYearID" ;
					$result=$connection2->prepare($sql);
					$result->execute($data);
				}
				catch(PDOException $e) { 
					print "<div class='error'>" . $e->getMessage() . "</div>" ; 
				}
				if ($result->rowcount()!=1) {
					print "<div class='error'>" ;
						print "The specified year does not exist." ;
					print "</div>" ;
				}
				else {
					$row=$result->fetch() ;
					$gibbonSchoolYearID=$row["gibbonSchoolYearID"] ;
					$gibbonSchoolYearName=$row["name"] ;
				}
			}
			
			$search="" ;
			if (isset($_GET["search"])) {
				$search=$_GET["search"] ;
			}
			
			if ($gibbonSchoolYearID!="") {
				print "<h2 class='top'>" ;
					print $gibbonSchoolYearName ;
				print "</h2>" ;
				
				print "<div class='linkTop'>" ;
					//Print year picker
					if (getPreviousSchoolYearID($gibbonSchoolYearID, $connection2)!=FALSE) {
						print "<a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . $_SESSION[$guid]["module"] . "/references_manage.php&gibbonSchoolYearID=" . getPreviousSchoolYearID($gibbonSchoolYearID, $connection2) . "'>Previous Year</a> " ;
					}
					else {
						print "Previous Year " ;
					}
					print " | " ;
					if (getNextSchoolYearID($gibbonSchoolYearID, $connection2)!=FALSE) {
						print "<a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . $_SESSION[$guid]["module"] . "/references_manage.php&gibbonSchoolYearID=" . getNextSchoolYearID($gibbonSchoolYearID, $connection2) . "'>Next Year</a> " ;
					}
					else {
						print "Next Year " ;
					}
				print "</div>" ;
			
				print "<h3 class='top'>" ;
				print "Search" ;
				print "</h3>" ;
				print "<div class='linkTop'>" ;
				print "<a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . $_SESSION[$guid]["module"] . "/references_manage.php'>Clear Search</a>" ;
				print "</div>" ;
				?>
				<form method="get" action="<?php print $_SESSION[$guid]["absoluteURL"]?>/index.php">
					<table class='smallIntBorder' cellspacing='0' style="width: 100%">	
						<tr>
							<td> 
								<b>Search For</b><br/>
								<span style="font-size: 90%"><i>Preferred, surname, username.</i></span>
							</td>
							<td class="right">
								<input name="search" id="search" maxlength=20 value="<?php print $search ?>" type="text" style="width: 300px">
							</td>
						</tr>
						<tr>
							<td colspan=2 class="right">
								<input type="hidden" name="q" value="/modules/<?php print $_SESSION[$guid]["module"] ?>/references_manage.php">
								<input type="hidden" name="address" value="<?php print $_SESSION[$guid]["address"] ?>">
								<input type="submit" value="Submit">
							</td>
						</tr>
					</table>
				</form>
				<?php
				
				print "<h3 class='top'>" ;
				print "View" ;
				print "</h3>" ;
				print "<p>" ;
				print "The table below shows all references request in the selected school year. Use the \"Previous Year\" and \"Next Year\" links to navigate to other years." ;
				print "<p>" ;
				
				//Set pagination variable
				$page="" ;
				if (isset($_GET["page"])) {
					$page=$_GET["page"] ;
				}
				if ((!is_numeric($page)) OR $page<1) {
					$page=1 ;
				}
				
				try {
					$data=array("gibbonSchoolYearID"=>$gibbonSchoolYearID); 
					$sql="SELECT higherEducationReference.*, surname, preferredName, title FROM higherEducationReference JOIN gibbonPerson ON (higherEducationReference.gibbonPersonID=gibbonPerson.gibbonPersonID) WHERE higherEducationReference.gibbonSchoolYearID=:gibbonSchoolYearID AND gibbonPerson.status='Full' ORDER BY status, timestamp" ; 
					if ($search!="") {
						$data=array("gibbonSchoolYearID"=>$gibbonSchoolYearID, "search1"=>"%$search%", "search2"=>"%$search%", "search3"=>"%$search%"); 
						$sql="SELECT higherEducationReference.*, surname, preferredName, title FROM higherEducationReference JOIN gibbonPerson ON (higherEducationReference.gibbonPersonID=gibbonPerson.gibbonPersonID) WHERE higherEducationReference.gibbonSchoolYearID=:gibbonSchoolYearID AND gibbonPerson.status='Full' AND (preferredName LIKE :search1 OR surname LIKE :search2 OR username LIKE :search3) ORDER BY status, timestamp" ; 
					}
					$sqlPage= $sql . " LIMIT " . $_SESSION[$guid]["pagination"] . " OFFSET " . (($page-1)*$_SESSION[$guid]["pagination"]) ; 
					$result=$connection2->prepare($sql);
					$result->execute($data);
				}
				catch(PDOException $e) { 
					print "<div class='error'>" . $e->getMessage() . "</div>" ; 
				}
				
				if ($result->rowCount()<1) {
					print "<div class='error'>" ;
					print "There are no enroled students to display." ;
					print "</div>" ;
				}
				else {
					if ($result->rowCount()>$_SESSION[$guid]["pagination"]) {
						printPagination($guid, $result->rowCount(), $page, $_SESSION[$guid]["pagination"], "top", "gibbonSchoolYearID=$gibbonSchoolYearID&search=$search") ;
					}
				
					print "<table cellspacing='0' style='width: 100%'>" ;
						print "<tr class='head'>" ;
							print "<th>" ;
								print "Name<br/>" ;
								print "<span style='font-size: 75%; font-style: italic'>Date</span>" ;
							print "</th>" ;
							print "<th colspan=2>" ;
								print "Status" ;
							print "</th>" ;
							print "<th>" ;
								print "Type" ;
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
									print $row["type"] ;
								print "</td>" ;
								print "<td>" ;
									print "<a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . $_SESSION[$guid]["module"] . "/references_manage_edit.php&higherEducationReferenceID=" . $row["higherEducationReferenceID"] . "&gibbonSchoolYearID=$gibbonSchoolYearID'><img title='Edit' src='./themes/" . $_SESSION[$guid]["gibbonThemeName"] . "/img/config.png'/></a> " ;
									print "<a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . $_SESSION[$guid]["module"] . "/references_manage_delete.php&higherEducationReferenceID=" . $row["higherEducationReferenceID"] . "&gibbonSchoolYearID=$gibbonSchoolYearID'><img title='Delete' src='./themes/" . $_SESSION[$guid]["gibbonThemeName"] . "/img/garbage.png'/></a>" ;
									print "<a target='_blank' href='" . $_SESSION[$guid]["absoluteURL"] . "/report.php?q=/modules/" . $_SESSION[$guid]["module"] . "/references_manage_edit_print.php&higherEducationReferenceID=" . $row["higherEducationReferenceID"] . "'><img title='Print' src='./themes/" . $_SESSION[$guid]["gibbonThemeName"] . "/img/print.png'/></a>" ;
								print "</td>" ;
							print "</tr>" ;
						}
					print "</table>" ;
					
					if ($result->rowCount()>$_SESSION[$guid]["pagination"]) {
						printPagination($guid, $result->rowCount(), $page, $_SESSION[$guid]["pagination"], "bottom", "gibbonSchoolYearID=$gibbonSchoolYearID&search=$search") ;
					}
				}
			}
		}
	}
}
?>