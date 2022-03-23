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
use Gibbon\Services\Format;
use Gibbon\Tables\DataTable;
use Gibbon\Domain\Staff\StaffGateway;

//Module includes
include __DIR__.'/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Higher Education/references_manage_edit.php') == false) {
    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {
    //Proceed!
    $gibbonSchoolYearID = $_GET['gibbonSchoolYearID'] ?? '';
    $higherEducationReferenceID = $_GET['higherEducationReferenceID'] ?? '';

    $page->breadcrumbs->add(__('Manage References'), 'references_manage.php', [
        'gibbonSchoolYearID' => $_GET['gibbonSchoolYearID'] ?? '',
        'search' => $_GET['search'] ?? '',
    ]);
    $page->breadcrumbs->add(__('Edit Reference'));

    $role = staffHigherEducationRole($session->get('gibbonPersonID'), $connection2);
    if ($role != 'Coordinator') {
        $page->addError(__('You do not have access to this action.'));
    } else {
        //Check if school year specified
        $gibbonSchoolYearID = $_GET['gibbonSchoolYearID'];
        $higherEducationReferenceID = $_GET['higherEducationReferenceID'];
        if ($higherEducationReferenceID == '' or $gibbonSchoolYearID == '') {
            $page->addError(__('You have not specified a reference.'));
        } else {
            try {
                $data = array('higherEducationReferenceID' => $higherEducationReferenceID);
                $sql = "SELECT preferredName, surname, higherEducationReference.* FROM higherEducationReference JOIN gibbonPerson ON (higherEducationReference.gibbonPersonID=gibbonPerson.gibbonPersonID) WHERE higherEducationReferenceID=:higherEducationReferenceID AND gibbonPerson.status='Full'";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $page->addError($e->getMessage());
            }

            if ($result->rowCount() != 1) {
                $page->addError(__('The selected reference does not exist.'));
            } else {
                //Let's go!
                $values = $result->fetch();

                echo "<p class='text-right mb-2 text-xs'>";
                echo "<a target='_blank' href='".$session->get('absoluteURL').'/report.php?q=/modules/'.$session->get('module')."/references_manage_edit_print.php&higherEducationReferenceID=$higherEducationReferenceID'>".__("Print")."<img style='margin-left: 5px' title='Print' src='./themes/".$session->get('gibbonThemeName')."/img/print.png'/></a>";
                echo '</p>';

                $form = Form::create('referencesManage', $session->get('absoluteURL').'/modules/'.$session->get('module')."/references_manage_editProcess.php?higherEducationReferenceID=$higherEducationReferenceID&gibbonSchoolYearID=$gibbonSchoolYearID");

                $form->addHiddenValue('alertsSent', $values['alertsSent']);
                $form->addHiddenValue('address', $session->get('address'));

                $form->addRow()->addHeading(__('Reference Information'));

                $row = $form->addRow();
                $row->addLabel('name', __('Student'));
                $row->addTextField('name')->isRequired()->readonly()->setValue(Format::name('', $values['preferredName'], $values['surname'], 'Student', false, false));

                $row = $form->addRow();
                $row->addLabel('type', __('Type'));
                $row->addTextField('name')->isRequired()->readonly()->setValue($values['type']);

                $row = $form->addRow();
                if ($values['status'] == 'Pending') {
                    $row->addLabel('status', __('Status'));
                    $row->addSelect('status')->isRequired()->fromArray(array('Pending' =>__('Pending'), 'In Progress' => __('In Progress'), 'Complete' => __('Complete'), 'Cancelled' => __('Cancelled')))->placeholder()->setValue($values['status']);

                } elseif ($values['status'] == 'In Progress') {
                    $row->addLabel('status', __('Status'));
                    $row->addSelect('status')->isRequired()->fromArray(array('In Progress' => __('In Progress'), 'Complete' => __('Complete'), 'Cancelled' => __('Cancelled')))->placeholder()->setValue($values['status']);

                } elseif ($values['status'] == 'Complete') {
                    $row->addLabel('status', __('Status'));
                    $row->addSelect('status')->isRequired()->fromArray(array('Complete' => __('Complete')))->placeholder()->setValue($values['status']);

                } elseif ($values['status'] == 'Cancelled') {
                    $row->addLabel('status', __('Status'));
                    $row->addSelect('status')->isRequired()->fromArray(array('Cancelled' => __('Cancelled')))->placeholder()->setValue($values['status']);
                }

                $form->toggleVisibilityByClass('contributionsRow')->onSelect('status')->when('In Progress');

                $row = $form->addRow();
                    $row->addLabel('statusNotes', __('Status Notes'));
                    if ($values['status'] == 'Pending' or $values['status'] == 'In Progress') {
                        $row->addTextField('statusNotes')->setValue($values['statusNotes']);
                    } elseif ($values['status'] == 'Complete' or $values['status'] == 'Cancelled') {
                        $row->addTextField('statusNotes')->readonly()->setValue($values['statusNotes']);
                    }

                 $row = $form->addRow();
                    $column = $row->addColumn();
                    $column->addLabel('notes', __('Reference Notes'))->description(__('Information about this reference shared by the student.'));
                    $column->addTextArea('notes')->setRows(5)->setClass('w-full')->readOnly()->setValue($values['notes']);

                $form->addRow()->addHeading(__('Contributions'));

                if ($values['alertsSent'] == 'N') {
                    $row = $form->addRow()->addClass('contributionsRow');
                    $row->addAlert(__('The user(s) listed below will be notified that their input is required for this reference. This will take place the next time you press the Submit button below.'), 'warning');
                } else {
                    $row = $form->addRow()->addClass('contributionsRow');
                    $row->addAlert(__('The user(s) listed below have already been notified by email that their input is required for this reference, and will not be alerted again.'), 'success');
                }

                    $dataContributions = array('higherEducationReferenceID' => $values['higherEducationReferenceID']);
                    $sqlContributions = 'SELECT higherEducationReferenceComponent.*, preferredName, surname FROM higherEducationReferenceComponent JOIN gibbonPerson ON (higherEducationReferenceComponent.gibbonPersonID=gibbonPerson.gibbonPersonID) WHERE higherEducationReferenceID=:higherEducationReferenceID ORDER BY title';
                    $resultContributions = $pdo->select($sqlContributions, $dataContributions)->toDataSet();

                if (count($resultContributions) < 1) {
                    echo "<div class='error'>";
                    echo 'Error: no referee requested, or a system error.';
                    echo "</div>";
                } else {

                //Dummy Criteria to force table to render as paginated
                $staffGateway = $container->get(StaffGateway::class);
                $criteria = $staffGateway->newQueryCriteria()
                    ->sortBy(['surname', 'preferredName'])
                    ->fromPOST();

                    $table = $form->addRow()->addDataTable('contributions', $criteria)->withData($resultContributions);
                        $table->addExpandableColumn('body');
                        $table->addColumn('name', __('Name'))->format(Format::using('name', ['title', 'preferredName', 'surname', 'Staff', true, true]));
                        $table->addColumn('status', __('Status'))->format(function($valuesContributions) use ($guid, $session) {
                            if ($valuesContributions['status'] == 'Complete') {
                                return "<img style='margin-right: 3px; float: left' title='Complete' src='./themes/".$session->get('gibbonThemeName')."/img/iconTick.png'/> <b>".$valuesContributions['status']."</b>";
                            } else {
                                return "<img style='margin-right: 3px; float: left' title='In Progress' src='./themes/".$session->get('gibbonThemeName')."/img/iconTick_light.png'/> <b> ".$valuesContributions['status']."</b>";
                            }
                        });
                        $table->addColumn('type', __('Type'));
                        $table->addColumn('title', __('Title'));
                        $table->addActionColumn()
                            ->addParam('higherEducationReferenceComponentID')
                            ->addParam('higherEducationReferenceID')
                            ->addParam('gibbonSchoolYearID', $gibbonSchoolYearID)
                            ->format(function ($valuesContributions, $actions) use ($session) {
                                $actions->addAction('edit', __('Edit'))
                                        ->setURL('/modules/'.$session->get('module').'/references_manage_edit_contribution_edit.php');
                                $actions->addAction('delete', __('Delete'))
                                        ->setURL('/modules/'.$session->get('module').'/references_manage_edit_contribution_delete.php');
                            });
                }

                $form->loadAllValuesFrom($values);

                $row = $form->addRow();
                    $row->addFooter();
                    $row->addSubmit();

                    echo $form->getOutput();
            }
        }
    }
}
?>
