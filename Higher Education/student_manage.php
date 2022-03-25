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

use Gibbon\Services\Format;
use Gibbon\Tables\DataTable;
use Gibbon\Module\HigherEducation\Domain\StudentGateway;

//Module includes
include __DIR__.'/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Higher Education/student_manage.php') == false) {
    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {
    $page->breadcrumbs->add(__('Manage Student Enrolment'));

    $role = staffHigherEducationRole($session->get('gibbonPersonID'), $connection2);
    if ($role != 'Coordinator') {
        $page->addError(__('You do not have access to this action.'));
    } else {
        $studentGateway = $container->get(StudentGateway::class);

        // QUERY
        $criteria = $studentGateway->newQueryCriteria(true)
            ->sortBy(['gibbonSchoolYear.sequenceNumber'], 'DESC')
            ->sortBy(['surname', 'preferredName'])
            ->pageSize(50)
            ->fromPOST();

        $students = $studentGateway->queryStudents($criteria, $session->get('gibbonSchoolYearID'));

        // TABLE
        $table = DataTable::createPaginated('students', $criteria);
        $table->setTitle(__('View'));

        $table->addHeaderAction('add', __('Add'))
            ->setURL('/modules/Higher Education/student_manage_add.php')
            ->displayLabel();

        $table->addColumn('name', __('Name'))->format(Format::using('name', ['', 'preferredName', 'surname', 'Student', true, true]));

        $table->addColumn('formGroup', __('Form Group'));

        $table->addColumn('schoolYear', __('Class Of'));

        $table->addColumn('advisor', __('Advisor'))->format(Format::using('name', ['', 'advisorpreferredName', 'advisorsurname', 'Staff', false, true]));

        $actions = $table->addActionColumn()
            ->addParam('higherEducationStudentID')
            ->format(function ($resource, $actions) {
                $actions->addAction('edit', __('Edit'))
                    ->setURL('/modules/Higher Education/student_manage_edit.php');
                $actions->addAction('delete', __('Delete'))
                    ->setURL('/modules/Higher Education/student_manage_delete.php');
            });

        echo $table->render($students);
    }
}
