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

use Gibbon\Forms\Form;

//Module includes
include __DIR__.'/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Higher Education/majors_manage_add.php') == false) {
    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {
    $page->breadcrumbs->add(__('Manage Majors'), 'majors_manage.php');
    $page->breadcrumbs->add(__('Add Major'));

    $role = staffHigherEducationRole($_SESSION[$guid]['gibbonPersonID'], $connection2);
    if ($role != 'Coordinator') {
        $page->addError(__('You do not have access to this action.'));
    } else {
        $returns = array();
        if (isset($_GET['editID'])) {
            $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Higher Education/majors_manage_edit.php&higherEducationMajorID='.$_GET['editID'];
        }
        if (isset($_GET['return'])) {
            returnProcess($guid, $_GET['return'], $editLink, $returns);
        }

        $form = Form::create('majors', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/majors_manage_addProcess.php');
        $form->addHiddenValue('address', $_SESSION[$guid]['address']);

        $row = $form->addRow();
            $row->addLabel('name', __('Name'));
            $row->addTextField('name')->isRequired()->maxLength(100);

        $row = $form->addRow();
            $row->addLabel('active', __('Active'));
            $row->addYesNo('active')->isRequired();

        $row = $form->addRow();
            $row->addFooter();
            $row->addSubmit();

        echo $form->getOutput();
    }
}
