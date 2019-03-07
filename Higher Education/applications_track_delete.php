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

if (isActionAccessible($guid, $connection2, '/modules/Higher Education/applications_track_delete.php') == false) {
    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Track Applications'), 'applications_track.php');
    $page->breadcrumbs->add(__('Delete Application'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Check for student enrolment
    if (studentEnrolment($_SESSION[$guid]['gibbonPersonID'], $connection2) == false) {
        $page->addError(__('You have not been enrolled for higher education applications.'));
    } else {
        //Check for application record
        try {
            $data = array('gibbonPersonID' => $_SESSION[$guid]['gibbonPersonID']);
            $sql = 'SELECT * FROM  higherEducationApplication WHERE gibbonPersonID=:gibbonPersonID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $page->addError($e->getMessage());
        }

        if ($result->rowCount() != 1) {
            $page->addError(__('You have not saved your application process yet.'));
        } else {
            $row = $result->fetch();

            //Check if school year specified
            $higherEducationApplicationInstitutionID = $_GET['higherEducationApplicationInstitutionID'];
            if ($higherEducationApplicationInstitutionID == '') {
                $page->addError(__('You have not specified an application.'));
            } else {
                try {
                    $data = array('higherEducationApplicationInstitutionID' => $higherEducationApplicationInstitutionID);
                    $sql = 'SELECT * FROM higherEducationApplicationInstitution JOIN higherEducationInstitution ON (higherEducationApplicationInstitution.higherEducationInstitutionID=higherEducationInstitution.higherEducationInstitutionID) WHERE higherEducationApplicationInstitutionID=:higherEducationApplicationInstitutionID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $page->addError($e->getMessage());
                }

                if ($result->rowCount() != 1) {
                    $page->addError(__('The specified application cannot be found.'));
                } else {
                    //Let's go!
                    $row = $result->fetch(); ?>
                    <form method="post" action="<?php echo $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/applications_track_deleteProcess.php?higherEducationApplicationInstitutionID=$higherEducationApplicationInstitutionID" ?>">
                        <table class='smallIntBorder' cellspacing='0' style="width: 100%">
                            <tr>
                                <td>
                                    <b>Are you sure you want to delete your application to study at <?php echo $row['name'] ?>?</b><br/>
                                    <span style="font-size: 90%; color: #cc0000"><i>This operation cannot be undone, and may lead to loss of vital data in your system.<br/>PROCEED WITH CAUTION!</i></span>
                                </td>
                                <td class="right">

                                </td>
                            </tr>
                            <tr>
                                <td>
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
    }
}
?>
