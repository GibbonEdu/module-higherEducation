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

if (isActionAccessible($guid, $connection2, '/modules/Higher Education/student_manage.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo 'You do not have access to this action.';
    echo '</div>';
} else {
    $page->breadcrumbs->add(__('Manage Student Enrolment'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $role = staffHigherEducationRole($_SESSION[$guid]['gibbonPersonID'], $connection2);
    if ($role != 'Coordinator') { echo "<div class='error'>";
        echo 'You do not have access to this action.';
        echo '</div>';
    } else {
        //Set pagination variable
        $page = null;
        if (isset($_GET['page'])) {
            $page = $_GET['page'];
        }
        if ((!is_numeric($page)) or $page < 1) {
            $page = 1;
        }

        try {
            $data = array('gibbonSchoolYearID' => $_SESSION[$guid]['gibbonSchoolYearID']);
            $sql = "SELECT higherEducationStudentID, surname, preferredName, gibbonYearGroup.nameShort AS yearGroup, gibbonRollGroup.nameShort AS rollGroup, gibbonPersonIDAdvisor, gibbonSchoolYear.name AS schoolYear FROM higherEducationStudent JOIN gibbonPerson ON (higherEducationStudent.gibbonPersonID=gibbonPerson.gibbonPersonID) JOIN gibbonStudentEnrolment ON (higherEducationStudent.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID) LEFT JOIN gibbonSchoolYear ON (gibbonSchoolYear.gibbonSchoolYearID=gibbonPerson.gibbonSchoolYearIDClassOf) LEFT JOIN gibbonYearGroup ON (gibbonStudentEnrolment.gibbonYearGroupID=gibbonYearGroup.gibbonYearGroupID) LEFT JOIN gibbonRollGroup ON (gibbonStudentEnrolment.gibbonRollGroupID=gibbonRollGroup.gibbonRollGroupID) WHERE gibbonStudentEnrolment.gibbonSchoolYearID=:gibbonSchoolYearID AND gibbonPerson.status='Full' ORDER BY gibbonSchoolYear.sequenceNumber DESC, surname, preferredName";
            $sqlPage = $sql.' LIMIT '.$_SESSION[$guid]['pagination'].' OFFSET '.(($page - 1) * $_SESSION[$guid]['pagination']);
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='error'>";
            echo $e->getMEssagE();
            echo 'Students cannot be displayed.';
            echo '</div>';
        }

        echo "<div class='linkTop'>";
        echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/student_manage_add.php'><img title='New' src='./themes/".$_SESSION[$guid]['gibbonThemeName']."/img/page_new.png'/></a>";
        echo '</div>';

        if ($result->rowCount() < 1) {
            echo "<div class='error'>";
            echo 'There are no students to display.';
            echo '</div>';
        } else {
            if ($result->rowCount() > $_SESSION[$guid]['pagination']) {
                printPagination($guid, $result->rowCount(), $page, $_SESSION[$guid]['pagination'], 'top');
            }

            echo "<table cellspacing='0' style='width: 100%'>";
            echo "<tr class='head'>";
            echo '<th>';
            echo 'Name';
            echo '</th>';
            echo '<th>';
            echo 'Roll<br/>Group';
            echo '</th>';
            echo '<th>';
            echo 'Class Of';
            echo '</th>';
            echo '<th>';
            echo 'Advisor';
            echo '</th>';
            echo '<th>';
            echo 'Actions';
            echo '</th>';
            echo '</tr>';

            $count = 0;
            $rowNum = 'odd';
            try {
                $resultPage = $connection2->prepare($sqlPage);
                $resultPage->execute($data);
            } catch (PDOException $e) {
                echo "<div class='error'>".$e->getMessage().'</div>';
            }

            while ($row = $resultPage->fetch()) {
                if ($count % 2 == 0) {
                    $rowNum = 'even';
                } else {
                    $rowNum = 'odd';
                }
                ++$count;

                    //COLOR ROW BY STATUS!
                    echo "<tr class=$rowNum>";
                echo '<td>';
                echo formatName('', $row['preferredName'], $row['surname'], 'Student', true, true);
                echo '</td>';
                echo '<td>';
                echo $row['rollGroup'];
                echo '</td>';
                echo '<td>';
                echo '<b>'.$row['schoolYear'].'</b>';
                echo '</td>';
                echo '<td>';
                if ($row['gibbonPersonIDAdvisor'] != '') {
                    try {
                        $dataAdvisor = array('gibbonPersonID' => $row['gibbonPersonIDAdvisor']);
                        $sqlAdvisor = "SELECT surname, preferredName FROM gibbonPerson WHERE gibbonPersonID=:gibbonPersonID AND status='Full'";
                        $resultAdvisor = $connection2->prepare($sqlAdvisor);
                        $resultAdvisor->execute($dataAdvisor);
                    } catch (PDOException $e) {
                        echo "<div class='error'>".$e->getMessage().'</div>';
                    }

                    if ($resultAdvisor->rowCount() == 1) {
                        $rowAdvisor = $resultAdvisor->fetch();
                        echo formatName('', $rowAdvisor['preferredName'], $rowAdvisor['surname'], 'Staff', false, true);
                    }
                }
                echo '</td>';
                echo '<td>';
                echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/student_manage_edit.php&higherEducationStudentID='.$row['higherEducationStudentID']."'><img title='Edit' src='./themes/".$_SESSION[$guid]['gibbonThemeName']."/img/config.png'/></a> ";
                echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/student_manage_delete.php&higherEducationStudentID='.$row['higherEducationStudentID']."'><img title='Delete' src='./themes/".$_SESSION[$guid]['gibbonThemeName']."/img/garbage.png'/></a> ";
                echo '</td>';
                echo '</tr>';
            }
            echo '</table>';

            if ($result->rowCount() > $_SESSION[$guid]['pagination']) {
                printPagination($guid, $result->rowCount(), $page, $_SESSION[$guid]['pagination'], 'bottom');
            }
        }
    }
}
