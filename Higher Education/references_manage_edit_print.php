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

if (isActionAccessible($guid, $connection2, '/modules/Higher Education/references_manage_edit.php') == false) {
    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {
    $role = staffHigherEducationRole($session->get('gibbonPersonID'), $connection2);
    if ($role != 'Coordinator') {
        $page->addError(__('You do not have access to this action.'));
    } else {
        $higherEducationReferenceID = $_GET['higherEducationReferenceID'];

        //Proceed!
        echo "<h2 class='top'>";
        echo 'Higher Education Reference';
        echo '</h2>';

        if ($higherEducationReferenceID != '') {
            try {
                $data = array('higherEducationReferenceID' => $higherEducationReferenceID);
                $sql = "SELECT preferredName, surname, higherEducationReference.* FROM higherEducationReference JOIN gibbonPerson ON (higherEducationReference.gibbonPersonID=gibbonPerson.gibbonPersonID) WHERE higherEducationReferenceID=:higherEducationReferenceID AND gibbonPerson.status='Full'";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                echo "<div class='warning'>";
                    echo $e->getMessage();
                echo '</div>';
            }

            if ($result->rowCount() != 1) {
                echo "<div class='warning'>";
                    echo __('The selected reference does not exist.');
                echo '</div>';
            } else {
                //Let's go!
                $row = $result->fetch();

                // Print
                echo "<p class='text-right mb-2 text-xs'>";
                echo "<a href='javascript:window.print()'>".__("Print")."<img style='margin-left: 5px' title='Print' src='./themes/".$session->get('gibbonThemeName')."/img/print.png'/></a>";
                echo '</p>';

                // Details table
                $table = DataTable::createDetails('reference');

                $table->addColumn('name', __('Student'))->format(Format::using('name', ['', 'preferredName', 'surname', 'Student', 'true']));
                $table->addColumn('type', __m('Reference Type'));

                echo $table->render([$row]);

                // Data table
                $referenceGateway = $container->get(ReferenceGateway::class);

                // QUERY
                $criteria = $referenceGateway->newQueryCriteria(true)
                    ->sortBy(['title'])
                    ->pageSize(50)
                    ->fromPOST();

                $references = $referenceGateway->queryReferenceComponentsByReference($criteria, $row['higherEducationReferenceID']);

                $table = DataTable::createPaginated('contributions', $criteria);
                    $table->addColumn('contribution', __m('Contributions'))->format(function($values) use ($guid, $session) {
                        $return = "<span class='text-base font-bold'>".$values['title']." . ".$values['type']." Comment by ". Format::name('', $values['preferredName'], $values['surname'], 'Student', false, false)."</span><br/>" ;
                        $return .= "<p class='mt-1'>".$values['body']."</p>";
                        return $return;
                    });

                echo $table->render($references);
            }
        }
    }
}
?>
