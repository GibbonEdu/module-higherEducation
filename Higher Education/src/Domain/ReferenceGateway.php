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

namespace Gibbon\Module\HigherEducation\Domain;

use Gibbon\Domain\Traits\TableAware;
use Gibbon\Domain\QueryCriteria;
use Gibbon\Domain\QueryableGateway;

class ReferenceGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'higherEducationReference';
    private static $primaryKey = 'higherEducationReferenceID';

    public function queryReferences($criteria, $gibbonSchoolYearID, $search = null)
    {
        $query = $this
            ->newQuery()
            ->cols(['higherEducationReference.*', 'student.surname', 'student.preferredName', 'gibbonYearGroup.nameShort AS yearGroup', 'gibbonFormGroup.nameShort AS formGroup', 'gibbonSchoolYear.name AS schoolYear'])
            ->from($this->getTableName())
            ->innerJoin('gibbonPerson AS student','higherEducationReference.gibbonPersonID=student.gibbonPersonID')
            ->innerJoin('gibbonStudentEnrolment','higherEducationReference.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID')
            ->leftJoin('gibbonSchoolYear','gibbonSchoolYear.gibbonSchoolYearID=student.gibbonSchoolYearIDClassOf')
            ->leftJoin('gibbonYearGroup','gibbonStudentEnrolment.gibbonYearGroupID=gibbonYearGroup.gibbonYearGroupID')
            ->leftJoin('gibbonFormGroup','gibbonStudentEnrolment.gibbonFormGroupID=gibbonFormGroup.gibbonFormGroupID')
            ->where('gibbonStudentEnrolment.gibbonSchoolYearID=:gibbonSchoolYearID')
            ->where('higherEducationReference.gibbonSchoolYearID=:gibbonSchoolYearID')
            ->bindValue('gibbonSchoolYearID', $gibbonSchoolYearID)
            ->where('student.status=\'Full\'');

        if (!empty($search)) {
            $query->where("((surname LIKE CONCAT('%', :search, '%') OR preferredName LIKE CONCAT('%', :search, '%') OR username LIKE CONCAT('%', :search, '%')))")
                ->bindValue('search', $search);
        }

        return $this->runQuery($query, $criteria);
    }

    public function queryReferenceComponents($criteria, $gibbonSchoolYearID, $gibbonPersonID)
    {
        $query = $this
            ->newQuery()
            ->cols(['higherEducationReference.timestamp', 'higherEducationReference.type AS typeReference', 'higherEducationReferenceComponent.*', 'surname', 'preferredName'])
            ->from('higherEducationReferenceComponent')
            ->innerJoin('higherEducationReference','higherEducationReferenceComponent.higherEducationReferenceID=higherEducationReference.higherEducationReferenceID')
            ->innerJoin('gibbonPerson','higherEducationReference.gibbonPersonID=gibbonPerson.gibbonPersonID')
            ->where('higherEducationReference.gibbonSchoolYearID=:gibbonSchoolYearID')
            ->bindValue('gibbonSchoolYearID', $gibbonSchoolYearID)
            ->where('gibbonPerson.status=\'Full\'')
            ->where('higherEducationReference.status=\'In Progress\'')
            ->where('higherEducationReferenceComponent.gibbonPersonID=:gibbonPersonID')
            ->bindValue('gibbonPersonID', $gibbonPersonID);

        return $this->runQuery($query, $criteria);
    }

    public function queryReferenceComponentsByReference($criteria, $higherEducationReferenceID, $higherEducationReferenceComponentID = null)
    {
        $query = $this
            ->newQuery()
            ->cols(['higherEducationReferenceComponent.*', 'surname', 'preferredName'])
            ->from('higherEducationReferenceComponent')
            ->innerJoin('gibbonPerson','higherEducationReferenceComponent.gibbonPersonID=gibbonPerson.gibbonPersonID')
            ->where('higherEducationReferenceComponent.higherEducationReferenceID=:higherEducationReferenceID')
            ->bindValue('higherEducationReferenceID', $higherEducationReferenceID);

        if (!empty($higherEducationReferenceComponentID)) {
            $query->where('NOT higherEducationReferenceComponent.higherEducationReferenceComponentID=:higherEducationReferenceComponentID')
                ->bindValue('higherEducationReferenceComponentID', $higherEducationReferenceComponentID);
        }

        return $this->runQuery($query, $criteria);
    }

    public function  queryReferencesByStudent($criteria, $gibbonPersonID)
    {
        $query = $this
            ->newQuery()
            ->cols(['higherEducationReference.*', "GROUP_CONCAT(DISTINCT surname, ', ', preferredName ORDER BY surname, preferredName ASC SEPARATOR '<br/>') AS referees"])
            ->from($this->getTableName())
            ->leftJoin('higherEducationReferenceComponent', 'higherEducationReferenceComponent.higherEducationReferenceID=higherEducationReference.higherEducationReferenceID')
            ->leftJoin('gibbonPerson', 'higherEducationReferenceComponent.gibbonPersonID=gibbonPerson.gibbonPersonID')
            ->where('higherEducationReference.gibbonPersonID=:gibbonPersonID')
            ->bindValue('gibbonPersonID', $gibbonPersonID)
            ->groupBy(['higherEducationReference.higherEducationReferenceID']);

        return $this->runQuery($query, $criteria);
    }

}
