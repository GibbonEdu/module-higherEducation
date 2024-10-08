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
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

use Gibbon\Services\Format;
use Gibbon\Tables\DataTable;
use Gibbon\Module\HigherEducation\Domain\InstitutionGateway;

//Module includes
include __DIR__.'/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Higher Education/institutions_manage.php') == false) {
    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {
    $page->breadcrumbs->add(__('Manage Institutions'));

    $role = staffHigherEducationRole($session->get('gibbonPersonID'), $connection2);
    if ($role != 'Coordinator') {
        $page->addError(__('You do not have access to this action.'));
    } else {

        $institutionGateway = $container->get(InstitutionGateway::class);

        // QUERY
        $criteria = $institutionGateway->newQueryCriteria(true)
            ->sortBy(['name', 'country'])
            ->pageSize(50)
            ->fromPOST();

        $institutions = $institutionGateway->queryInstitutions($criteria);

        // TABLE
        $table = DataTable::createPaginated('institutions', $criteria);
        $table->setTitle(__('View'));

        $table->modifyRows(function ($unit, $row) {
            if ($unit['active'] != 'Y') $row->addClass('error');
            return $row;
        });

        $table->addHeaderAction('add', __('Add'))
            ->setURL('/modules/Higher Education/institutions_manage_add.php')
            ->displayLabel();

        $table->addColumn('name', __('Name'))
            ->format(function ($values) {
                return $values['name'].", ".$values['country'];
            });

        $table->addColumn('active', __('active'))
            ->format(function ($values) {
                return Format::yesNo(__($values['active']));
            });

        $actions = $table->addActionColumn()
            ->addParam('higherEducationInstitutionID')
            ->format(function ($resource, $actions) {
                $actions->addAction('edit', __('Edit'))
                    ->setURL('/modules/Higher Education/institutions_manage_edit.php');
                $actions->addAction('delete', __('Delete'))
                    ->setURL('/modules/Higher Education/institutions_manage_delete.php');
            });

        echo $table->render($institutions);
    }
}
