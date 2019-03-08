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

if (isActionAccessible($guid, $connection2, '/modules/Higher Education/references_myNotes.php') == false) {
    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Edit My Reference Notes'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    try {
        $data = array('gibbonPersonID' => $_SESSION[$guid]['gibbonPersonID']);
        $sql = 'SELECT * FROM higherEducationStudent WHERE gibbonPersonID=:gibbonPersonID';
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
    }

    if ($result->rowCount() != 1) {
        $page->addError(__('You have not been enrolled for higher education applications.'));
    } else {
        $row = $result->fetch();
        ?>
        <p>
            On this page you can store some notes that will help your referee write about you. You might want to include some highlights of your achievements in and out of school, community service work you have done and activities you have taken part in.
        </p>
        <form method="post" action="<?php echo $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/references_myNotesProcess.php' ?>">
            <table class='smallIntBorder' cellspacing='0' style="width: 100%">
                <tr>
                    <td colspan=2>
                        <?php echo getEditor($guid,  true, 'referenceNotes', $row['referenceNotes'], 25, false, false, false) ?>
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
?>
