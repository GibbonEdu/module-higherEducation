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
			print "You have not specified a grade scale or grade." ;
		print "</div>" ;
	}
	else {
		try {
			$data=array("higherEducationReferenceID"=>$higherEducationReferenceID, "higherEducationReferenceComponentID"=>$higherEducationReferenceComponentID); 
			$sql="SELECT higherEducationReferenceComponent.*, higherEducationReference.type AS refType FROM higherEducationReference JOIN higherEducationReferenceComponent ON (higherEducationReference.higherEducationReferenceID=higherEducationReferenceComponent.higherEducationReferenceID) WHERE higherEducationReferenceComponent.higherEducationReferenceID=:higherEducationReferenceID AND higherEducationReferenceComponent.higherEducationReferenceComponentID=:higherEducationReferenceComponentID" ;
			$result=$connection2->prepare($sql);
			$result->execute($data);
		}
		catch(PDOException $e) { 
			print "<div class='error'>" . $e->getMessage() . "</div>" ; 
		}
		
		if ($result->rowCount()!=1) {
			print "<div class='error'>" ;
				print "The specified class cannot be found." ;
			print "</div>" ;
		}
		else {
			//Let's go!
			$row=$result->fetch() ;
			
			print "<div class='trail'>" ;
			print "<div class='trailHead'><a href='" . $_SESSION[$guid]["absoluteURL"] . "'>Home</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/" . getModuleEntry($_GET["q"], $connection2, $guid) . "'>" . getModuleName($_GET["q"]) . "</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/references_manage.php&gibbonSchoolYearID=$gibbonSchoolYearID'>Manage References</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/references_manage_edit.php&higherEducationReferenceID=$higherEducationReferenceID&gibbonSchoolYearID=$gibbonSchoolYearID'>Edit Reference</a> > </div><div class='trailEnd'>Edit Contribution</div>" ;
			print "</div>" ;
			
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
				else if ($updateReturn=="success0") {
					$updateReturnMessage ="Update was successful." ;	
					$class="success" ;
				}
				print "<div class='$class'>" ;
					print $updateReturnMessage;
				print "</div>" ;
			} 
			?>
			<form method="post" action="<? print $_SESSION[$guid]["absoluteURL"] . "/modules/" . $_SESSION[$guid]["module"] . "/references_manage_edit_contribution_editProcess.php?higherEducationReferenceComponentID=$higherEducationReferenceComponentID&higherEducationReferenceID=$higherEducationReferenceID&gibbonSchoolYearID=$gibbonSchoolYearID" ?>">
				<table class='smallIntBorder' cellspacing='0' style="width: 100%">	
					<tr>
						<td> 
							<b>Contribution Type *</b><br/>
							<span style="font-size: 90%"><i>This value cannot be changed.</i></span>
						</td>
						<td class="right">
							<input readonly name="type" id="type" maxlength=255 value="<? print $row["type"] ?>" type="text" style="width: 300px">
						</td>
					</tr>
					<tr>
						<td> 
							<b>Title *</b><br/>
						</td>
						<td class="right">
							<input name="title" id="title" maxlength=10 value="<? print $row["title"] ?>" type="text" style="width: 300px">
							<script type="text/javascript">
								var title=new LiveValidation('title');
								title.add(Validate.Presence);
							 </script>
						</td>
					</tr>
					<tr>
						<td colspan=2 style='padding-top: 15px;'> 
							<b>Reference *</b><br/>
							<span style="font-size: 90%"><i>
							<?
							if ($row["refType"]=="US Reference") {
								print "Maximum limit of 10,000 characters." ;
							}
							else {
								print "Maximum limit of 2,000 characters." ;
							}
							?>
							</i></span><br/>
							<textarea name="body" id="body" rows=20 style="width:738px; margin: 5px 0px 0px 0px"><? print $row["body"] ?></textarea>
							<script type="text/javascript">
								var body=new LiveValidation('body');
								body.add(Validate.Presence);
								<?
								if ($row["refType"]=="US Reference") {
									print "body.add( Validate.Length, { maximum: 10000 } );" ;
								}
								else {
									print "body.add( Validate.Length, { maximum: 2000 } );" ;
								}
								?>
							 </script>
						</td>
					</tr>
					<tr>
						<td> 
							<b>Status *</b><br/>
						</td>
						<td class="right">
							<select name="status" id="status" style="width: 302px">
								<option <? if ($row["status"]=="In Progress") { print "selected" ; } ?> value='In Progress'>In Progress</option> ;
								<option <? if ($row["status"]=="Complete") { print "selected" ; } ?> value='Complete'>Complete</option> ;
							</select>
						</td>
					</tr>
					<tr>
						<td>
							<span style="font-size: 90%"><i>* denotes a required field</i></span>
						</td>
						<td class="right">
							<input name="higherEducationReferenceID" id="higherEducationReferenceID" value="<? print $higherEducationReferenceID ?>" type="hidden">
							<input type="hidden" name="address" value="<? print $_SESSION[$guid]["address"] ?>">
							<input type="submit" value="Submit">
						</td>
					</tr>
				</table>
			</form>
			<?
		}
	}
}
?>