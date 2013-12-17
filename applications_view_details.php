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

@session_start() ;

//Module includes
include "./modules/" . $_SESSION[$guid]["module"] . "/moduleFunctions.php" ;

if (isActionAccessible($guid, $connection2, "/modules/Higher Education/applications_view_details.php")==FALSE) {
	//Acess denied
	print "<div class='error'>" ;
		print "You do not have access to this action." ;
	print "</div>" ;
}
else {
	$role=staffHigherEducationRole($_SESSION[$guid]["gibbonPersonID"], $connection2) ;
	if ($role==FALSE) {
		//Acess denied
		print "<div class='error'>" ;
			print "You are not enroled in the Higher Education programme." ;
		print "</div>" ;
	}
	else {
		$gibbonPersonID=$_GET["gibbonPersonID"] ;
		if ($gibbonPersonID=="") {
			print "<div class='error'>" ;
				print "You have not specified a student." ;
			print "</div>" ;
		}
		else {
			try {
				if ($role=="Coordinator") {
					$data=array("gibbonSchoolYearID"=>$_SESSION[$guid]["gibbonSchoolYearID"], "gibbonPersonID"=>$gibbonPersonID);  
					$sql="SELECT gibbonPerson.gibbonPersonID, higherEducationStudentID, surname, preferredName, image_240, gibbonYearGroup.nameShort AS yearGroup, gibbonRollGroup.nameShort AS rollGroup, gibbonRollGroup.gibbonRollGroupID, gibbonPersonIDAdvisor, gibbonSchoolYear.name AS schoolYear FROM higherEducationStudent JOIN gibbonPerson ON (higherEducationStudent.gibbonPersonID=gibbonPerson.gibbonPersonID) JOIN gibbonStudentEnrolment ON (higherEducationStudent.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID) LEFT JOIN gibbonSchoolYear ON (gibbonSchoolYear.gibbonSchoolYearID=gibbonPerson.gibbonSchoolYearIDClassOf) LEFT JOIN gibbonYearGroup ON (gibbonStudentEnrolment.gibbonYearGroupID=gibbonYearGroup.gibbonYearGroupID) LEFT JOIN gibbonRollGroup ON (gibbonStudentEnrolment.gibbonRollGroupID=gibbonRollGroup.gibbonRollGroupID) WHERE gibbonStudentEnrolment.gibbonSchoolYearID=:gibbonSchoolYearID AND gibbonPerson.status='Full' AND gibbonPerson.gibbonPersonID=:gibbonPersonID ORDER BY gibbonSchoolYear.sequenceNumber DESC, surname, preferredName" ; 
				}
				else {
					$data=array("gibbonSchoolYearID"=>$_SESSION[$guid]["gibbonSchoolYearID"], "advisor"=> $_SESSION[$guid]["gibbonPersonID"], "gibbonPersonID"=>$gibbonPersonID);  
					$sql="SELECT gibbonPerson.gibbonPersonID, higherEducationStudentID, surname, preferredName, image_240, , gibbonYearGroup.nameShort AS yearGroup, gibbonRollGroup.nameShort AS rollGroup, gibbonRollGroup.gibbonRollGroupID, gibbonPersonIDAdvisor, gibbonSchoolYear.name AS schoolYear FROM higherEducationStudent JOIN gibbonPerson ON (higherEducationStudent.gibbonPersonID=gibbonPerson.gibbonPersonID) JOIN gibbonStudentEnrolment ON (higherEducationStudent.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID) LEFT JOIN gibbonSchoolYear ON (gibbonSchoolYear.gibbonSchoolYearID=gibbonPerson.gibbonSchoolYearIDClassOf) LEFT JOIN gibbonYearGroup ON (gibbonStudentEnrolment.gibbonYearGroupID=gibbonYearGroup.gibbonYearGroupID) LEFT JOIN gibbonRollGroup ON (gibbonStudentEnrolment.gibbonRollGroupID=gibbonRollGroup.gibbonRollGroupID) WHERE gibbonStudentEnrolment.gibbonSchoolYearID=:gibbonSchoolYearID AND gibbonPerson.status='Full' AND gibbonPersonIDAdvisor=:advisor AND gibbonPerson.gibbonPersonID=:gibbonPersonID ORDER BY gibbonSchoolYear.sequenceNumber DESC, surname, preferredName" ; 
				}
				$result=$connection2->prepare($sql);
				$result->execute($data); 
			}
			catch(PDOException $e) { 
				print "<div class='error'>" . $e->getMessage() . "</div>" ; 
			}
		
			if ($result->rowCount()!=1) {
				print "<div class='error'>" ;
					print "The specified student does not exist, or you do not have access to them." ;
				print "</div>" ;
			}
			else {
				$row=$result->fetch() ;
				$image_240=$row["image_240"] ; 
				
				print "<div class='trail'>" ;
				print "<div class='trailHead'><a href='" . $_SESSION[$guid]["absoluteURL"] . "'>Home</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/" . getModuleEntry($_GET["q"], $connection2, $guid) . "'>" . getModuleName($_GET["q"]) . "</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/applications_view.php'>View Applications</a> > </div><div class='trailEnd'>Application Details</div>" ;
				print "</div>" ;
				
				print "<table class='smallIntBorder' cellspacing='0' style='width: 100%'>" ;
					print "<tr>" ;
						print "<td style='width: 34%; vertical-align: top'>" ;
							print "<span style='font-size: 115%; font-weight: bold'>Name</span><br/>" ;
							print formatName("", $row["preferredName"], $row["surname"], "Student", true, true) ;
						print "</td>" ;
						print "<td style='width: 34%; vertical-align: top'>" ;
							print "<span style='font-size: 115%; font-weight: bold'>Roll Group</span><br/>" ;
							try {
								$dataDetail=array("gibbonRollGroupID"=>$row["gibbonRollGroupID"]);  
								$sqlDetail="SELECT * FROM gibbonRollGroup WHERE gibbonRollGroupID=:gibbonRollGroupID" ;
								$resultDetail=$connection2->prepare($sqlDetail);
								$resultDetail->execute($dataDetail);
							}
							catch(PDOException $e) { 
								print "<div class='error'>" . $e->getMessage() . "</div>" ; 
							}
							if ($resultDetail->rowCount()==1) {
								$rowDetail=$resultDetail->fetch() ;
								print "<i>" . $rowDetail["name"] . "</i>" ;
							}
						print "</td>" ;
						print "<td style='width: 34%; vertical-align: top'>" ;
			
						print "</td>" ;
					print "</tr>" ;
				print "</table>" ;
				
				//Check for application record
				try {
					$data=array("gibbonPersonID"=>$gibbonPersonID); 
					$sql="SELECT * FROM  higherEducationApplication WHERE gibbonPersonID=:gibbonPersonID" ;
					$result=$connection2->prepare($sql);
					$result->execute($data);
				}
				catch(PDOException $e) { 
					print "<div class='error'>" . $e->getMessage() . "</div>" ; 
				}
			
				if ($result->rowCount()!=1) {
					print "<div class='warning'>" ;
						print "The selected student has not initiated the higher education application process." ;
					print "</div>" ;
				}
				else {
					$row=$result->fetch() ;
					
					if ($row["applying"]!="Y") {
						print "<div class='warning'>" ;
							print "The selected student is not applying for higher education." ;
						print "</div>" ;
					}
					else {
					
						//Create application record
						?>
						<form method="post" action="<? print $_SESSION[$guid]["absoluteURL"] . "/modules/" . $_SESSION[$guid]["module"] . "/applications_trackProcess.php?higherEducationApplicationID=" . $row["higherEducationApplicationID"] ?>">
						<table class='smallIntBorder' cellspacing='0' style="width: 100%">	
							<tr id='careerInterestsRow' <? if ($row["applying"]=="N" OR $row["applying"]=="") { print "style='display: none;'" ; } ?>>
								<td colspan=2 style='padding-top: 15px;'> 
									<b>Career Interests</b><br/>
									<span style="font-size: 90%"><i><b>Student asked</b>: What areas of work are you interested in? What are your ambitions?</i></span><br/>
									<textarea readonly name="careerInterests" id="careerInterests" rows=12 style="width:738px; margin: 5px 0px 0px 0px"><? print htmlPrep($row["careerInterests"]) ?></textarea>
								</td>
							</tr>
							<tr id='coursesMajorsRow' <? if ($row["applying"]=="N" OR $row["applying"]=="") { print "style='display: none;'" ; } ?>>
								<td colspan=2 style='padding-top: 15px;'> 
									<b>Courses/Majors</b><br/>
									<span style="font-size: 90%"><i><b>Student asked</b>: What areas of study are you interested in? How do these relate to your career interests?</i></span><br/>
									<textarea readonly name="coursesMajors" id="coursesMajors" rows=12 style="width:738px; margin: 5px 0px 0px 0px"><? print htmlPrep($row["coursesMajors"]) ?></textarea>
								</td>
							</tr>
							<tr id='otherScoresRow' <? if ($row["applying"]=="N" OR $row["applying"]=="") { print "style='display: none;'" ; } ?>>
								<td colspan=2 style='padding-top: 15px;'> 
									<b>Scores</b><br/>
									<span style="font-size: 90%"><i><b>Student asked</b>: Do you have any non-<? print $_SESSION[$guid]["organisationNameShort"] ?> exam scores?</i></span><br/>
									<textarea readonly name="otherScores" id="otherScores" rows=12 style="width:738px; margin: 5px 0px 0px 0px"><? print htmlPrep($row["otherScores"]) ?></textarea>
								</td>
							</tr>
							<tr id='personalStatementRow' <? if ($row["applying"]=="N" OR $row["applying"]=="") { print "style='display: none;'" ; } ?>>
								<td colspan=2 style='padding-top: 15px;'> 
									<b>Personal Statement</b><br/>
									<span style="font-size: 90%"><i><b>Student asked</b>: Draft out ideas for your personal statement.</i></span><br/>
									<textarea readonly name="personalStatement" id="personalStatement" rows=12 style="width:738px; margin: 5px 0px 0px 0px"><? print htmlPrep($row["personalStatement"]) ?></textarea>
								</td>
							</tr>
							<tr id='meetingNotesRow' <? if ($row["applying"]=="N" OR $row["applying"]=="") { print "style='display: none;'" ; } ?>>
								<td colspan=2 style='padding-top: 15px;'> 
									<b>Meeting notes</b><br/>
									<span style="font-size: 90%"><i><b>Student asked</b>: Take notes on any meetings you have regarding your application process.</i></span><br/>
									<textarea readonly name="meetingNotes" id="meetingNotes" rows=12 style="width:738px; margin: 5px 0px 0px 0px;"><? print htmlPrep($row["meetingNotes"]) ?></textarea>
								</td>
							</tr>
						</table>
						</form>
						<?
						
						$style="" ;
						if ($row["applying"]=="N" OR $row["applying"]=="") { $style="display: none;" ; }
						print "<div id='applicationsDiv' style='$style'>" ;
							print "<h2>" ;
							print "Application To Institutions" ;
							print "</h2>" ;
							
							
							if ($row["higherEducationApplicationID"]=="") {
								print "<div class='warning'>" ;
								print "You need to save the information above (press the Submit button) before you can start adding applications." ;
								print "</div>" ;
							}
							else {
								try {
									$dataApps=array("higherEducationApplicationID"=>$row["higherEducationApplicationID"]); 
									$sqlApps="SELECT higherEducationApplicationInstitution.higherEducationApplicationInstitutionID, higherEducationInstitution.name as institution, higherEducationMajor.name as major, higherEducationApplicationInstitution.* FROM higherEducationApplicationInstitution JOIN higherEducationInstitution ON (higherEducationApplicationInstitution.higherEducationInstitutionID=higherEducationInstitution.higherEducationInstitutionID) JOIN higherEducationMajor ON (higherEducationApplicationInstitution.higherEducationMajorID=higherEducationMajor.higherEducationMajorID) WHERE higherEducationApplicationID=:higherEducationApplicationID ORDER BY rank, institution, major" ; 
									$resultApps=$connection2->prepare($sqlApps);
									$resultApps->execute($dataApps);
								}
								catch(PDOException $e) { 
									print "<div class='error'>" . $e->getMessage() . "</div>" ; 
								}
								
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
												print "Status" ;
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
													print $rowApps["status"] ;
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
													print "<a class='show_hide-$count' onclick='false' href='#'><img style='padding-right: 5px' src='" . $_SESSION[$guid]["absoluteURL"] . "/themes/Default/img/page_down.png' alt='Show Details' onclick='return false;' /></a>" ;
												print "</td>" ;
											print "</tr>" ;
											print "<tr class='description-$count' id='fields-$count' style='background-color: #fff; display: none'>" ;
												print "<td colspan=5>" ;
													print "<table class='mini' cellspacing='0' style='width: 100%'>" ;
														print "<tr>" ;
															print "<td style='vertical-align: top'>" ;
																print "<b>Application Number</b>" ;
															print "</td>" ;
															print "<td style='vertical-align: top'>" ;
																if ($rowApps["applicationNumber"]=="") {
																	print "NA" ;
																}
																else {
																	print $rowApps["applicationNumber"] ;
																}
															print "</td>" ;
														print "</tr>" ;
														print "<tr>" ;
															print "<td style='vertical-align: top'>" ;
																print "<b>Scholarship Details</b>" ;
															print "</td>" ;
															print "<td style='vertical-align: top'>" ;
																if ($rowApps["scholarship"]=="") {
																	print "NA" ;
																}
																else {
																	print $rowApps["scholarship"] ;
																}
															print "</td>" ;
														print "</tr>" ;
														print "<tr>" ;
															print "<td style='vertical-align: top'>" ;
																print "<b>Offer</b>" ;
															print "</td>" ;
															print "<td style='vertical-align: top'>" ;
																if ($rowApps["offer"]=="") {
																	print "NA" ;
																}
																else {
																	print $rowApps["offer"] . "</br>" ;
																	print "<i>" . $rowApps["offerDetails"] . "</i></br>" ;
																}
																
															print "</td>" ;
														print "</tr>" ;
														print "<tr>" ;
															print "<td style='vertical-align: top'>" ;
																print "<b>Application Question</b>" ;
															print "</td>" ;
															print "<td style='vertical-align: top'>" ;
																if ($rowApps["question"]=="") {
																	print "NA" ;
																}
																else {
																	print $rowApps["question"] ;
																}
															print "</td>" ;
														print "</tr>" ;
														print "<tr>" ;
															print "<td style='vertical-align: top'>" ;
																print "<b>Application Answer</b>" ;
															print "</td>" ;
															print "<td style='vertical-align: top'>" ;
																if ($rowApps["answer"]=="") {
																	print "NA" ;
																}
																else {
																	print $rowApps["answer"] ;
																}
															print "</td>" ;
														print "</tr>" ;
													print "</table>" ;
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
				
				//Set sidebar
				$_SESSION[$guid]["sidebarExtra"]=getUserPhoto($guid, $image_240, 240) ;
			}	
		}
	}
}
?>