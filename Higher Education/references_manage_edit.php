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

if (isActionAccessible($guid, $connection2, "/modules/Higher Education/references_manage_edit.php")==FALSE) {
	//Acess denied
	print "<div class='error'>" ;
		print "You do not have access to this action." ;
	print "</div>" ;
}
else {
	//Proceed!
	print "<div class='trail'>" ;
	print "<div class='trailHead'><a href='" . $_SESSION[$guid]["absoluteURL"] . "'>Home</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/" . getModuleEntry($_GET["q"], $connection2, $guid) . "'>" . getModuleName($_GET["q"]) . "</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/references_manage.php&gibbonSchoolYearID=" . $_GET["gibbonSchoolYearID"] . "&higherEducationReferenceID=" . $_GET["higherEducationReferenceID"] . "'>Manage References</a> > </div><div class='trailEnd'>Edit Reference</div>" ;
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
			else if ($updateReturn=="success0") {
				$updateReturnMessage ="Update was successful." ;	
				$class="success" ;
			}
			print "<div class='$class'>" ;
				print $updateReturnMessage;
			print "</div>" ;
		} 
		
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
		
		//Check if school year specified
		$gibbonSchoolYearID=$_GET["gibbonSchoolYearID"];
		$higherEducationReferenceID=$_GET["higherEducationReferenceID"];
		if ($higherEducationReferenceID=="" OR $gibbonSchoolYearID=="") {
			print "<div class='error'>" ;
				print "You have not specified a reference." ;
			print "</div>" ;
		}
		else {
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
				print "<a target='_blank' href='" . $_SESSION[$guid]["absoluteURL"] . "/report.php?q=/modules/" . $_SESSION[$guid]["module"] . "/references_manage_edit_print.php&higherEducationReferenceID=$higherEducationReferenceID'><img title='Print' src='./themes/" . $_SESSION[$guid]["gibbonThemeName"] . "/img/print.png'/></a>" ;
				print "</div>" ;
				?>
				
				<form method="post" action="<?php print $_SESSION[$guid]["absoluteURL"] . "/modules/" . $_SESSION[$guid]["module"] . "/references_manage_editProcess.php?higherEducationReferenceID=$higherEducationReferenceID&gibbonSchoolYearID=$gibbonSchoolYearID" ?>">
					<table class='smallIntBorder' cellspacing='0' style="width: 100%">	
						<tr class='break'>
							<td colspan=2> 
								<h3 class='top'>Reference Information</h3>
							</td>
						</tr>		
						<tr>
							<td> 
								<b>Student *</b><br/>
								<span style="font-size: 90%"><i>This value cannot be changed.</i></span>
							</td>
							<td class="right">
								<input readonly name="student" id="student" maxlength=255 value="<?php print formatName("", htmlPrep($row["preferredName"]), htmlPrep($row["surname"]), "Student", false, false) ?>" type="text" style="width: 300px">
							</td>
						</tr>
						<tr>
							<td> 
								<b>Type *</b><br/>
								<span style="font-size: 90%"><i>This value cannot be changed.</i></span>
							</td>
							<td class="right">
								<input readonly name="type" id="type" maxlength=255 value="<?php print $row["type"] ?>" type="text" style="width: 300px">
							</td>
						</tr>
						<tr>
							<td> 
								<b>Status *</b><br/>
							</td>
							<td class="right">
								<select name="status" id="status" style="width: 302px">
									<?php
									if ($row["status"]=="Pending") {
										?>
										<option <?php if ($row["status"]=="Pending") { print "selected" ; } ?> value='Pending'>Pending</option> ;
										<option <?php if ($row["status"]=="In Progress") { print "selected" ; } ?> value='In Progress'>In Progress</option> ;
										<option <?php if ($row["status"]=="Complete") { print "selected" ; } ?> value='Complete'>Complete</option> ;
										<option <?php if ($row["status"]=="Cancelled") { print "selected" ; } ?> value='Cancelled'>Cancelled</option> ;
										<?php
									}
									else if ($row["status"]=="In Progress") {
										?>
										<option <?php if ($row["status"]=="In Progress") { print "selected" ; } ?> value='In Progress'>In Progress</option> ;
										<option <?php if ($row["status"]=="Complete") { print "selected" ; } ?> value='Complete'>Complete</option> ;
										<option <?php if ($row["status"]=="Cancelled") { print "selected" ; } ?> value='Cancelled'>Cancelled</option> ;
										<?php
									}
									else if ($row["status"]=="Complete") {
										?>
										<option <?php if ($row["status"]=="Complete") { print "selected" ; } ?> value='Complete'>Complete</option> ;
										<?php
									}
									else if ($row["status"]=="Cancelled") {
										?>
										<option <?php if ($row["status"]=="Cancelled") { print "selected" ; } ?> value='Cancelled'>Cancelled</option> ;
										<?php
									}
									?>
								</select>
							</td>
						</tr>
						<tr>
							<td> 
								<b>Status Notes</b><br/>
								<?php
								if ($row["status"]=="Pending" OR $row["status"]=="In Progress") {
									print "<span style=\"font-size: 90%\"><i>Brief comment on current status.</i></span>" ;
								}
								else if ($row["status"]=="Complete" OR $row["status"]=="Cancelled") {
									print "<span style=\"font-size: 90%\"><i>Brief comment on current status. This value cannot be changed.</i></span>" ;
								}
								?>
							</td>
							<td class="right">
								<?php
								$readonly="" ;
								if ($row["status"]=="Complete" OR $row["status"]=="Cancelled") {
									$readonly="readonly" ;
								}
								print "<input $readonly name=\"statusNotes\" id=\"statusNotes\" maxlength=255 value=\"" . $row["statusNotes"] . "\" type=\"text\" style=\"width: 300px\">" ;
								?>
							</td>
						</tr>
						
						<tr>
							<td colspan=2 style='padding-top: 15px;'> 
								<b>Reference Notes</b><br/>
								<span style="font-size: 90%"><i>Information about this reference shared by the student. This value cannot be changed.</i></span><br/>
								<textarea readonly name="notes" id="notes" rows=4 style="width:738px; margin: 5px 0px 0px 0px"><?php print $row["notes"] ?></textarea>
							</td>
						</tr>
						
						
						<script type="text/javascript">
							$(document).ready(function(){
								$("#status").change(function(){
									if ($('#status option:selected').val() == "In Progress") {
										$("#contributionsRow").slideDown("fast", $("#contributionsRow").css("display","table-row")); //Slide Down Effect
									}
									else {
										$("#contributionsRow").css("display","none");
									}
								 });
							});
						</script>
						<tr class='break'>
							<td colspan=2> 
								<h3>Contributions</h3>
							</td>
						</tr>
						<tr>
							<td colspan=2> 
								<?php
								//Check alert status
								print "<input type=\"hidden\" name=\"alertsSent\" value=\"" . $row["alertsSent"] . "\">" ;
								$style="" ;
								if ($row["status"]!="In Progress") { $style="style='display: none'" ; }
								print "<div id='contributionsRow' $style>" ;
									if ($row["alertsSent"]=="N") {
										print "<div class='warning'>" ;
											print "The user(s) listed below will be notified that their input is required for this reference. This will take place the next time you press the Submit beutton below." ;
										print "</div>" ;
									}
									else {
										print "<div class='success'>" ;
											print "The user(s) listed below have already been notified by email that their input is required for this reference, and will not be alerted again." ;
										print "</div>" ;
									}
								print "</div>" ;
								
								print "<table cellspacing='0' style='width: 100%'>" ;
									print "<tr class='head'>" ;
										print "<th>" ;
											print "Name<br/>" ;
										print "</th>" ;
										print "<th colspan=2>" ;
											print "Status<br/>" ;
										print "</th>" ;
										print "<th>" ;
											print "Type" ;
										print "</th>" ;
										print "<th>" ;
											print "Title" ;
										print "</th>" ;
										print "<th>" ;
											print "Actions" ;
										print "</th>" ;
									print "</tr>" ;
								
									try {
										$dataContributions=array("higherEducationReferenceID"=>$row["higherEducationReferenceID"]);  
										$sqlContributions="SELECT higherEducationReferenceComponent.*, preferredName, surname FROM higherEducationReferenceComponent JOIN gibbonPerson ON (higherEducationReferenceComponent.gibbonPersonID=gibbonPerson.gibbonPersonID) WHERE higherEducationReferenceID=:higherEducationReferenceID ORDER BY title" ; 
										$resultContributions=$connection2->prepare($sqlContributions);
										$resultContributions->execute($dataContributions); 
									}
									catch(PDOException $e) { }
									
									if ($resultContributions->rowCount()<1) {
										print "<tr class='even'>" ;
											print "<td colspan=2>" ;
												print "<i>Error: no referee requested, or a system error.</i>" ;
											print "</td>" ;
										print "</tr>" ;
									}
									else {
										$count=0;
										$rowNum="odd" ;
										while ($rowContributions=$resultContributions->fetch()) {
											if ($count%2==0) {
												$rowNum="even" ;
											}
											else {
												$rowNum="odd" ;
											}
											$count++ ;
											
											print "<tr class='$rowNum'>" ;
												print "<td>" ;
													print formatName("", $rowContributions["preferredName"], $rowContributions["surname"], "Staff", false, true) ;
												print "</td>" ;
												print "<td style='width: 25px'>" ;
													if ($rowContributions["status"]=="Complete") {
														print "<img style='margin-right: 3px; float: left' title='Complete' src='./themes/" . $_SESSION[$guid]["gibbonThemeName"] . "/img/iconTick.png'/> " ;
													}
													else {
														print "<img style='margin-right: 3px; float: left' title='In Progress' src='./themes/" . $_SESSION[$guid]["gibbonThemeName"] . "/img/iconTick_light.png'/> " ;
													}
												print "</td>" ;
												print "<td>" ;
													print "<b>" . $rowContributions["status"] . "</b>" ;
												print "</td>" ;
												print "<td>" ;
													print $rowContributions["type"] ;
												print "</td>" ;
												print "<td>" ;
													if ($rowContributions["title"]=="") {
														print "<i>NA</i>" ;
													}
													else {
														print $rowContributions["title"] ;
													}
												print "</td>" ;
												print "<td>" ;
													print "<script type='text/javascript'>" ;	
														print "$(document).ready(function(){" ;
															print "\$(\".description-$count\").hide();" ;
															print "\$(\".show_hide-$count\").fadeIn(1000);" ;
															print "\$(\".show_hide-$count\").click(function(){" ;
															print "\$(\".description-$count\").fadeToggle(1000);" ;
															print "});" ;
														print "});" ;
													print "</script>" ;
													print "<a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . $_SESSION[$guid]["module"] . "/references_manage_edit_contribution_edit.php&higherEducationReferenceComponentID=" . $rowContributions["higherEducationReferenceComponentID"] . "&higherEducationReferenceID=$higherEducationReferenceID&gibbonSchoolYearID=$gibbonSchoolYearID'><img title='Edit' src='./themes/" . $_SESSION[$guid]["gibbonThemeName"] . "/img/config.png'/></a> " ;
													print "<a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . $_SESSION[$guid]["module"] . "/references_manage_edit_contribution_delete.php&higherEducationReferenceComponentID=" . $rowContributions["higherEducationReferenceComponentID"] . "&higherEducationReferenceID=$higherEducationReferenceID&gibbonSchoolYearID=$gibbonSchoolYearID'><img title='Delete' src='./themes/" . $_SESSION[$guid]["gibbonThemeName"] . "/img/garbage.png'/></a> " ;
													if ($rowContributions["status"]!="Pending") {
														print "<a class='show_hide-$count' onclick='false' href='#'><img style='padding-right: 5px' src='" . $_SESSION[$guid]["absoluteURL"] . "/themes/Default/img/page_down.png' alt='Show Details' onclick='return false;' /></a>" ;
													}
												print "</td>" ;
											print "</tr>" ;
											if ($rowContributions["status"]!="Pending") {
												print "<tr class='description-$count' id='fields-$count' style='background-color: #fff; display: none'>" ;
													print "<td colspan=6>" ;
														print $rowContributions["body"] ;
													print "</td>" ;
												print "</tr>" ;
											}
										}
									}
								print "</table>" ;
								?>
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