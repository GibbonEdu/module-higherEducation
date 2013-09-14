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

if (isActionAccessible($guid, $connection2, "/modules/Higher Education/applications_track_delete.php")==FALSE) {
	//Acess denied
	print "<div class='error'>" ;
		print "You do not have access to this action." ;
	print "</div>" ;
}
else {
	//Proceed!
	print "<div class='trail'>" ;
	print "<div class='trailHead'><a href='" . $_SESSION[$guid]["absoluteURL"] . "'>Home</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/" . getModuleEntry($_GET["q"], $connection2, $guid) . "'>" . getModuleName($_GET["q"]) . "</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/applications_track.php'>Track Applications</a> > </div><div class='trailEnd'>Delete Application</div>" ; 
	print "</div>" ;
	
	$deleteReturn = $_GET["deleteReturn"] ;
	$deleteReturnMessage ="" ;
	$class="error" ;
	if (!($deleteReturn=="")) {
		if ($deleteReturn=="fail0") {
			$deleteReturnMessage ="Update failed because you do not have access to this action." ;	
		}
		else if ($deleteReturn=="fail1") {
			$deleteReturnMessage ="Update failed because a required parameter was not set." ;	
		}
		else if ($deleteReturn=="fail2") {
			$deleteReturnMessage ="Update failed due to a database error." ;	
		}
		else if ($deleteReturn=="fail3") {
			$deleteReturnMessage ="Update failed because your inputs were invalid." ;	
		}
		print "<div class='$class'>" ;
			print $deleteReturnMessage;
		print "</div>" ;
	} 
	
	//Check for student enrolment
	if (studentEnrolment($_SESSION[$guid]["gibbonPersonID"], $connection2)==FALSE) {
		print "<div class='error'>" ;
			print "You have not been enrolled for higher education applications." ;
		print "</div>" ;
	}
	else {
		//Check for application record
		try {
			$data=array("gibbonPersonID"=>$_SESSION[$guid]["gibbonPersonID"]); 
			$sql="SELECT * FROM  higherEducationApplication WHERE gibbonPersonID=:gibbonPersonID" ;
			$result=$connection2->prepare($sql);
			$result->execute($data);
		}
		catch(PDOException $e) { 
			print "<div class='error'>" . $e->getMessage() . "</div>" ; 
		}
		
		if ($result->rowCount()!=1) {
			print "<div class='error'>" ;
				print "You have not saved your application process yet." ;
			print "</div>" ;
		}
		else {
			$row=$result->fetch() ;
	
			//Check if school year specified
			$higherEducationApplicationInstitutionID=$_GET["higherEducationApplicationInstitutionID"] ;
			if ($higherEducationApplicationInstitutionID=="") {
				print "<div class='error'>" ;
					print "You have not specified an application." ;
				print "</div>" ;
			}
			else {
				try {
					$data=array("higherEducationApplicationInstitutionID"=>$higherEducationApplicationInstitutionID); 
					$sql="SELECT * FROM higherEducationApplicationInstitution JOIN higherEducationInstitution ON (higherEducationApplicationInstitution.higherEducationInstitutionID=higherEducationInstitution.higherEducationInstitutionID) WHERE higherEducationApplicationInstitutionID=:higherEducationApplicationInstitutionID" ;
					$result=$connection2->prepare($sql);
					$result->execute($data);
				}
				catch(PDOException $e) { 
					print "<div class='error'>" . $e->getMessage() . "</div>" ; 
				}
				
				if ($result->rowCount()!=1) {
					print "<div class='error'>" ;
						print "The specified application cannot be found." ;
					print "</div>" ;
				}
				else {
					//Let's go!
					$row=$result->fetch() ;
					?>
					<form method="post" action="<? print $_SESSION[$guid]["absoluteURL"] . "/modules/" . $_SESSION[$guid]["module"] . "/applications_track_deleteProcess.php?higherEducationApplicationInstitutionID=$higherEducationApplicationInstitutionID" ?>">
						<table style="width: 100%">	
							<tr>
								<td> 
									<b>Are you sure you want to delete your application to study at <? print $row["name"] ?>?</b><br/>
									<span style="font-size: 90%; color: #cc0000"><i>This operation cannot be undone, and may lead to loss of vital data in your system.<br/>PROCEED WITH CAUTION!</i></span>
								</td>
								<td class="right">
									
								</td>
							</tr>
							<tr>
								<td> 
									<input type="hidden" name="address" value="<? print $_SESSION[$guid]["address"] ?>">
									<input type="submit" value="Yes">
								</td>
								<td class="right">
									
								</td>
							</tr>
						</table>
					</form>
					<?
				}
			}
		}
	}
}
?>