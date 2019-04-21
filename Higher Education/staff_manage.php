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

if (isActionAccessible($guid, $connection2, '/modules/Higher Education/staff_manage.php') == false) {
    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {
    $page->breadcrumbs->add(__('Manage Staff'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Set pagination variable
    $pagination = null;
    if (isset($_GET['page'])) {
        $pagination = $_GET['page'];
    }
    if ((!is_numeric($pagination)) or $pagination < 1) {
        $pagination = 1;
    }

    //SELECT NAMED
    try {
        $data = array();
        $sql = "SELECT higherEducationStaffID, higherEducationStaff.role, surname, preferredName FROM higherEducationStaff JOIN gibbonPerson ON (higherEducationStaff.gibbonPersonID=gibbonPerson.gibbonPersonID) WHERE status='Full' ORDER BY role, surname, preferredName";
        $sqlPage = $sql.' LIMIT '.$_SESSION[$guid]['pagination'].' OFFSET '.(($pagination - 1) * $_SESSION[$guid]['pagination']);
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $page->addError($e->getMessage());
    }

    echo "<div class='linkTop'>";
    echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/staff_manage_add.php'><img title='New' src='./themes/".$_SESSION[$guid]['gibbonThemeName']."/img/page_new.png'/></a>";
    echo '</div>';

    if ($result->rowCount() < 1) {
        echo "<div class='warning'>";
            echo __('There are no staff to display.');
        echo '</div>';
    } else {
        if ($result->rowCount() > $_SESSION[$guid]['pagination']) {
            printPagination($guid, $result->rowCount(), $pagination, $_SESSION[$guid]['pagination'], 'top');
        }

        echo "<table cellspacing='0' style='width: 100%'>";
        echo "<tr class='head'>";
        echo '<th>';
        echo 'Name';
        echo '</th>';
        echo '<th>';
        echo 'Role';
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

            //COLOR ROW BY STATUS!
            echo "<tr class=$rowNum>";
            echo '<td>';
            echo formatName('', $row['preferredName'], $row['surname'], 'Staff', true, true);
            echo '</td>';
            echo '<td>';
            echo $row['role'];
            echo '</td>';
            echo '<td>';
            echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/staff_manage_edit.php&higherEducationStaffID='.$row['higherEducationStaffID']."'><img title='Edit' src='./themes/".$_SESSION[$guid]['gibbonThemeName']."/img/config.png'/></a> ";
            echo "<a class='thickbox' href='".$_SESSION[$guid]['absoluteURL'].'/fullscreen.php?q=/modules/'.$_SESSION[$guid]['module'].'/staff_manage_delete.php&higherEducationStaffID='.$row['higherEducationStaffID']."&width=650&height=135'><img title='Delete' src='./themes/".$_SESSION[$guid]['gibbonThemeName']."/img/garbage.png'/></a> ";
            echo '</td>';
            echo '</tr>';
        }
        echo '</table>';

        if ($result->rowCount() > $_SESSION[$guid]['pagination']) {
            printPagination($guid, $result->rowCount(), $pagination, $_SESSION[$guid]['pagination'], 'bottom');
        }
    }
}
