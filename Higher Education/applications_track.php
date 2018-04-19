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
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

//Module includes
include './modules/'.$_SESSION[$guid]['module'].'/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Higher Education/applications_track.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo 'You do not have access to this action.';
    echo '</div>';
} else {

    //Proceed!
    echo "<div class='trail'>";
    echo "<div class='trailHead'><a href='".$_SESSION[$guid]['absoluteURL']."'>Home</a> > <a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['q']).'/'.getModuleEntry($_GET['q'], $connection2, $guid)."'>".getModuleName($_GET['q'])."</a> > </div><div class='trailEnd'>Track Application</div>";
    echo '</div>';

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    if (studentEnrolment($_SESSION[$guid]['gibbonPersonID'], $connection2) == false) { echo "<div class='error'>";
        echo 'You have not been enrolled for higher education applications.';
        echo '</div>';
    } else {
        echo '<p>';
        echo 'Use this page to provide relevant information about your higher education application intentions and progress. This information will be used to guide you through this process.';
        echo '</p>';

        //Check for application record
        try {
            $data = array('gibbonPersonID' => $_SESSION[$guid]['gibbonPersonID']);
            $sql = 'SELECT * FROM  higherEducationApplication WHERE gibbonPersonID=:gibbonPersonID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='error'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() != 1) {
            echo "<div class='warning'>";
            echo 'It appears that you are new to application tracking via the Higher Education module. Please enter your details below, and press the Submit button once you are done. You can reenter details into this page at any time.';
            echo '</div>';
        } else {
            $row = $result->fetch();
        }

        //Create application record
        ?>
		<script type="text/javascript">
			/* Controls for showing/hiding fields */
			$(document).ready(function(){
				<?php
                if (isset($row['applying'])) {
                    if ($row['applying'] == 'N') {
                        ?>
						$("#applicationsDiv").css("display", "none");
						$("#careerInterestsRow").css("display", "none");
						$("#coursesMajorsRow").css("display", "none");
						$("#otherScoresRow").css("display", "none");
						$("#personalStatementRow").css("display", "none");
						$("#meetingNotesRow").css("display", "none");
						<?php

                    }
                } elseif (isset($row['applying']) == false) {
                    ?>
					$("#applicationsDiv").css("display", "none");
					$("#careerInterestsRow").css("display", "none");
					$("#coursesMajorsRow").css("display", "none");
					$("#otherScoresRow").css("display", "none");
					$("#personalStatementRow").css("display", "none");
					$("#meetingNotesRow").css("display", "none");
					<?php

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
		<?php
        $higherEducationApplicationID = null;
        if (isset($row['higherEducationApplicationID'])) {
            $higherEducationApplicationID = $row['higherEducationApplicationID'];
        }
        ?>
		<form method="post" action="<?php echo $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/applications_trackProcess.php?higherEducationApplicationID='.$higherEducationApplicationID ?>">
		<table class='smallIntBorder' cellspacing='0' style="width: 100%">
			<tr>
				<td>
					<b>Applying?</b><br/>
					<span style="font-size: 90%"><i>Are you intending on applying for entry to higher education?</i></span>
				</td>
				<td class="right">
					<select style="width: 302px" name="applying" id="applying">
						<option value='N' <?php if (isset($row['applying'])) { if ($row['applying'] == 'N') { echo 'selected'; } } ?>>N</option>
						<option value='Y' <?php if (isset($row['applying'])) { if ($row['applying'] == 'Y') { echo 'selected'; } } ?>>Y</option>
					</select>
				</td>
			</tr>
			<tr id='careerInterestsRow' <?php if (isset($row['applying'])) {
    			if ($row['applying'] == 'N' or $row['applying'] == '') { echo "style='display: none;'"; }} ?>>
				<td colspan=2 style='padding-top: 15px;'>
					<b>Career Interests</b><br/>
					<span style="font-size: 90%"><i>What areas of work are you interested in? What are your ambitions?</i></span><br/>
					<textarea name="careerInterests" id="careerInterests" rows=8 style="width:738px; margin: 5px 0px 0px 0px"><?php if (isset($row['careerInterests'])) { echo htmlPrep($row['careerInterests']); } ?></textarea>
				</td>
			</tr>
			<tr id='coursesMajorsRow' <?php if (isset($row['applying'])) {
    			if ($row['applying'] == 'N' or $row['applying'] == '') { echo "style='display: none;'"; }} ?>>
				<td colspan=2 style='padding-top: 15px;'>
					<b>Courses/Majors</b><br/>
					<span style="font-size: 90%"><i>What areas of study are you interested in? How do these relate to your career interests?</i></span><br/>
					<textarea name="coursesMajors" id="coursesMajors" rows=8 style="width:738px; margin: 5px 0px 0px 0px"><?php if (isset($row['coursesMajors'])) { echo htmlPrep($row['coursesMajors']); } ?></textarea>
				</td>
			</tr>
			<tr id='otherScoresRow' <?php if (isset($row['applying'])) { if ($row['applying'] == 'N' or $row['applying'] == '') { echo "style='display: none;'"; }} ?>>
				<td colspan=2 style='padding-top: 15px;'>
					<b>Scores</b><br/>
					<span style="font-size: 90%"><i>Do you have any non-<?php echo $_SESSION[$guid]['organisationNameShort'] ?> exam scores?</i></span><br/>
					<textarea name="otherScores" id="otherScores" rows=8 style="width:738px; margin: 5px 0px 0px 0px"><?php if (isset($row['otherScores'])) { echo htmlPrep($row['otherScores']); } ?></textarea>
				</td>
			</tr>
			<tr id='personalStatementRow' <?php if (isset($row['applying'])) { if ($row['applying'] == 'N' or $row['applying'] == '') { echo "style='display: none;'"; }} ?>>
				<td colspan=2 style='padding-top: 15px;'>
					<b>Personal Statement</b><br/>
					<span style="font-size: 90%"><i>Draft out ideas for your personal statement.</i></span><br/>
					<textarea name="personalStatement" id="personalStatement" rows=8 style="width:738px; margin: 5px 0px 0px 0px"><?php if (isset($row['personalStatement'])) { echo htmlPrep($row['personalStatement']); } ?></textarea>
				</td>
			</tr>
			<tr id='meetingNotesRow' <?php if (isset($row['applying'])) { if ($row['applying'] == 'N' or $row['applying'] == '') { echo "style='display: none;'"; }} ?>>
				<td colspan=2 style='padding-top: 15px;'>
					<b>Meeting notes</b><br/>
					<span style="font-size: 90%"><i>Take notes on any meetings you have regarding your application process.</i></span><br/>
					<textarea name="meetingNotes" id="meetingNotes" rows=8 style="width:738px; margin: 5px 0px 0px 0px"><?php if (isset($row['meetingNotes'])) { echo htmlPrep($row['meetingNotes']); } ?></textarea>
				</td>
			</tr>

			<tr>
				<td>
					<span style="font-size: 90%"><i>* denotes a required field</i></span>
				</td>
				<td class="right">
					<input type="hidden" name="address" value="<?php echo $_SESSION[$guid]['address'] ?>">
					<input type="submit" value="Submit">
				</td>
			</tr>
		</table>
		</form>
		<?php

        $style = '';
        if (isset($row['applying'])) {
            if ($row['applying'] == 'N' or $row['applying'] == '') {
                $style = 'display: none;';
            }
        }
        echo "<div id='applicationsDiv' style='$style'>";
        echo '<h2>';
        echo 'Application To Institutions';
        echo '</h2>';

        if (isset($row['higherEducationApplicationID']) == false) {
            echo "<div class='warning'>";
            echo 'You need to save the information above (press the Submit button) before you can start adding applications.';
            echo '</div>';
        } else {
            try {
                $dataApps = array('higherEducationApplicationID' => $row['higherEducationApplicationID']);
                $sqlApps = 'SELECT higherEducationApplicationInstitution.higherEducationApplicationInstitutionID, higherEducationInstitution.name as institution, higherEducationMajor.name as major, rank, rating FROM higherEducationApplicationInstitution JOIN higherEducationInstitution ON (higherEducationApplicationInstitution.higherEducationInstitutionID=higherEducationInstitution.higherEducationInstitutionID) JOIN higherEducationMajor ON (higherEducationApplicationInstitution.higherEducationMajorID=higherEducationMajor.higherEducationMajorID) WHERE higherEducationApplicationID=:higherEducationApplicationID ORDER BY rank, institution, major';
                $resultApps = $connection2->prepare($sqlApps);
                $resultApps->execute($dataApps);
            } catch (PDOException $e) {
                echo "<div class='error'>".$e->getMessage().'</div>';
            }

            echo "<div class='linkTop'>";
            echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/applications_track_add.php'><img title='New' src='./themes/".$_SESSION[$guid]['gibbonThemeName']."/img/page_new.png'/></a>";
            echo '</div>';

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
                    echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/applications_track_edit.php&higherEducationApplicationInstitutionID='.$rowApps['higherEducationApplicationInstitutionID']."'><img title='Edit' src='./themes/".$_SESSION[$guid]['gibbonThemeName']."/img/config.png'/></a> ";
                    echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/applications_track_delete.php&higherEducationApplicationInstitutionID='.$rowApps['higherEducationApplicationInstitutionID']."'><img title='Delete' src='./themes/".$_SESSION[$guid]['gibbonThemeName']."/img/garbage.png'/></a> ";
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
?>
