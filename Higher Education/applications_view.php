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
use Gibbon\Module\HigherEducation\Domain\ApplicationGateway;

//Module includes
include __DIR__.'/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Higher Education/applications_view.php') == false) {
    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {
    $role = staffHigherEducationRole($session->get('gibbonPersonID'), $connection2);
    if ($role == false) {
        //Acess denied
        $page->addError(__('You are not enroled in the Higher Education programme.'));
    } else {
        $page->breadcrumbs->add(__('View Applications'));
        echo '<p>';
        echo "Your higher educatuion staff role is $role. The students listed below are determined by your role, and student-staff relationship assignment.";
        echo '</p>';

        $applicationGateway = $container->get(ApplicationGateway::class);

        // QUERY
        $criteria = $applicationGateway->newQueryCriteria(true)
            ->sortBy(['gibbonSchoolYear.sequenceNumber'], 'DESC')
            ->sortBy(['surname', 'preferredName'])
            ->pageSize(50)
            ->fromPOST();

        $applications = $applicationGateway->queryApplications($criteria, $role, $session->get('gibbonSchoolYearID'), $session->get('gibbonPersonID'));

        // TABLE
        $table = DataTable::createPaginated('applications', $criteria);
        $table->setTitle(__('View'));

        $table->addColumn('name', __m('Name'))
            ->format(function ($values) {
                return Format::name('', $values['preferredName'], $values['surname'], 'Student', true, true);
            })
            ->notSortable();

        $table->addColumn('formGroup', __m('Form Group'));

        $table->addColumn('applying', __m('Applying'))
            ->format(function ($values) {
                if (!empty($values['applying'])) {
                    return Format::yesNo($values['applying']);
                } else {
                    return __m("Not yet indicated");
                }
            });

        $table->addColumn('applications', __m('Applications'))->format(function ($values) {
            if ($values['applying'] == "Y") {
                return $values['applications'];
            }
        });

        $actions = $table->addActionColumn()
            ->addParam('gibbonPersonID')
            ->format(function ($values, $actions) {
                    if ($values['applying'] == "Y") {
                        $actions->addAction('view', __('View'))
                            ->setURL('/modules/Higher Education/applications_view_details.php');
                    }
                });

        echo $table->render($applications);
    }
}
?>
