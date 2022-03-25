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

use Gibbon\Forms\Form;
use Gibbon\Services\Format;
use Gibbon\Tables\DataTable;
use Gibbon\Module\HigherEducation\Domain\ReferenceGateway;

//Module includes
include __DIR__.'/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Higher Education/references_manage.php') == false) {
    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {
    $role = staffHigherEducationRole($session->get('gibbonPersonID'), $connection2);
    if ($role == false) {
        //Acess denied
        $page->addError(__('You are not enroled in the Higher Education programme.'));
    } else {
        if ($role != 'Coordinator') {
            //Acess denied
            $page->addError(__('You do not have permission to access this page.'));
        } else {
            //Proceed!
            $page->breadcrumbs->add(__('Manage References'));

            $search = $_GET['search'] ?? '';
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
                // FILTER
                $page->navigator->addSchoolYearNavigation($gibbonSchoolYearID);

                $form = Form::create('search', $session->get('absoluteURL').'/index.php', 'get');
                $form->setTitle(__('Search'));
                $form->setClass('noIntBorder fullWidth');

                $form->addHiddenValue('q', '/modules/'.$session->get('module').'/references_manage.php');

                $row = $form->addRow();
                    $row->addLabel('search', __('Search For'))->description(__('Preferred, surname, username.'));
                    $row->addTextField('search')->setValue($search);

                $row = $form->addRow();
                    $row->addSearchSubmit($gibbon->session, __('Clear Search'));

                echo $form->getOutput();

                $referenceGateway = $container->get(ReferenceGateway::class);

                // QUERY
                $criteria = $referenceGateway->newQueryCriteria(true)
                    ->sortBy(['status', 'timestamp'])
                    ->pageSize(50)
                    ->fromPOST();

                $references = $referenceGateway->queryReferences($criteria, $session->get('gibbonSchoolYearID'), $search);

                // TABLE
                $table = DataTable::createPaginated('references', $criteria);
                $table->setTitle(__('View'));
                $table->setDescription(__m('The table below shows all references request in the selected school year. Use the "Previous Year" and "Next Year" links to navigate to other years.'));

                $table->addHeaderAction('add', __('Add Multiple Records'))
                    ->setURL('/modules/Higher Education/references_manage_addMulti.php')
                    ->displayLabel()
                    ->setIcon('page_new_multi');

                $table->addColumn('name', __('Name'))
                    ->format(function ($values) {
                        return Format::name('', $values['preferredName'], $values['surname'], 'Student', true, true)."<br/>".Format::small(Format::date($values['timestamp']));
                    });

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

                $table->addColumn('type', __('Type'));

                $actions = $table->addActionColumn()
                    ->addParam('higherEducationReferenceID')
                    ->addParam('gibbonSchoolYearID', $gibbonSchoolYearID)
                    ->format(function ($resource, $actions) {
                        $actions->addAction('edit', __('Edit'))
                            ->setURL('/modules/Higher Education/references_manage_edit.php');
                        $actions->addAction('delete', __('Delete'))
                            ->setURL('/modules/Higher Education/references_manage_delete.php');
                        $actions->addAction('print', __('Print'))
                            ->setURL('/modules/Higher Education/references_manage_edit_print.php');
                    });

                echo $table->render($references);
            }
        }
    }
}
?>
