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

@session_start();

//Module includes
include './modules/'.$_SESSION[$guid]['module'].'/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Higher Education/applications_view_details.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo 'You do not have access to this action.';
    echo '</div>';
} else {
    $role = staffHigherEducationRole($_SESSION[$guid]['gibbonPersonID'], $connection2);
    if ($role == false) {
        //Acess denied
        echo "<div class='error'>";
        echo 'You are not enroled in the Higher Education programme.';
        echo '</div>';
    } else {
        $gibbonPersonID = $_GET['gibbonPersonID'];
        if ($gibbonPersonID == '') {
            echo "<div class='error'>";
            echo 'You have not specified a student.';
            echo '</div>';
        } else {
            try {
                if ($role == 'Coordinator') {
                    $data = array('gibbonSchoolYearID' => $_SESSION[$guid]['gibbonSchoolYearID'], 'gibbonPersonID' => $gibbonPersonID);
                    $sql = "SELECT gibbonPerson.gibbonPersonID, higherEducationStudentID, surname, preferredName, image_240, gibbonYearGroup.nameShort AS yearGroup, gibbonRollGroup.nameShort AS rollGroup, gibbonRollGroup.gibbonRollGroupID, gibbonPersonIDAdvisor, gibbonSchoolYear.name AS schoolYear FROM higherEducationStudent JOIN gibbonPerson ON (higherEducationStudent.gibbonPersonID=gibbonPerson.gibbonPersonID) JOIN gibbonStudentEnrolment ON (higherEducationStudent.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID) LEFT JOIN gibbonSchoolYear ON (gibbonSchoolYear.gibbonSchoolYearID=gibbonPerson.gibbonSchoolYearIDClassOf) LEFT JOIN gibbonYearGroup ON (gibbonStudentEnrolment.gibbonYearGroupID=gibbonYearGroup.gibbonYearGroupID) LEFT JOIN gibbonRollGroup ON (gibbonStudentEnrolment.gibbonRollGroupID=gibbonRollGroup.gibbonRollGroupID) WHERE gibbonStudentEnrolment.gibbonSchoolYearID=:gibbonSchoolYearID AND gibbonPerson.status='Full' AND gibbonPerson.gibbonPersonID=:gibbonPersonID ORDER BY gibbonSchoolYear.sequenceNumber DESC, surname, preferredName";
                } else {
                    $data = array('gibbonSchoolYearID' => $_SESSION[$guid]['gibbonSchoolYearID'], 'advisor' => $_SESSION[$guid]['gibbonPersonID'], 'gibbonPersonID' => $gibbonPersonID);
                    $sql = "SELECT gibbonPerson.gibbonPersonID, higherEducationStudentID, surname, preferredName, image_240, , gibbonYearGroup.nameShort AS yearGroup, gibbonRollGroup.nameShort AS rollGroup, gibbonRollGroup.gibbonRollGroupID, gibbonPersonIDAdvisor, gibbonSchoolYear.name AS schoolYear FROM higherEducationStudent JOIN gibbonPerson ON (higherEducationStudent.gibbonPersonID=gibbonPerson.gibbonPersonID) JOIN gibbonStudentEnrolment ON (higherEducationStudent.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID) LEFT JOIN gibbonSchoolYear ON (gibbonSchoolYear.gibbonSchoolYearID=gibbonPerson.gibbonSchoolYearIDClassOf) LEFT JOIN gibbonYearGroup ON (gibbonStudentEnrolment.gibbonYearGroupID=gibbonYearGroup.gibbonYearGroupID) LEFT JOIN gibbonRollGroup ON (gibbonStudentEnrolment.gibbonRollGroupID=gibbonRollGroup.gibbonRollGroupID) WHERE gibbonStudentEnrolment.gibbonSchoolYearID=:gibbonSchoolYearID AND gibbonPerson.status='Full' AND gibbonPersonIDAdvisor=:advisor AND gibbonPerson.gibbonPersonID=:gibbonPersonID ORDER BY gibbonSchoolYear.sequenceNumber DESC, surname, preferredName";
                }
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                echo "<div class='error'>".$e->getMessage().'</div>';
            }

            if ($result->rowCount() != 1) {
                echo "<div class='error'>";
                echo 'The specified student does not exist, or you do not have access to them.';
                echo '</div>';
            } else {
                $row = $result->fetch();
                $image_240 = $row['image_240'];

                echo "<div class='trail'>";
                echo "<div class='trailHead'><a href='".$_SESSION[$guid]['absoluteURL']."'>Home</a> > <a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['q']).'/'.getModuleEntry($_GET['q'], $connection2, $guid)."'>".getModuleName($_GET['q'])."</a> > <a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['q'])."/applications_view.php'>View Applications</a> > </div><div class='trailEnd'>Application Details</div>";
                echo '</div>';

                echo "<table class='smallIntBorder' cellspacing='0' style='width: 100%'>";
                echo '<tr>';
                echo "<td style='width: 34%; vertical-align: top'>";
                echo "<span style='font-size: 115%; font-weight: bold'>Name</span><br/>";
                echo formatName('', $row['preferredName'], $row['surname'], 'Student', true, true);
                echo '</td>';
                echo "<td style='width: 34%; vertical-align: top'>";
                echo "<span style='font-size: 115%; font-weight: bold'>Roll Group</span><br/>";
                try {
                    $dataDetail = array('gibbonRollGroupID' => $row['gibbonRollGroupID']);
                    $sqlDetail = 'SELECT * FROM gibbonRollGroup WHERE gibbonRollGroupID=:gibbonRollGroupID';
                    $resultDetail = $connection2->prepare($sqlDetail);
                    $resultDetail->execute($dataDetail);
                } catch (PDOException $e) {
                    echo "<div class='error'>".$e->getMessage().'</div>';
                }
                if ($resultDetail->rowCount() == 1) {
                    $rowDetail = $resultDetail->fetch();
                    echo '<i>'.$rowDetail['name'].'</i>';
                }
                echo '</td>';
                echo "<td style='width: 34%; vertical-align: top'>";

                echo '</td>';
                echo '</tr>';
                echo '</table>';

                //Check for application record
                try {
                    $data = array('gibbonPersonID' => $gibbonPersonID);
                    $sql = 'SELECT * FROM  higherEducationApplication WHERE gibbonPersonID=:gibbonPersonID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    echo "<div class='error'>".$e->getMessage().'</div>';
                }

                if ($result->rowCount() != 1) {
                    echo "<div class='warning'>";
                    echo 'The selected student has not initiated the higher education application process.';
                    echo '</div>';
                } else {
                    $row = $result->fetch();

                    if ($row['applying'] != 'Y') {
                        echo "<div class='warning'>";
                        echo 'The selected student is not applying for higher education.';
                        echo '</div>';
                    } else {

                        //Create application record
                        ?>
						<form method="post" action="<?php echo $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/applications_trackProcess.php?higherEducationApplicationID='.$row['higherEducationApplicationID'] ?>">
						<table class='smallIntBorder' cellspacing='0' style="width: 100%">	
							<tr id='careerInterestsRow' <?php if ($row['applying'] == 'N' or $row['applying'] == '') { echo "style='display: none;'"; } ?>>
								<td colspan=2 style='padding-top: 15px;'> 
									<b>Career Interests</b><br/>
									<span style="font-size: 90%"><i><b>Student asked</b>: What areas of work are you interested in? What are your ambitions?</i></span><br/>
									<textarea readonly name="careerInterests" id="careerInterests" rows=12 style="width:738px; margin: 5px 0px 0px 0px"><?php echo htmlPrep($row['careerInterests']) ?></textarea>
								</td>
							</tr>
							<tr id='coursesMajorsRow' <?php if ($row['applying'] == 'N' or $row['applying'] == '') { echo "style='display: none;'"; } ?>>
								<td colspan=2 style='padding-top: 15px;'> 
									<b>Courses/Majors</b><br/>
									<span style="font-size: 90%"><i><b>Student asked</b>: What areas of study are you interested in? How do these relate to your career interests?</i></span><br/>
									<textarea readonly name="coursesMajors" id="coursesMajors" rows=12 style="width:738px; margin: 5px 0px 0px 0px"><?php echo htmlPrep($row['coursesMajors']) ?></textarea>
								</td>
							</tr>
							<tr id='otherScoresRow' <?php if ($row['applying'] == 'N' or $row['applying'] == '') { echo "style='display: none;'"; } ?>>
								<td colspan=2 style='padding-top: 15px;'> 
									<b>Scores</b><br/>
									<span style="font-size: 90%"><i><b>Student asked</b>: Do you have any non-<?php echo $_SESSION[$guid]['organisationNameShort'] ?> exam scores?</i></span><br/>
									<textarea readonly name="otherScores" id="otherScores" rows=12 style="width:738px; margin: 5px 0px 0px 0px"><?php echo htmlPrep($row['otherScores']) ?></textarea>
								</td>
							</tr>
							<tr id='personalStatementRow' <?php if ($row['applying'] == 'N' or $row['applying'] == '') { echo "style='display: none;'"; } ?>>
								<td colspan=2 style='padding-top: 15px;'> 
									<b>Personal Statement</b><br/>
									<span style="font-size: 90%"><i><b>Student asked</b>: Draft out ideas for your personal statement.</i></span><br/>
									<textarea readonly name="personalStatement" id="personalStatement" rows=12 style="width:738px; margin: 5px 0px 0px 0px"><?php echo htmlPrep($row['personalStatement']) ?></textarea>
								</td>
							</tr>
							<tr id='meetingNotesRow' <?php if ($row['applying'] == 'N' or $row['applying'] == '') { echo "style='display: none;'"; } ?>>
								<td colspan=2 style='padding-top: 15px;'> 
									<b>Meeting notes</b><br/>
									<span style="font-size: 90%"><i><b>Student asked</b>: Take notes on any meetings you have regarding your application process.</i></span><br/>
									<textarea readonly name="meetingNotes" id="meetingNotes" rows=12 style="width:738px; margin: 5px 0px 0px 0px;"><?php echo htmlPrep($row['meetingNotes']) ?></textarea>
								</td>
							</tr>
						</table>
						</form>
						<?php

                        $style = '';
                        if ($row['applying'] == 'N' or $row['applying'] == '') {
                            $style = 'display: none;';
                        }
                        echo "<div id='applicationsDiv' style='$style'>";
                        echo '<h2>';
                        echo 'Application To Institutions';
                        echo '</h2>';

                        if ($row['higherEducationApplicationID'] == '') {
                            echo "<div class='warning'>";
                            echo 'You need to save the information above (press the Submit button) before you can start adding applications.';
                            echo '</div>';
                        } else {
                            try {
                                $dataApps = array('higherEducationApplicationID' => $row['higherEducationApplicationID']);
                                $sqlApps = 'SELECT higherEducationApplicationInstitution.higherEducationApplicationInstitutionID, higherEducationInstitution.name as institution, higherEducationMajor.name as major, higherEducationApplicationInstitution.* FROM higherEducationApplicationInstitution JOIN higherEducationInstitution ON (higherEducationApplicationInstitution.higherEducationInstitutionID=higherEducationInstitution.higherEducationInstitutionID) JOIN higherEducationMajor ON (higherEducationApplicationInstitution.higherEducationMajorID=higherEducationMajor.higherEducationMajorID) WHERE higherEducationApplicationID=:higherEducationApplicationID ORDER BY rank, institution, major';
                                $resultApps = $connection2->prepare($sqlApps);
                                $resultApps->execute($dataApps);
                            } catch (PDOException $e) {
                                echo "<div class='error'>".$e->getMessage().'</div>';
                            }

                            if ($resultApps->rowCount() < 1) {
                                echo "<div class='error'>";
                                echo 'There are no applications to display.';
                                echo '</div>';
                            } else {
                                echo "<table cellspacing='0' style='width: 100%'>";
                                echo "<tr class='head'>";
                                echo '<th>';
                                echo 'Institution';
                                echo '</th>';
                                echo '<th>';
                                echo 'Major';
                                echo '</th>';
                                echo '<th>';
                                echo 'Ranking<br/>';
                                echo "<span style='font-size: 75%; font-style: italic'>Rating</span>";
                                echo '</th>';
                                echo '<th>';
                                echo 'Status';
                                echo '</th>';
                                echo '<th>';
                                echo 'Actions';
                                echo '</th>';
                                echo '</tr>';

                                $count = 0;
                                $rowNum = 'odd';
                                while ($rowApps = $resultApps->fetch()) {
                                    if ($count % 2 == 0) {
                                        $rowNum = 'even';
                                    } else {
                                        $rowNum = 'odd';
                                    }

									//COLOR ROW BY STATUS!
									echo "<tr class=$rowNum>";
                                    echo '<td>';
                                    echo $rowApps['institution'];
                                    echo '</td>';
                                    echo '<td>';
                                    echo $rowApps['major'];
                                    echo '</td>';
                                    echo '<td>';
                                    echo $rowApps['rank'].'<br/>';
                                    echo "<span style='font-size: 75%; font-style: italic'>".$rowApps['rating'].'</span>';
                                    echo '</td>';
                                    echo '<td>';
                                    echo $rowApps['status'];
                                    echo '</td>';
                                    echo '<td>';
                                    echo "<script type='text/javascript'>";
                                    echo '$(document).ready(function(){';
                                    echo "\$(\".description-$count\").hide();";
                                    echo "\$(\".show_hide-$count\").fadeIn(1000);";
                                    echo "\$(\".show_hide-$count\").click(function(){";
                                    echo "\$(\".description-$count\").fadeToggle(1000);";
                                    echo '});';
                                    echo '});';
                                    echo '</script>';
                                    echo "<a class='show_hide-$count' onclick='false' href='#'><img style='padding-right: 5px' src='".$_SESSION[$guid]['absoluteURL']."/themes/Default/img/page_down.png' alt='Show Details' onclick='return false;' /></a>";
                                    echo '</td>';
                                    echo '</tr>';
                                    echo "<tr class='description-$count' id='fields-$count' style='background-color: #fff; display: none'>";
                                    echo '<td colspan=5>';
                                    echo "<table class='mini' cellspacing='0' style='width: 100%'>";
                                    echo '<tr>';
                                    echo "<td style='vertical-align: top'>";
                                    echo '<b>Application Number</b>';
                                    echo '</td>';
                                    echo "<td style='vertical-align: top'>";
                                    if ($rowApps['applicationNumber'] == '') {
                                        echo 'NA';
                                    } else {
                                        echo $rowApps['applicationNumber'];
                                    }
                                    echo '</td>';
                                    echo '</tr>';
                                    echo '<tr>';
                                    echo "<td style='vertical-align: top'>";
                                    echo '<b>Scholarship Details</b>';
                                    echo '</td>';
                                    echo "<td style='vertical-align: top'>";
                                    if ($rowApps['scholarship'] == '') {
                                        echo 'NA';
                                    } else {
                                        echo $rowApps['scholarship'];
                                    }
                                    echo '</td>';
                                    echo '</tr>';
                                    echo '<tr>';
                                    echo "<td style='vertical-align: top'>";
                                    echo '<b>Offer</b>';
                                    echo '</td>';
                                    echo "<td style='vertical-align: top'>";
                                    if ($rowApps['offer'] == '') {
                                        echo 'NA';
                                    } else {
                                        echo $rowApps['offer'].'</br>';
                                        echo '<i>'.$rowApps['offerDetails'].'</i></br>';
                                    }

                                    echo '</td>';
                                    echo '</tr>';
                                    echo '<tr>';
                                    echo "<td style='vertical-align: top'>";
                                    echo '<b>Application Question</b>';
                                    echo '</td>';
                                    echo "<td style='vertical-align: top'>";
                                    if ($rowApps['question'] == '') {
                                        echo 'NA';
                                    } else {
                                        echo $rowApps['question'];
                                    }
                                    echo '</td>';
                                    echo '</tr>';
                                    echo '<tr>';
                                    echo "<td style='vertical-align: top'>";
                                    echo '<b>Application Answer</b>';
                                    echo '</td>';
                                    echo "<td style='vertical-align: top'>";
                                    if ($rowApps['answer'] == '') {
                                        echo 'NA';
                                    } else {
                                        echo $rowApps['answer'];
                                    }
                                    echo '</td>';
                                    echo '</tr>';
                                    echo '</table>';
                                    echo '</td>';
                                    echo '</tr>';

                                    ++$count;
                                }
                                echo '</table>';
                            }
                        }
                        echo '</div>';
                    }
                }

                //Set sidebar
                $_SESSION[$guid]['sidebarExtra'] = getUserPhoto($guid, $image_240, 240);
            }
        }
    }
}
?>