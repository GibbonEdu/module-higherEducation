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

if (isActionAccessible($guid, $connection2, "/modules/Higher Education/references_write_edit.php")==FALSE) {
	//Acess denied
	print "<div class='error'>" ;
		print "You do not have access to this action." ;
	print "</div>" ;
}
else {
	//Proceed!
	print "<div class='trail'>" ;
	print "<div class='trailHead'><a href='" . $_SESSION[$guid]["absoluteURL"] . "'>Home</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/" . getModuleEntry($_GET["q"], $connection2, $guid) . "'>" . getModuleName($_GET["q"]) . "</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/references_write.php'>Write References</a> > </div><div class='trailEnd'>Write Reference</div>" ;
	print "</div>" ;
	
	$updateReturn = $_GET["updateReturn"] ;
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
	
	//Check if school year specified
	$higherEducationReferenceComponentID=$_GET["higherEducationReferenceComponentID"];
	if ($higherEducationReferenceComponentID=="") {
		print "<div class='error'>" ;
			print "You have not specified a reference." ;
		print "</div>" ;
	}
	else {
		try {
			$data=array("higherEducationReferenceComponentID"=>$higherEducationReferenceComponentID);  
			$sql="SELECT higherEducationReference.gibbonPersonID AS gibbonPersonIDStudent, preferredName, surname, higherEducationReference.type as refType, higherEducationReference.notes, higherEducationReferenceComponent.* FROM higherEducationReferenceComponent JOIN higherEducationReference ON (higherEducationReferenceComponent.higherEducationReferenceID=higherEducationReference.higherEducationReferenceID) JOIN gibbonPerson ON (higherEducationReference.gibbonPersonID=gibbonPerson.gibbonPersonID) WHERE higherEducationReferenceComponentID=:higherEducationReferenceComponentID" ; 
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
			?>
			<form method="post" action="<? print $_SESSION[$guid]["absoluteURL"] . "/modules/" . $_SESSION[$guid]["module"] . "/references_write_editProcess.php?higherEducationReferenceComponentID=$higherEducationReferenceComponentID" ?>">
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
							<input readonly name="student" id="student" maxlength=255 value="<? print formatName("", htmlPrep($row["preferredName"]), htmlPrep($row["surname"]), "Student", false, false) ?>" type="text" style="width: 300px">
						</td>
					</tr>
					<tr>
						<td> 
							<b>Type *</b><br/>
							<span style="font-size: 90%"><i>This value cannot be changed.</i></span>
						</td>
						<td class="right">
							<input readonly name="refType" id="refType" maxlength=255 value="<? print $row["refType"] ?>" type="text" style="width: 300px">
						</td>
					</tr>
					<tr>
						<td colspan=2 style='padding-top: 15px;'> 
							<b>Reference Notes</b><br/>
							<span style="font-size: 90%"><i>Information about this reference shared by the student. This value cannot be changed.</i></span><br/>
							<textarea readonly name="notes" id="notes" rows=4 style="width:738px; margin: 5px 0px 0px 0px"><? print $row["notes"] ?></textarea>
						</td>
					</tr>
					
					<tr class='break'>
						<td colspan=2> 
							<h3>Useful Information</h3>
						</td>
					</tr>
					<tr>
						<td> 
							<b>Academic</b><br/>
						</td>
						<td class="right">
							<a target='_blank' href='<? print $_SESSION[$guid]["absoluteURL"] ?>/index.php?q=/modules/Students/student_view_details.php&gibbonPersonID=<? print $row["gibbonPersonIDStudent"] ?>&subpage=Markbook'>Markbook</a> | <a target='_blank' href='<? print $_SESSION[$guid]["absoluteURL"] ?>/index.php?q=/modules/Students/student_view_details.php&gibbonPersonID=<? print $row["gibbonPersonIDStudent"] ?>&subpage=External Assessment'>External Assessment</a>
						</td>
					</tr>
					<tr>
						<td> 
							<b>Co-curricular</b><br/>
						</td>
						<td class="right">
							<a target='_blank' href='<? print $_SESSION[$guid]["absoluteURL"] ?>/index.php?q=/modules/Students/student_view_details.php&gibbonPersonID=<? print $row["gibbonPersonIDStudent"] ?>&subpage=Activities'>Activities</a>
							<?
								$gibbonModuleID=checkModuleReady("/modules/IB Diploma/index.php", $connection2);
								if ($gibbonModuleID!=FALSE) {
									try {
										$dataAction=array("gibbonModuleID"=>$gibbonModuleID, "actionName"=>"View CAS in Student Profile", "gibbonRoleID"=>$_SESSION[$guid]["gibbonRoleIDCurrent"]); 
										$sqlAction="SELECT gibbonAction.name FROM gibbonAction JOIN gibbonPermission ON (gibbonAction.gibbonActionID=gibbonPermission.gibbonActionID) JOIN gibbonRole ON (gibbonPermission.gibbonRoleID=gibbonRole.gibbonRoleID) WHERE (gibbonAction.name=:actionName) AND (gibbonPermission.gibbonRoleID=:gibbonRoleID) AND gibbonAction.gibbonModuleID=:gibbonModuleID" ;
										$resultAction=$connection2->prepare($sqlAction);
										$resultAction->execute($dataAction);
									}
									catch(PDOException $e) { }
									if ($resultAction->rowCount()>0) {
										try {
											$dataHooks=array(); 
											$sqlHooks="SELECT * FROM gibbonHook WHERE type='Student Profile' AND name='IB Diploma CAS'" ;
											$resultHooks=$connection2->prepare($sqlHooks);
											$resultHooks->execute($dataHooks);
										}
										catch(PDOException $e) { }
										if ($resultHooks->rowCount()==1) {
											$rowHooks=$resultHooks->fetch() ;
											$options=unserialize($rowHooks["options"]) ;
											//Check for permission to hook
											try {
												$dataHook=array("gibbonRoleIDCurrent"=>$_SESSION[$guid]["gibbonRoleIDCurrent"], "sourceModuleName"=>$options["sourceModuleName"]); 
												$sqlHook="SELECT gibbonHook.name, gibbonModule.name AS module, gibbonAction.name AS action FROM gibbonHook JOIN gibbonModule ON (gibbonModule.name='" . $options["sourceModuleName"] . "') JOIN gibbonAction ON (gibbonAction.name='" . $options["sourceModuleAction"] . "') JOIN gibbonPermission ON (gibbonPermission.gibbonActionID=gibbonAction.gibbonActionID) WHERE gibbonAction.gibbonModuleID=(SELECT gibbonModuleID FROM gibbonModule WHERE gibbonPermission.gibbonRoleID=:gibbonRoleIDCurrent AND name=:sourceModuleName) AND gibbonHook.type='Student Profile' ORDER BY name" ;
												$resultHook=$connection2->prepare($sqlHook);
												$resultHook->execute($dataHook);
											}
											catch(PDOException $e) { }
											if ($resultHook->rowCount()==1) {
												print " | <a target='_blank' href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/Students/student_view_details.php&gibbonPersonID=" . $row["gibbonPersonIDStudent"] . "&hook=" . $rowHooks["name"] . "&module=" . $options["sourceModuleName"] . "&action=" . $options["sourceModuleAction"] . "&gibbonHookID=" . $rowHooks["gibbonHookID"] . "'>" . $rowHooks["name"] . "</a>" ;
											}
										}
									}
							 		
							 	}
							?>
						</td>
					</tr>
					<tr>
						<td> 
							<b>Miscellaneous</b><br/>
						</td>
						<td class="right">
							<a target='_blank' href='<? print $_SESSION[$guid]["absoluteURL"] ?>/index.php?q=/modules/Students/student_view_details.php&gibbonPersonID=<? print $row["gibbonPersonIDStudent"] ?>&subpage=Behaviour'>Behaviour</a> | <a target='_blank' href='<? print $_SESSION[$guid]["absoluteURL"] ?>/index.php?q=/modules/Students/student_view_details.php&gibbonPersonID=<? print $row["gibbonPersonIDStudent"] ?>&subpage=School Attendance'>Attendance</a>
						</td>
					</tr>
					<?
					try {
						$dataNotes=array("gibbonPersonID"=>$row["gibbonPersonIDStudent"]);  
						$sqlNotes="SELECT * FROM higherEducationStudent WHERE gibbonPersonID=:gibbonPersonID" ; 
						$resultNotes=$connection2->prepare($sqlNotes);
						$resultNotes->execute($dataNotes); 
					}
					catch(PDOException $e) { 
						print "<tr>" ;
							print "<td colspan=2>" ;
								print "<div class='error'>" . $e->getMessage() . "</div>" ;
							print "</td>" ; 
						print "</tr>" ;
					}
					if ($resultNotes->rowCount()==1) {
						$rowNotes=$resultNotes->fetch() ;
						?>
						<tr>
							<td colspan=2 style='padding-top: 15px;'> 
								<b>Higher Education Notes</b><br/>
								<span style="font-size: 90%"><i>Information about higher education in general shared by the student. This value cannot be changed.</i></span><br/>
								<div style="padding: 1px; background-color: #e2e2e2; border: 1px solid #BFBFBF; min-height: 74px; width:738px; margin: 5px 0px 0px 0px"><? print $rowNotes["referenceNotes"] ?></div>
							</td>
						</tr>
						<?
					}
					?>
					
					
					<tr class='break'>
						<td colspan=2> 
							<h3>Your Contribution</h3>
						</td>
					</tr>
					<tr>
						<td> 
							<b>Type *</b><br/>
							<span style="font-size: 90%"><i>This value cannot be changed.</i></span>
						</td>
						<td class="right">
							<input readonly name="type" id="type" maxlength=255 value="<? print $row["type"] ?>" type="text" style="width: 300px">
						</td>
					</tr>
					<?
					if ($row["title"]!="") {
						?>
						<tr>
							<td> 
								<b>Title *</b><br/>
								<span style="font-size: 90%"><i>This value cannot be changed.</i></span>
							</td>
							<td class="right">
								<input readonly name="title" id="title" maxlength=255 value="<? print $row["title"] ?>" type="text" style="width: 300px">
							</td>
						</tr>
						<?
					}
					?>
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
								var body = new LiveValidation('body');
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
					
					<?
					try {
						$dataContributions=array("higherEducationReferenceID"=>$row["higherEducationReferenceID"], "higherEducationReferenceComponentID"=>$higherEducationReferenceComponentID);  
						$sqlContributions="SELECT higherEducationReferenceComponent.*, preferredName, surname FROM higherEducationReferenceComponent JOIN gibbonPerson ON (higherEducationReferenceComponent.gibbonPersonID=gibbonPerson.gibbonPersonID) WHERE higherEducationReferenceID=:higherEducationReferenceID AND NOT higherEducationReferenceComponentID=:higherEducationReferenceComponentID ORDER BY title" ; 
						$resultContributions=$connection2->prepare($sqlContributions);
						$resultContributions->execute($dataContributions); 
					}
					catch(PDOException $e) { }
					
					if ($resultContributions->rowCount()>0) {
						?>
						<tr>
							<td colspan=2> 
								<h3>Other Contributions</h3>
								<?
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
												if ($rowContributions["status"]!="Pending") {
													print "<a class='show_hide-$count' onclick='false' href='#'><img style='padding-right: 5px' src='" . $_SESSION[$guid]["absoluteURL"] . "/themes/Default/img/page_down.png' alt='Show Details' onclick='return false;' /></a>" ;
												}
											print "</td>" ;
										print "</tr>" ;
										if ($rowContributions["status"]!="Pending") {
											print "<tr class='description-$count' id='fields-$count' style='background-color: #fff; display: none'>" ;
												print "<td style='border-bottom: 1px solid #333' colspan=6>" ;
													print $rowContributions["body"] ;
												print "</td>" ;
											print "</tr>" ;
										}
									}
								print "</table>" ;
								?>
							</td>
						</tr>
						<?
					}
					?>
					
					<tr>
						<td>
							<span style="font-size: 90%"><i>* denotes a required field</i></span>
						</td>
						<td class="right">
							<input type="hidden" name="address" value="<? print $_SESSION[$guid]["address"] ?>">
							<input type="reset" value="Reset"> <input type="submit" value="Submit">
						</td>
					</tr>
				</table>
			</form>
			<?
		}	
	}
}
?>