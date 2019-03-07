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

if (isActionAccessible($guid, $connection2, '/modules/Higher Education/student_manage_delete.php') == false) {

    //Acess denied
    echo "<div class='error'>";
    echo 'You do not have access to this action.';
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Student Enrolment'), 'student_manage.php');
    $page->breadcrumbs->add(__('Delete Student Enrolment'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Check if school year specified
    $higherEducationStudentID = $_GET['higherEducationStudentID'];
    if ($higherEducationStudentID == '') { echo "<div class='error'>";
        echo 'You have not specified a student member.';
        echo '</div>';
    } else {
        try {
            $data = array('gibbonSchoolYearID' => $_SESSION[$guid]['gibbonSchoolYearID'], 'higherEducationStudentID' => $higherEducationStudentID);
            $sql = "SELECT higherEducationStudentID, surname, preferredName, gibbonYearGroup.nameShort AS yearGroup, gibbonRollGroup.nameShort AS rollGroup FROM higherEducationStudent JOIN gibbonPerson ON (higherEducationStudent.gibbonPersonID=gibbonPerson.gibbonPersonID) JOIN gibbonStudentEnrolment ON (higherEducationStudent.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID) LEFT JOIN gibbonYearGroup ON (gibbonStudentEnrolment.gibbonYearGroupID=gibbonYearGroup.gibbonYearGroupID) LEFT JOIN gibbonRollGroup ON (gibbonStudentEnrolment.gibbonRollGroupID=gibbonRollGroup.gibbonRollGroupID) WHERE gibbonStudentEnrolment.gibbonSchoolYearID=:gibbonSchoolYearID AND gibbonPerson.status='Full' AND higherEducationStudentID=:higherEducationStudentID";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='error'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() != 1) {
            echo "<div class='error'>";
            echo 'The selected student member does not exist.';
            echo '</div>';
        } else {
            //Let's go!
            $row = $result->fetch();
            ?>
            <form method="post" action="<?php echo $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/student_manage_deleteProcess.php?higherEducationStudentID=$higherEducationStudentID" ?>">
                <table class='smallIntBorder' cellspacing='0' style="width: 100%">
                    <tr>
                        <td>
                            <b>Are you sure you want to delete "<?php echo formatName('', $row['preferredName'], $row['surname'], 'Student', true, true) ?>" from the Higher Education programme?</b><br/>
                            <span style="font-size: 90%; color: #cc0000"><i>This operation cannot be undone, and may lead to loss of vital data in your system.<br/>PROCEED WITH CAUTION!</i></span>
                        </td>
                        <td class="right">

                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input name="higherEducationStudentID" id="higherEducationStudentID" value="<?php echo $higherEducationStudentID ?>" type="hidden">
                            <input type="hidden" name="address" value="<?php echo $_SESSION[$guid]['address'] ?>">
                            <input type="submit" value="Yes">
                        </td>
                        <td class="right">

                        </td>
                    </tr>
                </table>
            </form>
            <?php

        }
    }
}
?>
