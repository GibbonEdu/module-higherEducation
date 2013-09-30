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

if (isActionAccessible($guid, $connection2, "/modules/Higher Education/institutions_manage_delete.php")==FALSE) {

	//Acess denied
	print "<div class='error'>" ;
		print "You do not have access to this action." ;
	print "</div>" ;
}
else {
	//Proceed!
	print "<div class='trail'>" ;
	print "<div class='trailHead'><a href='" . $_SESSION[$guid]["absoluteURL"] . "'>Home</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/" . getModuleEntry($_GET["q"], $connection2, $guid) . "'>" . getModuleName($_GET["q"]) . "</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/institutions_manage.php'>Manage Institutions</a> > </div><div class='trailEnd'>Delete Institution</div>" ;
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
		
		//Check if school year specified
		$higherEducationInstitutionID=$_GET["higherEducationInstitutionID"];
		if ($higherEducationInstitutionID=="") {
			print "<div class='error'>" ;
				print "You have not specified a student member." ;
			print "</div>" ;
		}
		else {
			try {
				$data=array("higherEducationInstitutionID"=>$higherEducationInstitutionID);  
				$sql="SELECT * FROM higherEducationInstitution WHERE higherEducationInstitutionID=:higherEducationInstitutionID" ; 
				$result=$connection2->prepare($sql);
				$result->execute($data); 
			}
			catch(PDOException $e) { 
				print "<div class='error'>" . $e->getMessage() . "</div>" ; 
			}
	
			if ($result->rowCount()!=1) {
				print "<div class='error'>" ;
					print "The selected student member does not exist." ;
				print "</div>" ;
			}
			else {
				//Let's go!
				$row=$result->fetch() ;
				?>
				<form method="post" action="<? print $_SESSION[$guid]["absoluteURL"] . "/modules/" . $_SESSION[$guid]["module"] . "/institutions_manage_deleteProcess.php?higherEducationInstitutionID=$higherEducationInstitutionID" ?>">
					<table class='smallIntBorder' cellspacing='0' style="width: 100%">	
						<tr>
							<td> 
								<b>Are you sure you want to delete "<? print $row["name"] ?>" from the Higher Education programme?</b><br/>
								<span style="font-size: 90%; color: #cc0000"><i>This operation cannot be undone, and may lead to loss of vital data in your system.<br/>PROCEED WITH CAUTION!</i></span>
							</td>
							<td class="right">
								
							</td>
						</tr>
						<tr>
							<td> 
								<input name="higherEducationInstitutionID" id="higherEducationInstitutionID" value="<? print $higherEducationInstitutionID ?>" type="hidden">
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
?>