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

use Gibbon\Services\Format;
use Gibbon\Tables\DataTable;
use Gibbon\Module\HigherEducation\Domain\ReferenceGateway;

//Module includes
include __DIR__.'/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Higher Education/references_request.php') == false) {
    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {

    //Proceed!
    $page->breadcrumbs->add(__('Request References'));

    if (studentEnrolment($session->get('gibbonPersonID'), $connection2) == false) {
        $page->addError(__('You have not been enrolled for higher education applications.'));
    } else {
        $page->addMessage(__m('Use the form below to request references for particular purposes, and then track the writing and completion of the reference. Please remember that your reference is a complex document written by several people, and so make take some time to create.'));

        $referenceGateway = $container->get(ReferenceGateway::class);

        // QUERY
        $criteria = $referenceGateway->newQueryCriteria(true)
            ->sortBy(['timestamp'])
            ->pageSize(50)
            ->fromPOST();

        $references = $referenceGateway->queryReferencesByStudent($criteria, $session->get('gibbonPersonID'));

        // TABLE
        $table = DataTable::createPaginated('references', $criteria);
        $table->setTitle(__('View'));
        $table->setDescription(__m('The table below shows all references request in the selected school year. Use the "Previous Year" and "Next Year" links to navigate to other years.'));

        $table->addHeaderAction('add', __('Add'))
            ->setURL('/modules/Higher Education/references_request_add.php')
            ->displayLabel();

        $table->addColumn('timestamp', __('Date'))
            ->format(function ($values) {
                return Format::date($values['timestamp'])."<br/>".Format::small(Format::time($values['timestamp']));
            });

        $table->addColumn('type', __('Type'));

        $table->addColumn('status', __('Status'))
            ->format(function ($values) use ($session) {
                if ($values['status'] == 'Cancelled') {
                    $return = "<img style='margin-right: 3px; float: left' title='Cancelled' src='./themes/".$session->get('gibbonThemeName')."/img/iconCross.png'/> ";
                } elseif ($values['status'] == 'Complete') {
                    $return = "<img style='margin-right: 3px; float: left' title='Complete' src='./themes/".$session->get('gibbonThemeName')."/img/iconTick.png'/> ";
                } else {
                    $return = "<img style='margin-right: 3px; float: left' title='In Progress' src='./themes/".$session->get('gibbonThemeName')."/img/iconTick_light.png'/> ";
                }
                $return .= Format::bold($values['status'])."<br/>";
                $return .= Format::small($values['statusNotes']);

                return $return;
            });


        $table->addColumn('referees', __('Referees'));

        echo $table->render($references);
    }
}
