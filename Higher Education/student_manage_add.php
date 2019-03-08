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

//Module includes
include __DIR__.'/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Higher Education/student_manage_add.php') == false) {

    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {
    $page->breadcrumbs->add(__('Student Enrolment'), 'student_manage.php');
    $page->breadcrumbs->add(__('Add Student Enrolment'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    ?>
    <form method="post" action="<?php echo $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/student_manage_addProcess.php' ?>">
        <table class='smallIntBorder' cellspacing='0' style="width: 100%">
            <tr>
                <td>
                    <b>Students *</b><br/>
                </td>
                <td class="right">
                    <select name="Members[]" id="Members[]" multiple style="width: 302px; height: 150px">
                        <?php
                        try {
                            $dataSelect = array('gibbonSchoolYearID' => $_SESSION[$guid]['gibbonSchoolYearID']);
                            $sqlSelect = "SELECT gibbonPerson.gibbonPersonID, preferredName, surname, gibbonRollGroup.name AS name FROM gibbonPerson, gibbonStudentEnrolment, gibbonRollGroup WHERE gibbonPerson.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID AND gibbonStudentEnrolment.gibbonRollGroupID=gibbonRollGroup.gibbonRollGroupID AND status='FULL' AND gibbonRollGroup.gibbonSchoolYearID=:gibbonSchoolYearID ORDER BY name, surname, preferredName";
                            $resultSelect = $connection2->prepare($sqlSelect);
                            $resultSelect->execute($dataSelect);
                        } catch (PDOException $e) {
                        }
                        while ($rowSelect = $resultSelect->fetch()) {
                            echo "<option value='".$rowSelect['gibbonPersonID']."'>".htmlPrep($rowSelect['name']).' - '.formatName('', $rowSelect['preferredName'], $rowSelect['surname'], 'Student', true, true).'</option>';
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>
                    <b>Advisor</b><br/>
                </td>
                <td class="right">
                    <select style="width: 302px" name="gibbonPersonIDAdvisor" id="gibbonPersonIDAdvisor">
                        <?php
                        echo "<option value=''></option>";
                        try {
                            $data = array();
                            $sql = "SELECT * FROM gibbonPerson JOIN higherEducationStaff ON (gibbonPerson.gibbonPersonID=higherEducationStaff.gibbonPersonID) WHERE status='Full' ORDER BY surname, preferredName";
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                        }
                        while ($row = $result->fetch()) {
                            echo "<option value='".$row['gibbonPersonID']."'>".formatName('', $row['preferredName'], $row['surname'], 'Staff', true, true).'</option>';
                        }
                        ?>
                    </select>
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

}
?>
