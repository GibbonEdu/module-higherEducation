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

if (isActionAccessible($guid, $connection2, '/modules/Higher Education/applications_view.php') == false) {
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
        echo "<div class='trail'>";
        echo "<div class='trailHead'><a href='".$_SESSION[$guid]['absoluteURL']."'>Home</a> > <a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['q']).'/'.getModuleEntry($_GET['q'], $connection2, $guid)."'>".getModuleName($_GET['q'])."</a> > </div><div class='trailEnd'>View Applications</div>";
        echo '</div>';
        echo '<p>';
        echo "Your higher educatuion staff role is $role. The students listed below are determined by your role, and student-staff relationship assignment.";
        echo '</p>';

        try {
            if ($role == 'Coordinator') {
                $data = array('gibbonSchoolYearID' => $_SESSION[$guid]['gibbonSchoolYearID']);
                $sql = "SELECT gibbonPerson.gibbonPersonID, higherEducationStudentID, surname, preferredName, gibbonYearGroup.nameShort AS yearGroup, gibbonRollGroup.nameShort AS rollGroup, gibbonRollGroup.gibbonRollGroupID, gibbonPersonIDAdvisor, gibbonSchoolYear.name AS schoolYear FROM higherEducationStudent JOIN gibbonPerson ON (higherEducationStudent.gibbonPersonID=gibbonPerson.gibbonPersonID) JOIN gibbonStudentEnrolment ON (higherEducationStudent.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID) LEFT JOIN gibbonSchoolYear ON (gibbonSchoolYear.gibbonSchoolYearID=gibbonPerson.gibbonSchoolYearIDClassOf) LEFT JOIN gibbonYearGroup ON (gibbonStudentEnrolment.gibbonYearGroupID=gibbonYearGroup.gibbonYearGroupID) LEFT JOIN gibbonRollGroup ON (gibbonStudentEnrolment.gibbonRollGroupID=gibbonRollGroup.gibbonRollGroupID) WHERE gibbonStudentEnrolment.gibbonSchoolYearID=:gibbonSchoolYearID AND gibbonPerson.status='Full' ORDER BY gibbonSchoolYear.sequenceNumber DESC, surname, preferredName";
            } else {
                $data = array('gibbonSchoolYearID' => $_SESSION[$guid]['gibbonSchoolYearID'], 'advisor' => $_SESSION[$guid]['gibbonPersonID']);
                $sql = "SELECT gibbonPerson.gibbonPersonID, higherEducationStudentID, surname, preferredName, gibbonYearGroup.nameShort AS yearGroup, gibbonRollGroup.nameShort AS rollGroup, gibbonRollGroup.gibbonRollGroupID, gibbonPersonIDAdvisor, gibbonSchoolYear.name AS schoolYear FROM higherEducationStudent JOIN gibbonPerson ON (higherEducationStudent.gibbonPersonID=gibbonPerson.gibbonPersonID) JOIN gibbonStudentEnrolment ON (higherEducationStudent.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID) LEFT JOIN gibbonSchoolYear ON (gibbonSchoolYear.gibbonSchoolYearID=gibbonPerson.gibbonSchoolYearIDClassOf) LEFT JOIN gibbonYearGroup ON (gibbonStudentEnrolment.gibbonYearGroupID=gibbonYearGroup.gibbonYearGroupID) LEFT JOIN gibbonRollGroup ON (gibbonStudentEnrolment.gibbonRollGroupID=gibbonRollGroup.gibbonRollGroupID) WHERE gibbonStudentEnrolment.gibbonSchoolYearID=:gibbonSchoolYearID AND gibbonPerson.status='Full' AND gibbonPersonIDAdvisor=:advisor ORDER BY gibbonSchoolYear.sequenceNumber DESC, surname, preferredName";
            }
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='error'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() < 1) {
            echo "<div class='error'>";
            echo 'There are no students to display.';
            echo '</div>';
        } else {
            echo "<div class='linkTop'>";
            echo 'Filter Roll Group: ';

            ?>
				<script type="text/javascript">
				$(document).ready(function() {
					$('.searchInput').val(1);
					$('.body').find("tr:odd").addClass('odd');
					$('.body').find("tr:even").addClass('even');
						
					$(".searchInput").change(function(){
						$('.body').find("tr").hide() ;
						if ($('.searchInput :selected').val() == "" ) {
							$('.body').find("tr").show() ;
						}
						else {
							$('.body').find('.' + $('.searchInput :selected').val()).show();
						}
									
						$('.body').find("tr").removeClass('odd even');
						$('.body').find('tr:visible:odd').addClass('odd');
						$('.body').find('tr:visible:even').addClass('even');
					});
				});
				</script>

				<select name="searchInput" class="searchInput" style='float: none; width: 100px'>
					<option selected value=''>All</option>
					<?php
                    try {
                        if ($role == 'Coordinator') {
                            $dataSelect = array('gibbonSchoolYearID' => $_SESSION[$guid]['gibbonSchoolYearID']);
                            $sqlSelect = "SELECT DISTINCT gibbonRollGroup.nameShort AS rollGroup, gibbonRollGroup.gibbonRollGroupID FROM higherEducationStudent JOIN gibbonPerson ON (higherEducationStudent.gibbonPersonID=gibbonPerson.gibbonPersonID) JOIN gibbonStudentEnrolment ON (higherEducationStudent.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID) LEFT JOIN gibbonRollGroup ON (gibbonStudentEnrolment.gibbonRollGroupID=gibbonRollGroup.gibbonRollGroupID) WHERE gibbonStudentEnrolment.gibbonSchoolYearID=:gibbonSchoolYearID AND gibbonPerson.status='Full' ORDER BY gibbonRollGroup.nameShort";
                        } else {
                            $dataSelect = array('gibbonSchoolYearID' => $_SESSION[$guid]['gibbonSchoolYearID'], 'advisor' => $_SESSION[$guid]['gibbonPersonID']);
                            $sqlSelect = "SELECT DISTINCT gibbonRollGroup.nameShort AS rollGroup, gibbonRollGroup.gibbonRollGroupID FROM higherEducationStudent JOIN gibbonPerson ON (higherEducationStudent.gibbonPersonID=gibbonPerson.gibbonPersonID) JOIN gibbonStudentEnrolment ON (higherEducationStudent.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID) LEFT JOIN gibbonRollGroup ON (gibbonStudentEnrolment.gibbonRollGroupID=gibbonRollGroup.gibbonRollGroupID) WHERE gibbonStudentEnrolment.gibbonSchoolYearID=:gibbonSchoolYearID AND gibbonPerson.status='Full' AND gibbonPersonIDAdvisor=:advisor ORDER BY gibbonRollGroup.nameShort";
                        }
                        $resultSelect = $connection2->prepare($sqlSelect);
                        $resultSelect->execute($dataSelect);
                    } catch (PDOException $e) {
                        echo "<div class='error'>".$e->getMessage().'</div>';
                    }

            while ($rowSelect = $resultSelect->fetch()) {
                echo "<option value='".$rowSelect['gibbonRollGroupID']."'>".htmlPrep($rowSelect['rollGroup']).'</option>';
            }
            ?>
				</select>
			<?php	
            echo '</div>';

            echo "<table cellspacing='0' style='width: 100%'>";
            echo "<tr class='head'>";
            echo '<th>';
            echo 'Name';
            echo '</th>';
            echo '<th>';
            echo 'Roll<br/>Group';
            echo '</th>';
            echo '<th>';
            echo 'Applying';
            echo '</th>';
            echo '<th>';
            echo 'Applications';
            echo '</th>';
            echo '<th>';
            echo 'Actions';
            echo '</th>';
            echo '</tr>';
            echo "<tbody class='body'>";

            $count = 0;
            $rowNum = 'odd';
            while ($row = $result->fetch()) {
                ++$count;

                        //COLOR ROW BY STATUS!
                        echo "<tr class='".$row['gibbonRollGroupID']."' id='".$row['rollGroup']."' name='".$row['rollGroup']."'>";
                echo '<td>';
                echo formatName('', $row['preferredName'], $row['surname'], 'Student', true, true);
                echo '</td>';
                echo '<td>';
                echo $row['rollGroup'];
                echo '</td>';
                echo '<td>';
                try {
                    $dataAdvisor = array('gibbonPersonID' => $row['gibbonPersonID']);
                    $sqlAdvisor = 'SELECT * FROM higherEducationApplication LEFT JOIN higherEducationApplicationInstitution ON (higherEducationApplicationInstitution.higherEducationApplicationID=higherEducationApplication.higherEducationApplicationID) WHERE gibbonPersonID=:gibbonPersonID';
                    $resultAdvisor = $connection2->prepare($sqlAdvisor);
                    $resultAdvisor->execute($dataAdvisor);
                } catch (PDOException $e) {
                    echo "<div class='error'>".$e->getMessage().'</div>';
                }

                if ($resultAdvisor->rowCount() < 1) {
                    echo '<i>Not yet indicated.</i>';
                } else {
                    $rowAdvisor = $resultAdvisor->fetch();
                    echo $rowAdvisor['applying'];
                }
                echo '</td>';
                echo '<td>';
                echo $resultAdvisor->rowCount();
                echo '</td>';
                echo '<td>';
                if ($resultAdvisor->rowCount() > 0 and $rowAdvisor['applying'] == 'Y') {
                    echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/applications_view_details.php&gibbonPersonID='.$row['gibbonPersonID']."'><img title='Details' src='./themes/".$_SESSION[$guid]['gibbonThemeName']."/img/page_right.png'/></a> ";
                }
                echo '</td>';
                echo '</tr>';
            }
            echo '</tbody>';
            echo '</table>';
        }
    }
}
?>