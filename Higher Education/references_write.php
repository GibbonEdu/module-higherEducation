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

use Gibbon\Services\Format;
use Gibbon\Tables\DataTable;
use Gibbon\Module\HigherEducation\Domain\ReferenceGateway;

//Module includes
include __DIR__.'/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Higher Education/references_write.php') == false) {
    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Write References'));

    $gibbonSchoolYearID = $_GET['gibbonSchoolYearID'] ?? null;
    if ($gibbonSchoolYearID == '') {
        $gibbonSchoolYearID = $session->get('gibbonSchoolYearID');
        $gibbonSchoolYearName = $session->get('gibbonSchoolYearName');
    }
    if (isset($_GET['gibbonSchoolYearID'])) {
        try {
            $data = array('gibbonSchoolYearID' => $_GET['gibbonSchoolYearID']);
            $sql = 'SELECT * FROM gibbonSchoolYear WHERE gibbonSchoolYearID=:gibbonSchoolYearID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $page->addError($e->getMessage());
        }
        if ($result->rowcount() != 1) {
            $page->addError(__('The specified year does not exist.'));
        } else {
            $row = $result->fetch();
            $gibbonSchoolYearID = $row['gibbonSchoolYearID'];
            $gibbonSchoolYearName = $row['name'];
        }
    }

    if ($gibbonSchoolYearID != '') {
        $page->navigator->addSchoolYearNavigation($gibbonSchoolYearID);

        $referenceGateway = $container->get(ReferenceGateway::class);

        // QUERY
        $criteria = $referenceGateway->newQueryCriteria(true)
            ->sortBy(['higherEducationReferenceComponent.status', 'timestamp'])
            ->sortBy(['timestamp'], 'DESC')
            ->pageSize(50)
            ->fromPOST();

        $references = $referenceGateway->queryReferenceComponents($criteria, $session->get('gibbonSchoolYearID'), $session->get('gibbonPersonID'));

        // TABLE
        $table = DataTable::createPaginated('references', $criteria);
        $table->setTitle(__('View'));
        $table->setDescription(__m('The table below shows all references for which your input is required in the selected school year.'));

        $table->addColumn('name', __('Name'))
            ->format(function ($values) {
                return Format::name('', $values['preferredName'], $values['surname'], 'Student', true, true)."<br/>".Format::small(Format::date($values['timestamp']));
            });

        $table->addColumn('status', __m('Your Contribution'))
            ->format(function ($values) use ($session) {
                if ($values['status'] == 'Cancelled') {
                    $return = "<img style='margin-right: 3px; float: left' title='Cancelled' src='./themes/".$session->get('gibbonThemeName')."/img/iconCross.png'/> ";
                } elseif ($values['status'] == 'Complete') {
                    $return = "<img style='margin-right: 3px; float: left' title='Complete' src='./themes/".$session->get('gibbonThemeName')."/img/iconTick.png'/> ";
                } else {
                    $return = "<img style='margin-right: 3px; float: left' title='In Progress' src='./themes/".$session->get('gibbonThemeName')."/img/iconTick_light.png'/> ";
                }
                $return .= Format::bold($values['status']);

                return $return;
            });


        $table->addColumn('typeReference', __('Type'));

        $table->addColumn('perspective', __('Perspective'))
            ->format(function ($values) use ($session) {
                return $values['type']."<br/>".Format::small($values['title']);
            });

        $actions = $table->addActionColumn()
            ->addParam('higherEducationReferenceComponentID')
            ->addParam('gibbonSchoolYearID', $gibbonSchoolYearID)
            ->format(function ($resource, $actions) {
                $actions->addAction('edit', __('Edit'))
                    ->setURL('/modules/Higher Education/references_write_edit.php');
            });

        echo $table->render($references);
    }
}
