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

if (isActionAccessible($guid, $connection2, "/modules/Higher Education/applications_track_edit.php")==FALSE) {
	//Acess denied
	print "<div class='error'>" ;
		print "You do not have access to this action." ;
	print "</div>" ;
}
else {
	//Proceed!
	print "<div class='trail'>" ;
	print "<div class='trailHead'><a href='" . $_SESSION[$guid]["absoluteURL"] . "'>Home</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/" . getModuleEntry($_GET["q"], $connection2, $guid) . "'>" . getModuleName($_GET["q"]) . "</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/applications_track.php'>Track Applications</a> > </div><div class='trailEnd'>Edit Application</div>" ; 
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
					<form method="post" action="<? print $_SESSION[$guid]["absoluteURL"] . "/modules/" . $_SESSION[$guid]["module"] . "/applications_track_editProcess.php?higherEducationApplicationInstitutionID=$higherEducationApplicationInstitutionID" ?>">
						<table style="width: 100%">	
							<tr><td style="width: 30%"></td><td></td></tr>
							<tr>
								<td colspan=2> 
									<h3 class='top'>Application Information</h3>
								</td>
							</tr>
							<tr>
								<td> 
									<b>Institution *</b><br/>
								</td>
								<td class="right">
									<select name="higherEducationInstitutionID" id="higherEducationInstitutionID" style="width: 302px">
										<?
										print "<option value='Please select...'>Please select...</option>" ;
										try {
											$dataSelect=array(); 
											$sqlSelect="SELECT * FROM higherEducationInstitution WHERE active='Y' ORDER BY name" ;
											$resultSelect=$connection2->prepare($sqlSelect);
											$resultSelect->execute($dataSelect);
										}
										catch(PDOException $e) { }
										while ($rowSelect=$resultSelect->fetch()) {
											$selected="" ;
											if ($rowSelect["higherEducationInstitutionID"]==$row["higherEducationInstitutionID"]) {
												$selected="selected" ;
											}
											print "<option $selected value='" . $rowSelect["higherEducationInstitutionID"] . "'>" . htmlPrep($rowSelect["name"]) . " (" . htmlPrep($rowSelect["country"]) . ")</option>" ;
										}		
										?>				
									</select>
									<script type="text/javascript">
										var higherEducationInstitutionID = new LiveValidation('higherEducationInstitutionID');
										higherEducationInstitutionID.add(Validate.Exclusion, { within: ['Please select...'], failureMessage: "Select something!"});
									 </script>
								</td>
							</tr>
							<tr>
								<td> 
									<b>Major/Course *</b><br/>
								</td>
								<td class="right">
									<select name="higherEducationMajorID" id="higherEducationMajorID" style="width: 302px">
										<?
										print "<option value='Please select...'>Please select...</option>" ;
										try {
											$dataSelect=array(); 
											$sqlSelect="SELECT * FROM higherEducationMajor WHERE active='Y' ORDER BY name" ;
											$resultSelect=$connection2->prepare($sqlSelect);
											$resultSelect->execute($dataSelect);
										}
										catch(PDOException $e) { }
										while ($rowSelect=$resultSelect->fetch()) {
											$selected="" ;
											if ($rowSelect["higherEducationMajorID"]==$row["higherEducationMajorID"]) {
												$selected="selected" ;
											}
											print "<option $selected value='" . $rowSelect["higherEducationMajorID"] . "'>" . htmlPrep($rowSelect["name"]) . "</option>" ;
										}		
										?>				
									</select>
									<script type="text/javascript">
										var higherEducationMajorID = new LiveValidation('higherEducationMajorID');
										higherEducationMajorID.add(Validate.Exclusion, { within: ['Please select...'], failureMessage: "Select something!"});
									 </script>
								</td>
							</tr>
							<tr>
								<td> 
									<b>Application Number</b><br/>
									<span style="font-size: 90%"><i>Official number for your application (given by institution, UCAS, etc).</i></span>
								</td>
								<td class="right">
									<input name="applicationNumber" id="applicationNumber" maxlength=50 value="<? print $row["applicationNumber"] ?>" type="text" style="width: 300px">
								</td>
							</tr>
							<tr>
								<td> 
									<b>Rank</b><br/>
									<span style="font-size: 90%"><i>Order all your applications. 1 should be your most favoured application.</i></span>
								</td>
								<td class="right">
									<select name="rank" id="rank" style="width: 302px">
										<?
										print "<option value=''></option>" ;
										for ($i=1; $i<11; $i++) {
											$selected="" ;
											if ($i==$row["rank"]) {
												$selected="selected" ;
											}
											print "<option $selected value='$i'>$i</option>" ;
										}
										?>				
									</select>
								</td>
							</tr>
							<tr>
								<td> 
									<b>Rating</b><br/>
									<span style="font-size: 90%"><i>How likely is it that you will get into this institution?</i></span>
								</td>
								<td class="right">
									<select name="rating" id="rating" style="width: 302px">
										<option <? if ($row["rating"]=="") { print "selected" ; } ?> value=""></option>
										<option <? if ($row["rating"]=="High Reach") { print "selected" ; } ?> value="High Reach">High Reach</option>
										<option <? if ($row["rating"]=="Reach") { print "selected" ; } ?> value="Reach">Reach</option>
										<option <? if ($row["rating"]=="Mid") { print "selected" ; } ?> value="Mid">Mid</option>
										<option <? if ($row["rating"]=="Safe") { print "selected" ; } ?> value="Safe">Safe</option>		
									</select>
								</td>
							</tr>
							<tr>
								<td colspan=2 style='padding-top: 15px;'> 
									<b>Application Question</b><br/>
									<span style="font-size: 90%"><i>If the application form has a question, enter it here.</i></span><br/>
									<textarea name="question" id="question" rows=4 style="width:756px; margin: 5px 0px 0px 0px"><? print htmlPrep($row["question"]) ?></textarea>
								</td>
							</tr>
							<tr>
								<td colspan=2 style='padding-top: 15px;'> 
									<b>Application Answer</b><br/>
									<span style="font-size: 90%"><i>Answer the above question here.</i></span><br/>
									<textarea name="answer" id="answer" rows=14 style="width:756px; margin: 5px 0px 0px 0px"><? print htmlPrep($row["answer"]) ?></textarea>
								</td>
							</tr>
							<tr>
								<td colspan=2 style='padding-top: 15px;'> 
									<b>Scholarship Details</b><br/>
									<span style="font-size: 90%"><i>Have you applied for a scholarship? If so, list the details below.</i></span><br/>
									<textarea name="scholarship" id="scholarship" rows=4 style="width:756px; margin: 5px 0px 0px 0px"><? print htmlPrep($row["scholarship"]) ?></textarea>
								</td>
							</tr>
							
							<tr>
								<td colspan=2> 
									<h3>Status & Offers</h3>
								</td>
							</tr>
							<tr>
								<td> 
									<b>Status</b><br/>
									<span style="font-size: 90%"><i>Where are you in the application process?</i></span>
								</td>
								<td class="right">
									<select name="status" id="status" style="width: 302px">
										<option <? if ($row["status"]=="") { print "selected" ; } ?> value=""></option>
										<option <? if ($row["status"]=="Not Yet Started") { print "selected" ; } ?> value="Not Yet Started">Not Yet Started</option>
										<option <? if ($row["status"]=="Researching") { print "selected" ; } ?> value="Researching">Researching</option>
										<option <? if ($row["status"]=="Started") { print "selected" ; } ?> value="Started">Started</option>
										<option <? if ($row["status"]=="Passed To Careers Office") { print "selected" ; } ?> value="Passed To Careers Office">Passed To Careers Office</option>
										<option <? if ($row["status"]=="Completed") { print "selected" ; } ?> value="Completed">Completed</option>
										<option <? if ($row["status"]=="Application Sent") { print "selected" ; } ?> value="Application Sent">Application Sent</option>
										<option <? if ($row["status"]=="Offer/Acceptance Received") { print "selected" ; } ?> value="Offer/Acceptance Received">Offer/Acceptance Received</option>
										<option <? if ($row["status"]=="Rejection Received") { print "selected" ; } ?> value="Rejection Received">Rejection Received</option>
										<option <? if ($row["status"]=="Offer Denied") { print "selected" ; } ?> value="Offer Denied">Offer Denied</option>
										<option <? if ($row["status"]=="Deposit Paid/Offer Accepted") { print "selected" ; } ?> value="Deposit Paid/Offer Accepted">Deposit Paid/Offer Accepted</option>
										<option <? if ($row["status"]=="Enrolling") { print "selected" ; } ?> value="Enrolling">Enrolling</option>	
									</select>
								</td>
							</tr>
							<tr>
								<td> 
									<b>Offer</b><br/>
									<span style="font-size: 90%"><i>If you have received an offer or rejection, select relevant option below:</i></span>
								</td>
								<td class="right">
									<select name="offer" id="offer" style="width: 302px">
										<option <? if ($row["offer"]=="") { print "selected" ; } ?> value=""></option>
										<option <? if ($row["offer"]=="First Choice") { print "selected" ; } ?> value="First Choice">Yes - First Choice</option>
										<option <? if ($row["offer"]=="Backup") { print "selected" ; } ?> value="Backup">Yes - Backup Choice</option>	
										<option <? if ($row["offer"]=="Y") { print "selected" ; } ?> value="Y">Yes - Other</option>	
										<option <? if ($row["offer"]=="N") { print "selected" ; } ?> value="N">No</option>		
									</select>
								</td>
							</tr>
							<tr>
								<td colspan=2 style='padding-top: 15px;'> 
									<b>Offer Details</b><br/>
									<span style="font-size: 90%"><i>If you have received an offer, enter details here.</i></span><br/>
									<textarea name="offerDetails" id="offerDetails" rows=4 style="width:756px; margin: 5px 0px 0px 0px"><? print htmlPrep($row["offerDetails"]) ?></textarea>
								</td>
							</tr>
							<tr>
								<td class="right" colspan=2>
									<input name="gibbonCourseID" id="gibbonCourseID" value="<? print $gibbonCourseID ?>" type="hidden">
									<input name="gibbonSchoolYearID" id="gibbonSchoolYearID" value="<? print $gibbonSchoolYearID ?>" type="hidden">
									<input type="hidden" name="address" value="<? print $_SESSION[$guid]["address"] ?>">
									<input type="reset" value="Reset"> <input type="submit" value="Submit">
								</td>
							</tr>
							<tr>
								<td class="right" colspan=2>
									<span style="font-size: 90%"><i>* denotes a required field</i></span>
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