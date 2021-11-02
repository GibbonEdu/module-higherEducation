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

if (isActionAccessible($guid, $connection2, '/modules/Higher Education/institutions_manage.php') == false) {
    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {
    $page->breadcrumbs->add(__('Manage Institutions'));

    $role = staffHigherEducationRole($session->get('gibbonPersonID'), $connection2);
    if ($role != 'Coordinator') {
        $page->addError(__('You do not have access to this action.'));
    } else {
       	//Set pagination variable
        $pagination = null;
        if (isset($_GET['page'])) {
            $pagination = $_GET['page'];
        }
        if ((!is_numeric($pagination)) or $pagination < 1) {
            $pagination = 1;
        }

        try {
            $data = array('gibbonSchoolYearID' => $session->get('gibbonSchoolYearID'));
            $sql = 'SELECT * FROM higherEducationInstitution ORDER BY name, country';
            $sqlPage = $sql.' LIMIT '.$session->get('pagination').' OFFSET '.(($pagination - 1) * $session->get('pagination'));
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $page->addError(__('Error: {error}. Students cannot be displayed.', ['error' => $e->getMessage()]));
        }

        echo "<div class='linkTop'>";
        echo "<a href='".$session->get('absoluteURL').'/index.php?q=/modules/'.$session->get('module')."/institutions_manage_add.php'><img title='New' src='./themes/".$session->get('gibbonThemeName')."/img/page_new.png'/></a>";
        echo '</div>';

        if ($result->rowCount() < 1) {
            $page->addError(__('There are no students to display.'));
        } else {
            if ($result->rowCount() > $session->get('pagination')) {
                printPagination($guid, $result->rowCount(), $pagination, $session->get('pagination'), 'top');
            }

            echo "<table cellspacing='0' style='width: 100%'>";
            echo "<tr class='head'>";
            echo '<th>';
            echo 'Name';
            echo '</th>';
            echo '<th>';
            echo 'Active';
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
                echo "<div class='warning'>";
                    echo $e->getMessage();
                echo '</div>';
            }

            while ($row = $resultPage->fetch()) {
                if ($count % 2 == 0) {
                    $rowNum = 'even';
                } else {
                    $rowNum = 'odd';
                }
                ++$count;

                if ($row['active'] == 'N') {
                    $rowNum = 'error';
                }

                //COLOR ROW BY STATUS!
                echo "<tr class=$rowNum>";
                echo '<td>';
                echo $row['name'].', '.$row['country'];
                echo '</td>';
                echo '<td>';
                echo $row['active'];
                echo '</td>';
                echo '<td>';
                echo "<a href='".$session->get('absoluteURL').'/index.php?q=/modules/'.$session->get('module').'/institutions_manage_edit.php&higherEducationInstitutionID='.$row['higherEducationInstitutionID']."'><img title='Edit' src='./themes/".$session->get('gibbonThemeName')."/img/config.png'/></a> ";
                echo "<a class='thickbox' href='".$session->get('absoluteURL').'/fullscreen.php?q=/modules/'.$session->get('module').'/institutions_manage_delete.php&higherEducationInstitutionID='.$row['higherEducationInstitutionID']."&width=650&height=135'><img title='Delete' src='./themes/".$session->get('gibbonThemeName')."/img/garbage.png'/></a> ";
                echo '</td>';
                echo '</tr>';
            }
            echo '</table>';

            if ($result->rowCount() > $session->get('pagination')) {
                printPagination($guid, $result->rowCount(), $pagination, $session->get('pagination'), 'bottom');
            }
        }
    }
}
