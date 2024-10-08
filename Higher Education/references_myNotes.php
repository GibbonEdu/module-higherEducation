<?php
/*
Gibbon: the flexible, open school platform
Founded by Ross Parker at ICHK Secondary. Built by Ross Parker, Sandra Kuipers and the Gibbon community (https://gibbonedu.org/about/)
Copyright © 2010, Gibbon Foundation
Gibbon™, Gibbon Education Ltd. (Hong Kong)

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

//Module includes
include __DIR__.'/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Higher Education/references_myNotes.php') == false) {
    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Edit My Reference Notes'));

    try {
        $data = array('gibbonPersonID' => $session->get('gibbonPersonID'));
        $sql = 'SELECT * FROM higherEducationStudent WHERE gibbonPersonID=:gibbonPersonID';
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
    }

    if ($result->rowCount() != 1) {
        $page->addError(__('You have not been enrolled for higher education applications.'));
    } else {
        $values = $result->fetch();

        echo '<p>'.__m('On this page you can store some notes that will help your referee write about you. You might want to include some highlights of your achievements in and out of school, community service work you have done and activities you have taken part in.').'</p>';

        $form = Form::create('myNotes', $session->get('absoluteURL').'/modules/'.$session->get('module').'/references_myNotesProcess.php');
        $form->addHiddenValue('address', $session->get('address'));

        $row = $form->addRow();
            $row->addEditor('referenceNotes', $guid)->setRows(25)->showMedia()->setValue($values['referenceNotes']);

        $row = $form->addRow();
            $row->addSubmit();

        echo $form->getOutput();
    }
}
