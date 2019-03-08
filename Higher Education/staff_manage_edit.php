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

if (isActionAccessible($guid, $connection2, '/modules/Higher Education/staff_manage_edit.php') == false) {

    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage Staff'), 'staff_manage.php');
    $page->breadcrumbs->add(__('Edit Staff'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Check if school year specified
    $higherEducationStaffID = $_GET['higherEducationStaffID'];
    if ($higherEducationStaffID == 'Y') {
        $page->addError(__('You have not specified an activity.'));
    } else {
        try {
            $data = array('higherEducationStaffID' => $higherEducationStaffID);
            $sql = "SELECT higherEducationStaffID, higherEducationStaff.role, surname, preferredName FROM higherEducationStaff JOIN gibbonPerson ON (higherEducationStaff.gibbonPersonID=gibbonPerson.gibbonPersonID) WHERE status='Full' AND higherEducationStaffID=:higherEducationStaffID";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $page->addError($e->getMessage());
        }

        if ($result->rowCount() != 1) {
            $page->addError(__('The selected activity does not exist.'));
        } else {
            //Let's go!
            $row = $result->fetch();
            ?>
            <form method="post" action="<?php echo $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/staff_manage_editProcess.php?higherEducationStaffID=$higherEducationStaffID" ?>">
                <table class='smallIntBorder' cellspacing='0' style="width: 100%">
                    <tr>
                        <td>
                            <b>Staff *</b><br/>
                            <span style="font-size: 90%"><i>This value cannot be changed</i></span>
                        </td>
                        <td class="right">
                            <input readonly type='text' style='width: 302px' value='<?php echo formatName('', $row['preferredName'], $row['surname'], 'Staff', true, true) ?>'>
                            <script type="text/javascript">
                                var gibbonPersonID=new LiveValidation('gibbonPersonID');
                                gibbonPersonID.add(Validate.Exclusion, { within: ['Please select...'], failureMessage: "Select something!"});
                             </script>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <b>Role *</b><br/>
                            <span style="font-size: 90%"><i></i></span>
                        </td>
                        <td class="right">
                            <select name="role" id="role" style="width: 302px">
                                <option value="Please select...">Please select...</option>
                                <option <?php if ($row['role'] == 'Coordinator') { echo 'selected '; } ?>value="Coordinator">Coordinator</option>
                                <option <?php if ($row['role'] == 'Advisor') { echo 'selected '; } ?>value="Advisor">Advisor</option>
                            </select>
                            <script type="text/javascript">
                                var role=new LiveValidation('role');
                                role.add(Validate.Exclusion, { within: ['Please select...'], failureMessage: "Select something!"});
                             </script>
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
    }
}
?>
