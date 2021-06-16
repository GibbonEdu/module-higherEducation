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

use Gibbon\Forms\Prefab\DeleteForm;

if (isActionAccessible($guid, $connection2, '/modules/Higher Education/references_manage_edit.php') == false) {
    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {
    //Check if school year specified
    $gibbonSchoolYearID = $_GET['gibbonSchoolYearID'];
    $higherEducationReferenceComponentID = $_GET['higherEducationReferenceComponentID'];
    $higherEducationReferenceID = $_GET['higherEducationReferenceID'];
    if ($higherEducationReferenceComponentID == '' or $higherEducationReferenceID == '' or $gibbonSchoolYearID == '') {
        $page->addError(__('You have not specified a reference or component.'));
    } else {
        try {
            $data = array('higherEducationReferenceComponentID' => $higherEducationReferenceComponentID);
            $sql = 'SELECT * FROM higherEducationReferenceComponent WHERE higherEducationReferenceComponentID=:higherEducationReferenceComponentID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
        }

        if ($result->rowCount() != 1) {
            $page->addError(__('The specified reference component cannot be found.'));
        } else {
            //Let's go!
            $row = $result->fetch();

            $page->breadcrumbs->add(__('Manage References'), 'references_manage.php', ['gibbonSchoolYearID' => $gibbonSchoolYearID]);
            $page->breadcrumbs->add(__('Edit Reference'), 'references_manage_edit.php', [
                'higherEducationReferenceID' => $higherEducationReferenceID,
                'gibbonSchoolYearID' => $gibbonSchoolYearID,
            ]);
            $page->breadcrumbs->add(__('Delete Contribution'));

            if (isset($_GET['deleteReturn'])) {
                $deleteReturn = $_GET['deleteReturn'];
            } else {
                $deleteReturn = '';
            }
            $deleteReturnMessage = '';
            $class = 'error';
            if (!($deleteReturn == '')) {
                if ($deleteReturn == 'fail0') {
                    $deleteReturnMessage = 'Update failed because you do not have access to this action.';
                } elseif ($deleteReturn == 'fail1') {
                    $deleteReturnMessage = 'Update failed because a required parameter was not set.';
                } elseif ($deleteReturn == 'fail2') {
                    $deleteReturnMessage = 'Update failed due to a database error.';
                } elseif ($deleteReturn == 'fail3') {
                    $deleteReturnMessage = 'Update failed because your inputs were invalid.';
                }
                echo "<div class='$class'>";
                echo $deleteReturnMessage;
                echo '</div>';
            }

            $form = DeleteForm::createForm($session->get('absoluteURL').'/modules/'.$session->get('module')."/references_manage_edit_contribution_deleteProcess.php?higherEducationReferenceComponentID=$higherEducationReferenceComponentID&higherEducationReferenceID=$higherEducationReferenceID&gibbonSchoolYearID=$gibbonSchoolYearID");
            echo $form->getOutput();
        }
    }
}
