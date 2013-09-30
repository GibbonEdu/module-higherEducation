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

session_start() ;

//Module includes
include "./modules/" . $_SESSION[$guid]["module"] . "/moduleFunctions.php" ;

if (isActionAccessible($guid, $connection2, "/modules/Higher Education/institutions_manage.php")==FALSE) {
	//Acess denied
	print "<div class='error'>" ;
		print "You do not have access to this action." ;
	print "</div>" ;
}
else {
	print "<div class='trail'>" ;
	print "<div class='trailHead'><a href='" . $_SESSION[$guid]["absoluteURL"] . "'>Home</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/" . getModuleEntry($_GET["q"], $connection2, $guid) . "'>" . getModuleName($_GET["q"]) . "</a> > </div><div class='trailEnd'>Manage Institutions</div>" ;
	print "</div>" ;
	
	$role=staffHigherEducationRole($_SESSION[$guid]["gibbonPersonID"], $connection2) ;
	if ($role!="Coordinator") {
		print "<div class='error'>" ;
			print "You do not have access to this action." ;
		print "</div>" ;
	}
	else {
		$deleteReturn = $_GET["deleteReturn"] ;
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
		
		//Set pagination variable
		$page=$_GET["page"] ;
		if ((!is_numeric($page)) OR $page<1) {
			$page=1 ;
		}
		
		try {
			$data=array("gibbonSchoolYearID"=>$_SESSION[$guid]["gibbonSchoolYearID"]);  
			$sql="SELECT * FROM higherEducationInstitution ORDER BY name, country" ; 
			$sqlPage= $sql . " LIMIT " . $_SESSION[$guid]["pagination"] . " OFFSET " . (($page-1)*$_SESSION[$guid]["pagination"]) ;
			$result=$connection2->prepare($sql);
			$result->execute($data); 
		}
		catch(PDOException $e) { 
			print "<div class='error'>" ;
			print $e->getMEssagE() ;
			print "Students cannot be displayed." ;
			print "</div>" ;
		}
		
		print "<div class='linkTop'>" ;
		print "<a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . $_SESSION[$guid]["module"] . "/institutions_manage_add.php'><img title='New' src='./themes/" . $_SESSION[$guid]["gibbonThemeName"] . "/img/page_new.gif'/></a>" ;
		print "</div>" ;
		
		if ($result->rowCount()<1) {
			print "<div class='error'>" ;
			print "There are no students to display." ;
			print "</div>" ;
		}
		else {
			if ($result->rowCount()>$_SESSION[$guid]["pagination"]) {
				printPagination($guid, $result->rowCount(), $page, $_SESSION[$guid]["pagination"], "top") ;
			}
		
			print "<table cellspacing='0' style='width: 100%'>" ;
				print "<tr class='head'>" ;
					print "<th>" ;
						print "Name" ;
					print "</th>" ;
					print "<th>" ;
						print "Active" ;
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
					
					if ($row["active"]=="N") {
						$rowNum="error" ;
					}
	
					//COLOR ROW BY STATUS!
					print "<tr class=$rowNum>" ;
						print "<td>" ;
							print $row["name"] . ", " . $row["country"] ;
						print "</td>" ;
						print "<td>" ;
							print $row["active"] ;
						print "</td>" ;
						print "<td>" ;
							print "<a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . $_SESSION[$guid]["module"] . "/institutions_manage_edit.php&higherEducationInstitutionID=" . $row["higherEducationInstitutionID"] . "'><img title='Edit' src='./themes/" . $_SESSION[$guid]["gibbonThemeName"] . "/img/config.png'/></a> " ;
							print "<a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . $_SESSION[$guid]["module"] . "/institutions_manage_delete.php&higherEducationInstitutionID=" . $row["higherEducationInstitutionID"] . "'><img title='Delete' src='./themes/" . $_SESSION[$guid]["gibbonThemeName"] . "/img/garbage.png'/></a> " ;
						print "</td>" ;
					print "</tr>" ;
				}
			print "</table>" ;
			
			if ($result->rowCount()>$_SESSION[$guid]["pagination"]) {
				printPagination($guid, $result->rowCount(), $page, $_SESSION[$guid]["pagination"], "bottom") ;
			}
		}
	}
}	
?>