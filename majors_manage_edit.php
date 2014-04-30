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
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

@session_start() ;

//Module includes
include "./modules/" . $_SESSION[$guid]["module"] . "/moduleFunctions.php" ;

if (isActionAccessible($guid, $connection2, "/modules/Higher Education/majors_manage_edit.php")==FALSE) {

	//Acess denied
	print "<div class='error'>" ;
		print "You do not have access to this action." ;
	print "</div>" ;
}
else {
	//Proceed!
	print "<div class='trail'>" ;
	print "<div class='trailHead'><a href='" . $_SESSION[$guid]["absoluteURL"] . "'>Home</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/" . getModuleEntry($_GET["q"], $connection2, $guid) . "'>" . getModuleName($_GET["q"]) . "</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/majors_manage.php'>Manage Majors</a> > </div><div class='trailEnd'>Edit Major</div>" ;
	print "</div>" ;
	
	$role=staffHigherEducationRole($_SESSION[$guid]["gibbonPersonID"], $connection2) ;
	if ($role!="Coordinator") {
		print "<div class='error'>" ;
			print "You do not have access to this action." ;
		print "</div>" ;
	}
	else {
		if (isset($_GET["updateReturn"])) { $updateReturn=$_GET["updateReturn"] ; } else { $updateReturn="" ; }
		$updateReturnMessage ="" ;
		$class="error" ;
		if (!($updateReturn=="")) {
			if ($updateReturn=="fail0") {
				$updateReturnMessage ="Update failed because you do not have access to this action." ;	
			}
			else if ($updateReturn=="fail1") {
				$updateReturnMessage ="Update failed because a required parameter was not set." ;	
			}
			else if ($updateReturn=="fail2") {
				$updateReturnMessage ="Update failed due to a database error." ;	
			}
			else if ($updateReturn=="fail3") {
				$updateReturnMessage ="Update failed because your inputs were invalid." ;	
			}
			else if ($updateReturn=="fail4") {
				$updateReturnMessage ="Update failed some values need to be unique but were not." ;	
			}
			else if ($updateReturn=="fail5") {
				$updateReturnMessage ="Update failed because your attachment could not be uploaded." ;	
			}
			else if ($updateReturn=="success0") {
				$updateReturnMessage ="Update was successful." ;	
				$class="success" ;
			}
			print "<div class='$class'>" ;
				print $updateReturnMessage;
			print "</div>" ;
		} 
		
		//Check if school year specified
		$higherEducationMajorID=$_GET["higherEducationMajorID"];
		if ($higherEducationMajorID=="Y") {
			print "<div class='error'>" ;
				print "You have not specified an activity." ;
			print "</div>" ;
		}
		else {
			try {
				$data=array("higherEducationMajorID"=>$higherEducationMajorID);  
				$sql="SELECT * FROM higherEducationMajor WHERE higherEducationMajorID=:higherEducationMajorID" ; 
				$result=$connection2->prepare($sql);
				$result->execute($data); 
			}
			catch(PDOException $e) { 
				print "<div class='error'>" ;
					print "The student cannot be edited due to a database error." ;
				print "</div>" ;
			}
			
			if ($result->rowCount()!=1) {
				print "<div class='error'>" ;
					print "The selected activity does not exist." ;
				print "</div>" ;
			}
			else {
				//Let's go!
				$row=$result->fetch() ;
				?>
				<form method="post" action="<?php print $_SESSION[$guid]["absoluteURL"] . "/modules/" . $_SESSION[$guid]["module"] . "/majors_manage_editProcess.php?higherEducationMajorID=$higherEducationMajorID" ?>">
					<table class='smallIntBorder' cellspacing='0' style="width: 100%">	
						<tr>
							<td> 
								<b>Name *</b><br/>
								<span style="font-size: 90%"><i></i></span>
							</td>
							<td class="right">
								<input name="name" id="uniname" maxlength=150 value="<?php print $row["name"] ?>" type="text" style="width: 300px">
								<script type="text/javascript">
									var uniname=new LiveValidation('uniname');
									uniname.add(Validate.Presence);
								 </script>
							</td>
						</tr>
						<tr>
							<td> 
								<b>Active *</b><br/>
							</td>
							<td class="right">
								<select name="active" id="active" style="width: 302px">
									<option <?php if ($row["active"]=="Y") { print " selected " ; } ?>value="Y">Y</option>
									<option <?php if ($row["active"]=="N") { print " selected " ; } ?>value="N">N</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>
								<span style="font-size: 90%"><i>* denotes a required field</i></span>
							</td>
							<td class="right">
								<input type="hidden" name="address" value="<?php print $_SESSION[$guid]["address"] ?>">
								<input type="submit" value="Submit">
							</td>
						</tr>
					</table>
				</form>
				<?php
			}
		}
	}
}
?>