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

use Gibbon\Forms\Form;
use Gibbon\Services\Format;

//Module includes
include __DIR__.'/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Higher Education/references_manage.php') == false) {
    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {
    $role = staffHigherEducationRole($session->get('gibbonPersonID'), $connection2);
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

            $gibbonSchoolYearID = null;
            if (isset($_GET['gibbonSchoolYearID'])) {
                $gibbonSchoolYearID = $_GET['gibbonSchoolYearID'];
            }
            if ($gibbonSchoolYearID == '') {
                $gibbonSchoolYearID = $session->get('gibbonSchoolYearID');
                $gibbonSchoolYearName = $session->get('gibbonSchoolYearName');
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
                $page->navigator->addSchoolYearNavigation($gibbonSchoolYearID);

                echo "<h3 class='top'>";
                echo __('Search');
                echo '</h3>';

                $form = Form::create('search', $session->get('absoluteURL').'/index.php', 'get');
                $form->setClass('noIntBorder fullWidth');

                $form->addHiddenValue('q', '/modules/'.$session->get('module').'/references_manage.php');

                $row = $form->addRow();
                    $row->addLabel('search', __('Search For'))->description(__('Preferred, surname, username.'));
                    $row->addTextField('search')->setValue($search);

                $row = $form->addRow();
                    $row->addSearchSubmit($gibbon->session, __('Clear Search'));

                echo $form->getOutput();

                echo "<h3 class='top'>";
                echo __('View');
                echo '</h3>';
                echo '<p>';
                echo 'The table below shows all references request in the selected school year. Use the "Previous Year" and "Next Year" links to navigate to other years.';
                echo '<p>';

                //Set pagination variable
                $pagination = '';
                if (isset($_GET['page'])) {
                    $pagination = $_GET['page'];
                }
                if ((!is_numeric($pagination)) or $pagination < 1) {
                    $pagination = 1;
                }

                try {
                    $data = array('gibbonSchoolYearID' => $gibbonSchoolYearID);
                    $sql = "SELECT higherEducationReference.*, surname, preferredName, title FROM higherEducationReference JOIN gibbonPerson ON (higherEducationReference.gibbonPersonID=gibbonPerson.gibbonPersonID) WHERE higherEducationReference.gibbonSchoolYearID=:gibbonSchoolYearID AND gibbonPerson.status='Full' ORDER BY status, timestamp";
                    if ($search != '') {
                        $data = array('gibbonSchoolYearID' => $gibbonSchoolYearID, 'search1' => "%$search%", 'search2' => "%$search%", 'search3' => "%$search%");
                        $sql = "SELECT higherEducationReference.*, surname, preferredName, title FROM higherEducationReference JOIN gibbonPerson ON (higherEducationReference.gibbonPersonID=gibbonPerson.gibbonPersonID) WHERE higherEducationReference.gibbonSchoolYearID=:gibbonSchoolYearID AND gibbonPerson.status='Full' AND (preferredName LIKE :search1 OR surname LIKE :search2 OR username LIKE :search3) ORDER BY status, timestamp";
                    }
                    $sqlPage = $sql.' LIMIT '.$session->get('pagination').' OFFSET '.(($pagination - 1) * $session->get('pagination'));
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                }

                echo "<p class='text-right mb-2 text-xs'>";
                echo "<a href='".$session->get('absoluteURL').'/index.php?q=/modules/'.$session->get('module')."/references_manage_addMulti.php&gibbonSchoolYearID=$gibbonSchoolYearID&search=$search'>".__('Add Multiple Records')."<img style='margin-left: 5px' title='".__('Add Multiple Records')."' src='./themes/".$session->get('gibbonThemeName')."/img/page_new_multi.png'/></a>";
                echo '</p>';

                if ($result->rowCount() < 1) {
                    echo "<div class='warning'>";
                        echo __('There are no records to display.');
                    echo '</div>';
                } else {
                    if ($result->rowCount() > $session->get('pagination')) {
                        printPagination($guid, $result->rowCount(), $pagination, $session->get('pagination'), 'top', "gibbonSchoolYearID=$gibbonSchoolYearID&search=$search");
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

                        echo "<tr class=$rowNum>";
                        echo '<td>';
                        echo Format::name('', $row['preferredName'], $row['surname'], 'Student', true).'<br/>';
                        echo "<span style='font-size: 75%; font-style: italic'>".Format::date(substr($row['timestamp'], 0, 10)).'</span>';
                        echo '</td>';
                        echo "<td style='width: 25px'>";
                        if ($row['status'] == 'Cancelled') {
                            echo "<img style='margin-right: 3px; float: left' title='Cancelled' src='./themes/".$session->get('gibbonThemeName')."/img/iconCross.png'/> ";
                        } elseif ($row['status'] == 'Complete') {
                            echo "<img style='margin-right: 3px; float: left' title='Complete' src='./themes/".$session->get('gibbonThemeName')."/img/iconTick.png'/> ";
                        } else {
                            echo "<img style='margin-right: 3px; float: left' title='In Progress' src='./themes/".$session->get('gibbonThemeName')."/img/iconTick_light.png'/> ";
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
                        echo "<a href='".$session->get('absoluteURL').'/index.php?q=/modules/'.$session->get('module').'/references_manage_edit.php&higherEducationReferenceID='.$row['higherEducationReferenceID']."&gibbonSchoolYearID=$gibbonSchoolYearID'><img title='Edit' src='./themes/".$session->get('gibbonThemeName')."/img/config.png'/></a> ";
                        echo "<a class='thickbox' href='".$session->get('absoluteURL').'/fullscreen.php?q=/modules/'.$session->get('module').'/references_manage_delete.php&higherEducationReferenceID='.$row['higherEducationReferenceID']."&gibbonSchoolYearID=$gibbonSchoolYearID&width=650&height=135'><img title='Delete' src='./themes/".$session->get('gibbonThemeName')."/img/garbage.png'/></a>";
                        echo "<a target='_blank' href='".$session->get('absoluteURL').'/report.php?q=/modules/'.$session->get('module').'/references_manage_edit_print.php&higherEducationReferenceID='.$row['higherEducationReferenceID']."'><img title='Print' src='./themes/".$session->get('gibbonThemeName')."/img/print.png'/></a>";
                        echo '</td>';
                        echo '</tr>';
                    }
                    echo '</table>';

                    if ($result->rowCount() > $session->get('pagination')) {
                        printPagination($guid, $result->rowCount(), $pagination, $session->get('pagination'), 'bottom', "gibbonSchoolYearID=$gibbonSchoolYearID&search=$search");
                    }
                }
            }
        }
    }
}
?>
