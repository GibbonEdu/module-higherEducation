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

//Module includes
include "./modules/" . $_SESSION[$guid]["module"] . "/moduleFunctions.php" ;

if (isActionAccessible($guid, $connection2, "/modules/Higher Education/applications_track.php")==FALSE) {
	//Acess denied
	print "<div class='error'>" ;
		print "You do not have access to this action." ;
	print "</div>" ;
}
else {
	
	//Proceed!
	print "<div class='trail'>" ;
	print "<div class='trailHead'><a href='" . $_SESSION[$guid]["absoluteURL"] . "'>Home</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/" . getModuleEntry($_GET["q"], $connection2, $guid) . "'>" . getModuleName($_GET["q"]) . "</a> > </div><div class='trailEnd'>Track Application</div>" ;
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
	
	if (studentEnrolment($_SESSION[$guid]["gibbonPersonID"], $connection2)==FALSE) {
		print "<div class='error'>" ;
			print "You have not been enrolled for higher education applications." ;
		print "</div>" ;
	}
	else {
		print "<p>" ;
			print "Use this page to provide relevant information about your higher education application intentions and progress. This information will be used to guide you through this process." ;
		print "</p>" ;				
		
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
			print "<div class='warning'>" ;
				print "It appears that you are new to application tracking via the Higher Education module. Please enter your details below, and press the Submit button once you are done. You can reenter details into this page at any time." ;
			print "</div>" ;
		}
		else {
			$row=$result->fetch() ;
		}
		
		//Create application record
		?>
		<script type="text/javascript">
			/* Controls for showing/hiding fields */
			$(document).ready(function(){
				<?
				if (isset($row["applying"])) { 
					if ($row["applying"]=="N") {
						?>
						$("#applicationsDiv").css("display", "none");
						$("#careerInterestsRow").css("display", "none");
						$("#coursesMajorsRow").css("display", "none");
						$("#otherScoresRow").css("display", "none");
						$("#personalStatementRow").css("display", "none");
						$("#meetingNotesRow").css("display", "none");
						<?
					}
				}
				else if (isset($row["applying"])==FALSE) { 
					?>
					$("#applicationsDiv").css("display", "none");
					$("#careerInterestsRow").css("display", "none");
					$("#coursesMajorsRow").css("display", "none");
					$("#otherScoresRow").css("display", "none");
					$("#personalStatementRow").css("display", "none");
					$("#meetingNotesRow").css("display", "none");
					<?
				}
				?>
						
				$("#applying").change(function(){
					if ($('#applying option:selected').val() == "Y" ) {
						$("#applicationsDiv").slideDown("fast", $("#applicationsDiv").css("{'display' : 'table-row'}")); //Slide Down Effect
						$("#careerInterestsRow").slideDown("fast", $("#careerInterestsRow").css("{'display' : 'table-row'}")); //Slide Down Effect
						$("#coursesMajorsRow").slideDown("fast", $("#coursesMajorsRow").css("{'display' : 'table-row'}")); //Slide Down Effect
						$("#otherScoresRow").slideDown("fast", $("#otherScoresRow").css("{'display' : 'table-row'}")); //Slide Down Effect
						$("#personalStatementRow").slideDown("fast", $("#personalStatementRow").css("{'display' : 'table-row'}")); //Slide Down Effect
						$("#meetingNotesRow").slideDown("fast", $("#meetingNotesRow").css("{'display' : 'table-row'}")); //Slide Down Effect
					} 
					else {
						$("#applicationsDiv").slideUp("fast"); //Slide Down Effect
						$("#careerInterestsRow").slideUp("fast"); //Slide Down Effect
						$("#coursesMajorsRow").slideUp("fast"); //Slide Down Effect
						$("#otherScoresRow").slideUp("fast"); //Slide Down Effect
						$("#personalStatementRow").slideUp("fast"); //Slide Down Effect
						$("#meetingNotesRow").slideUp("fast"); //Slide Down Effect
					}
				 });
			});
		</script>
		<?
		$higherEducationApplicationID=NULL ;
		if (isset($row["higherEducationApplicationID"])) {
			$higherEducationApplicationID=$row["higherEducationApplicationID"] ;
		}
		?>
		<form method="post" action="<? print $_SESSION[$guid]["absoluteURL"] . "/modules/" . $_SESSION[$guid]["module"] . "/applications_trackProcess.php?higherEducationApplicationID=" . $higherEducationApplicationID ?>">
		<table class='smallIntBorder' cellspacing='0' style="width: 100%">	
			<tr>
				<td> 
					<b>Applying?</b><br/>
					<span style="font-size: 90%"><i>Are you intending on applying for entry to higher education?</i></span>
				</td>
				<td class="right">
					<select style="width: 302px" name="applying" id="applying">
						<option value='N' <? if (isset($row["applying"])) { if ($row["applying"]=="N") { print "selected" ; } } ?>>N</option>	
						<option value='Y' <? if (isset($row["applying"])) { if ($row["applying"]=="Y") { print "selected" ; } } ?>>Y</option>		
					</select>
				</td>
			</tr>
			<tr id='careerInterestsRow' <? if (isset($row["applying"])) { if ($row["applying"]=="N" OR $row["applying"]=="") { print "style='display: none;'" ; } } ?>>
				<td colspan=2 style='padding-top: 15px;'> 
					<b>Career Interests</b><br/>
					<span style="font-size: 90%"><i>What areas of work are you interested in? What are your ambitions?</i></span><br/>
					<textarea name="careerInterests" id="careerInterests" rows=8 style="width:738px; margin: 5px 0px 0px 0px"><? if (isset($row["careerInterests"])) { print htmlPrep($row["careerInterests"]) ; } ?></textarea>
				</td>
			</tr>
			<tr id='coursesMajorsRow' <? if (isset($row["applying"])) { if ($row["applying"]=="N" OR $row["applying"]=="") { print "style='display: none;'" ; } } ?>>
				<td colspan=2 style='padding-top: 15px;'> 
					<b>Courses/Majors</b><br/>
					<span style="font-size: 90%"><i>What areas of study are you interested in? How do these relate to your career interests?</i></span><br/>
					<textarea name="coursesMajors" id="coursesMajors" rows=8 style="width:738px; margin: 5px 0px 0px 0px"><? if (isset($row["coursesMajors"])) {  print htmlPrep($row["coursesMajors"]) ; } ?></textarea>
				</td>
			</tr>
			<tr id='otherScoresRow' <? if (isset($row["applying"])) { if ($row["applying"]=="N" OR $row["applying"]=="") { print "style='display: none;'" ; } } ?>>
				<td colspan=2 style='padding-top: 15px;'> 
					<b>Scores</b><br/>
					<span style="font-size: 90%"><i>Do you have any non-<? print $_SESSION[$guid]["organisationNameShort"] ?> exam scores?</i></span><br/>
					<textarea name="otherScores" id="otherScores" rows=8 style="width:738px; margin: 5px 0px 0px 0px"><? if (isset($row["otherScores"])) {   print htmlPrep($row["otherScores"]) ; } ?></textarea>
				</td>
			</tr>
			<tr id='personalStatementRow' <? if (isset($row["applying"])) { if ($row["applying"]=="N" OR $row["applying"]=="") { print "style='display: none;'" ; } } ?>>
				<td colspan=2 style='padding-top: 15px;'> 
					<b>Personal Statement</b><br/>
					<span style="font-size: 90%"><i>Draft out ideas for your personal statement.</i></span><br/>
					<textarea name="personalStatement" id="personalStatement" rows=8 style="width:738px; margin: 5px 0px 0px 0px"><? if (isset($row["personalStatement"])) {   print htmlPrep($row["personalStatement"]) ; } ?></textarea>
				</td>
			</tr>
			<tr id='meetingNotesRow' <? if (isset($row["applying"])) { if ($row["applying"]=="N" OR $row["applying"]=="") { print "style='display: none;'" ; } } ?>>
				<td colspan=2 style='padding-top: 15px;'> 
					<b>Meeting notes</b><br/>
					<span style="font-size: 90%"><i>Take notes on any meetings you have regarding your application process.</i></span><br/>
					<textarea name="meetingNotes" id="meetingNotes" rows=8 style="width:738px; margin: 5px 0px 0px 0px"><? if (isset($row["meetingNotes"])) {   print htmlPrep($row["meetingNotes"]) ; } ?></textarea>
				</td>
			</tr>
			
			<tr>
				<td>
					<span style="font-size: 90%"><i>* denotes a required field</i></span>
				</td>
				<td class="right">
					<input type="hidden" name="address" value="<? print $_SESSION[$guid]["address"] ?>">
					<input type="submit" value="Submit">
				</td>
			</tr>
		</table>
		</form>
		<?
		
		$style="" ;
		if (isset($row["applying"])) { if ($row["applying"]=="N" OR $row["applying"]=="") { $style="display: none;" ; } }
		print "<div id='applicationsDiv' style='$style'>" ;
			print "<h2>" ;
			print "Application To Institutions" ;
			print "</h2>" ;
			
			
			if (isset($row["higherEducationApplicationID"])==FALSE) {
				print "<div class='warning'>" ;
				print "You need to save the information above (press the Submit button) before you can start adding applications." ;
				print "</div>" ;
			}
			else {
				try {
					$dataApps=array("higherEducationApplicationID"=>$row["higherEducationApplicationID"]); 
					$sqlApps="SELECT higherEducationApplicationInstitution.higherEducationApplicationInstitutionID, higherEducationInstitution.name as institution, higherEducationMajor.name as major, rank, rating FROM higherEducationApplicationInstitution JOIN higherEducationInstitution ON (higherEducationApplicationInstitution.higherEducationInstitutionID=higherEducationInstitution.higherEducationInstitutionID) JOIN higherEducationMajor ON (higherEducationApplicationInstitution.higherEducationMajorID=higherEducationMajor.higherEducationMajorID) WHERE higherEducationApplicationID=:higherEducationApplicationID ORDER BY rank, institution, major" ; 
					$resultApps=$connection2->prepare($sqlApps);
					$resultApps->execute($dataApps);
				}
				catch(PDOException $e) { 
					print "<div class='error'>" . $e->getMessage() . "</div>" ; 
				}
				
				print "<div class='linkTop'>" ;
				print "<a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . $_SESSION[$guid]["module"] . "/applications_track_add.php'><img title='New' src='./themes/" . $_SESSION[$guid]["gibbonThemeName"] . "/img/page_new.gif'/></a>" ;
				print "</div>" ;
				
				if ($resultApps->rowCount()<1) {
					print "<div class='error'>" ;
					print "There are no applications to display." ;
					print "</div>" ;
				}
				else {
					print "<table cellspacing='0' style='width: 100%'>" ;
						print "<tr class='head'>" ;
							print "<th>" ;
								print "Institution" ;
							print "</th>" ;
							print "<th>" ;
								print "Major" ;
							print "</th>" ;
							print "<th>" ;
								print "Ranking<br/>" ;
								print "<span style='font-size: 75%; font-style: italic'>Rating</span>" ;
							print "</th>" ;
							print "<th>" ;
								print "Actions" ;
							print "</th>" ;
						print "</tr>" ;
						
						$count=0;
						$rowNum="odd" ;
						while ($rowApps=$resultApps->fetch()) {
							if ($count%2==0) {
								$rowNum="even" ;
							}
							else {
								$rowNum="odd" ;
							}
							
							//COLOR ROW BY STATUS!
							print "<tr class=$rowNum>" ;
								print "<td>" ;
									print $rowApps["institution"] ;
								print "</td>" ;
								print "<td>" ;
									print $rowApps["major"] ;
								print "</td>" ;
								print "<td>" ;
									print $rowApps["rank"] . "<br/>" ;
									print "<span style='font-size: 75%; font-style: italic'>" . $rowApps["rating"] . "</span>" ;
								print "</td>" ;
								print "<td>" ;
									print "<a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . $_SESSION[$guid]["module"] . "/applications_track_edit.php&higherEducationApplicationInstitutionID=" . $rowApps["higherEducationApplicationInstitutionID"] . "'><img title='Edit' src='./themes/" . $_SESSION[$guid]["gibbonThemeName"] . "/img/config.png'/></a> " ;
									print "<a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . $_SESSION[$guid]["module"] . "/applications_track_delete.php&higherEducationApplicationInstitutionID=" . $rowApps["higherEducationApplicationInstitutionID"] . "'><img title='Delete' src='./themes/" . $_SESSION[$guid]["gibbonThemeName"] . "/img/garbage.png'/></a> " ;
								print "</td>" ;
							print "</tr>" ;
							
							$count++ ;
						}
					print "</table>" ;
				}
			}
		print "</div>" ;
	}
}
?>