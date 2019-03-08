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
include __DIR__.'/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Higher Education/references_manage.php') == false) {
    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {
    $role = staffHigherEducationRole($_SESSION[$guid]['gibbonPersonID'], $connection2);
    if ($role == false) {
        //Acess denied
        $page->addError(__('You are not enroled in the Higher Education programme.'));
    } else {
        if ($role != 'Coordinator') {
            //Acess denied
            $page->addError(__('You do not have permission to access this page.'));
        } else {
            //Proceed!
            $page->breadcrumbs->add(__('Manage References'));

            if (isset($_GET['return'])) {
                returnProcess($guid, $_GET['return'], null, null);
            }

            $gibbonSchoolYearID = null;
            if (isset($_GET['gibbonSchoolYearID'])) {
                $gibbonSchoolYearID = $_GET['gibbonSchoolYearID'];
            }
            if ($gibbonSchoolYearID == '') {
                $gibbonSchoolYearID = $_SESSION[$guid]['gibbonSchoolYearID'];
                $gibbonSchoolYearName = $_SESSION[$guid]['gibbonSchoolYearName'];
            }
            if (isset($_GET['gibbonSchoolYearID'])) {
                try {
                    $data = array('gibbonSchoolYearID' => $_GET['gibbonSchoolYearID']);
                    $sql = 'SELECT * FROM gibbonSchoolYear WHERE gibbonSchoolYearID=:gibbonSchoolYearID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $page->addError($e->getMessage());
                }
                if ($result->rowcount() != 1) {
                    $page->addError(__('The specified year does not exist.'));
                } else {
                    $row = $result->fetch();
                    $gibbonSchoolYearID = $row['gibbonSchoolYearID'];
                    $gibbonSchoolYearName = $row['name'];
                }
            }

            $search = '';
            if (isset($_GET['search'])) {
                $search = $_GET['search'];
            }

            if ($gibbonSchoolYearID != '') {
                echo "<h2 class='top'>";
                echo $gibbonSchoolYearName;
                echo '</h2>';

                echo "<div class='linkTop'>";
                    //Print year picker
                    if (getPreviousSchoolYearID($gibbonSchoolYearID, $connection2) != false) {
                        echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/references_manage.php&gibbonSchoolYearID='.getPreviousSchoolYearID($gibbonSchoolYearID, $connection2)."'>Previous Year</a> ";
                    } else {
                        echo 'Previous Year ';
                    }
                    echo ' | ';
                    if (getNextSchoolYearID($gibbonSchoolYearID, $connection2) != false) {
                        echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/references_manage.php&gibbonSchoolYearID='.getNextSchoolYearID($gibbonSchoolYearID, $connection2)."'>Next Year</a> ";
                    } else {
                        echo 'Next Year ';
                    }
                echo '</div>';

                echo "<h3 class='top'>";
                echo 'Search';
                echo '</h3>';
                echo "<div class='linkTop'>";
                echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/references_manage.php'>Clear Search</a>";
                echo '</div>'; ?>
                <form method="get" action="<?php echo $_SESSION[$guid]['absoluteURL']?>/index.php">
                    <table class='smallIntBorder' cellspacing='0' style="width: 100%">
                        <tr>
                            <td>
                                <b>Search For</b><br/>
                                <span style="font-size: 90%"><i>Preferred, surname, username.</i></span>
                            </td>
                            <td class="right">
                                <input name="search" id="search" maxlength=20 value="<?php echo $search ?>" type="text" style="width: 300px">
                            </td>
                        </tr>
                        <tr>
                            <td colspan=2 class="right">
                                <input type="hidden" name="q" value="/modules/<?php echo $_SESSION[$guid]['module'] ?>/references_manage.php">
                                <input type="hidden" name="address" value="<?php echo $_SESSION[$guid]['address'] ?>">
                                <input type="submit" value="Submit">
                            </td>
                        </tr>
                    </table>
                </form>
                <?php

                echo "<h3 class='top'>";
                echo 'View';
                echo '</h3>';
                echo '<p>';
                echo 'The table below shows all references request in the selected school year. Use the "Previous Year" and "Next Year" links to navigate to other years.';
                echo '<p>';

                //Set pagination variable
                $page = '';
                if (isset($_GET['page'])) {
                    $page = $_GET['page'];
                }
                if ((!is_numeric($page)) or $page < 1) {
                    $page = 1;
                }

                try {
                    $data = array('gibbonSchoolYearID' => $gibbonSchoolYearID);
                    $sql = "SELECT higherEducationReference.*, surname, preferredName, title FROM higherEducationReference JOIN gibbonPerson ON (higherEducationReference.gibbonPersonID=gibbonPerson.gibbonPersonID) WHERE higherEducationReference.gibbonSchoolYearID=:gibbonSchoolYearID AND gibbonPerson.status='Full' ORDER BY status, timestamp";
                    if ($search != '') {
                        $data = array('gibbonSchoolYearID' => $gibbonSchoolYearID, 'search1' => "%$search%", 'search2' => "%$search%", 'search3' => "%$search%");
                        $sql = "SELECT higherEducationReference.*, surname, preferredName, title FROM higherEducationReference JOIN gibbonPerson ON (higherEducationReference.gibbonPersonID=gibbonPerson.gibbonPersonID) WHERE higherEducationReference.gibbonSchoolYearID=:gibbonSchoolYearID AND gibbonPerson.status='Full' AND (preferredName LIKE :search1 OR surname LIKE :search2 OR username LIKE :search3) ORDER BY status, timestamp";
                    }
                    $sqlPage = $sql.' LIMIT '.$_SESSION[$guid]['pagination'].' OFFSET '.(($page - 1) * $_SESSION[$guid]['pagination']);
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $page->addError($e->getMessage());
                }

                echo "<div class='linkTop'>";
                echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/references_manage_addMulti.php&gibbonSchoolYearID=$gibbonSchoolYearID&search=$search'>".__('Add Multiple Records')."<img title='".__('Add Multiple Records')."' src='./themes/".$_SESSION[$guid]['gibbonThemeName']."/img/page_new_multi.png'/></a>";
                echo '</div>';

                if ($result->rowCount() < 1) {
                    $page->addError(__('There are no records to display.'));
                } else {
                    if ($result->rowCount() > $_SESSION[$guid]['pagination']) {
                        printPagination($guid, $result->rowCount(), $page, $_SESSION[$guid]['pagination'], 'top', "gibbonSchoolYearID=$gibbonSchoolYearID&search=$search");
                    }

                    echo "<table cellspacing='0' style='width: 100%'>";
                    echo "<tr class='head'>";
                    echo '<th>';
                    echo 'Name<br/>';
                    echo "<span style='font-size: 75%; font-style: italic'>Date</span>";
                    echo '</th>';
                    echo '<th colspan=2>';
                    echo 'Status';
                    echo '</th>';
                    echo '<th>';
                    echo 'Type';
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
                        $page->addError($e->getMessage());
                    }
                    while ($row = $resultPage->fetch()) {
                        if ($count % 2 == 0) {
                            $rowNum = 'even';
                        } else {
                            $rowNum = 'odd';
                        }
                        ++$count;

                        echo "<tr class=$rowNum>";
                        echo '<td>';
                        echo formatName('', $row['preferredName'], $row['surname'], 'Student', true).'<br/>';
                        echo "<span style='font-size: 75%; font-style: italic'>".dateConvertBack($guid, substr($row['timestamp'], 0, 10)).'</span>';
                        echo '</td>';
                        echo "<td style='width: 25px'>";
                        if ($row['status'] == 'Cancelled') {
                            echo "<img style='margin-right: 3px; float: left' title='Cancelled' src='./themes/".$_SESSION[$guid]['gibbonThemeName']."/img/iconCross.png'/> ";
                        } elseif ($row['status'] == 'Complete') {
                            echo "<img style='margin-right: 3px; float: left' title='Complete' src='./themes/".$_SESSION[$guid]['gibbonThemeName']."/img/iconTick.png'/> ";
                        } else {
                            echo "<img style='margin-right: 3px; float: left' title='In Progress' src='./themes/".$_SESSION[$guid]['gibbonThemeName']."/img/iconTick_light.png'/> ";
                        }
                        echo '</td>';
                        echo '<td>';
                        echo '<b>'.$row['status'].'</b>';
                        if ($row['statusNotes'] != '') {
                            echo "<br/><span style='font-size: 75%; font-style: italic'>".$row['statusNotes'].'</span>';
                        }
                        echo '</td>';
                        echo '<td>';
                        echo $row['type'];
                        echo '</td>';
                        echo '<td>';
                        echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/references_manage_edit.php&higherEducationReferenceID='.$row['higherEducationReferenceID']."&gibbonSchoolYearID=$gibbonSchoolYearID'><img title='Edit' src='./themes/".$_SESSION[$guid]['gibbonThemeName']."/img/config.png'/></a> ";
                        echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/references_manage_delete.php&higherEducationReferenceID='.$row['higherEducationReferenceID']."&gibbonSchoolYearID=$gibbonSchoolYearID'><img title='Delete' src='./themes/".$_SESSION[$guid]['gibbonThemeName']."/img/garbage.png'/></a>";
                        echo "<a target='_blank' href='".$_SESSION[$guid]['absoluteURL'].'/report.php?q=/modules/'.$_SESSION[$guid]['module'].'/references_manage_edit_print.php&higherEducationReferenceID='.$row['higherEducationReferenceID']."'><img title='Print' src='./themes/".$_SESSION[$guid]['gibbonThemeName']."/img/print.png'/></a>";
                        echo '</td>';
                        echo '</tr>';
                    }
                    echo '</table>';

                    if ($result->rowCount() > $_SESSION[$guid]['pagination']) {
                        printPagination($guid, $result->rowCount(), $page, $_SESSION[$guid]['pagination'], 'bottom', "gibbonSchoolYearID=$gibbonSchoolYearID&search=$search");
                    }
                }
            }
        }
    }
}
?>
