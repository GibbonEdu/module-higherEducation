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
use Gibbon\Module\HigherEducation\Domain\StaffGateway;

//Module includes
include __DIR__.'/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Higher Education/staff_manage.php') == false) {
    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {
    $page->breadcrumbs->add(__('Manage Staff'));

    $staffGateway = $container->get(StaffGateway::class);

    // QUERY
    $criteria = $staffGateway->newQueryCriteria(true)
        ->sortBy(['role', 'surname', 'preferredName'])
        ->pageSize(50)
        ->fromPOST();

    $staff = $staffGateway->queryStaff($criteria);

    // TABLE
    $table = DataTable::createPaginated('staff', $criteria);
    $table->setTitle(__('View'));

    $table->addHeaderAction('add', __('Add'))
        ->setURL('/modules/Higher Education/staff_manage_add.php')
        ->displayLabel();

    $table->addColumn('name', __('Name'))->format(Format::using('name', ['', 'preferredName', 'surname', 'Staff', true, true]));

    $table->addColumn('role', __('Role'));

    $actions = $table->addActionColumn()
        ->addParam('higherEducationStaffID')
        ->format(function ($resource, $actions) {
            $actions->addAction('edit', __('Edit'))
                ->setURL('/modules/Higher Education/staff_manage_edit.php');
            $actions->addAction('delete', __('Delete'))
                ->setURL('/modules/Higher Education/staff_manage_delete.php');
        });

    echo $table->render($staff);
}
