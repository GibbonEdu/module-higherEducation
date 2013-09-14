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

if (isActionAccessible($guid, $connection2, "/modules/Higher Education/references_manage_edit.php")==FALSE) {
	//Acess denied
	print "<div class='error'>" ;
		print "You do not have access to this action." ;
	print "</div>" ;
}
else {
	//Check if school year specified
	$gibbonSchoolYearID=$_GET["gibbonSchoolYearID"];
	$higherEducationReferenceComponentID=$_GET["higherEducationReferenceComponentID"] ;
	$higherEducationReferenceID=$_GET["higherEducationReferenceID"] ;
	if ($higherEducationReferenceComponentID=="" OR $higherEducationReferenceID=="" OR $gibbonSchoolYearID=="") {
		print "<div class='error'>" ;
			print "You have not specified a reference or component." ;
		print "</div>" ;
	}
	else {
		try {
			$data=array("higherEducationReferenceComponentID"=>$higherEducationReferenceComponentID); 
			$sql="SELECT * FROM higherEducationReferenceComponent WHERE higherEducationReferenceComponentID=:higherEducationReferenceComponentID" ;
			$result=$connection2->prepare($sql);
			$result->execute($data);
		}
		catch(PDOException $e) { 
			print "<div class='error'>" . $e->getMessage() . "</div>" ; 
		}

		if ($result->rowCount()!=1) {
			print "<div class='error'>" ;
				print "The specified reference component cannot be found." ;
			print "</div>" ;
		}
		else {
			//Let's go!
			$row=$result->fetch() ;
			
			print "<div class='trail'>" ;
			print "<div class='trailHead'><a href='" . $_SESSION[$guid]["absoluteURL"] . "'>Home</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/" . getModuleEntry($_GET["q"], $connection2, $guid) . "'>" . getModuleName($_GET["q"]) . "</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/references_manage.php&gibbonSchoolYearID=$gibbonSchoolYearID'>Manage References</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/references_manage_edit.php&higherEducationReferenceID=$higherEducationReferenceID&gibbonSchoolYearID=$gibbonSchoolYearID'>Edit Reference</a> > </div><div class='trailEnd'>Delete Contribution</div>" ;
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
			?>
			<form method="post" action="<? print $_SESSION[$guid]["absoluteURL"] . "/modules/" . $_SESSION[$guid]["module"] . "/references_manage_edit_contribution_deleteProcess.php?higherEducationReferenceComponentID=$higherEducationReferenceComponentID&higherEducationReferenceID=$higherEducationReferenceID&gibbonSchoolYearID=$gibbonSchoolYearID" ?>">
				<table style="width: 100%">	
					<tr>
						<td> 
							<b>Are you sure you want to delete this contribution?</b><br/>
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
?>