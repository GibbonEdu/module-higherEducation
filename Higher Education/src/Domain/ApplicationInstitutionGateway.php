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

class ApplicationInstitutionGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'higherEducationApplicationInstitution';
    private static $primaryKey = 'higherEducationApplicationInstitutionID';

    public function queryApplicationInstitutions($criteria, $higherEducationApplicationID)
    {
        $query = $this
            ->newQuery()
            ->cols(['higherEducationApplicationInstitution.*', 'higherEducationInstitution.name as institution', 'higherEducationMajor.name as major', 'rank', 'rating'])
            ->from($this->getTableName())
            ->innerJoin('higherEducationInstitution','higherEducationApplicationInstitution.higherEducationInstitutionID=higherEducationInstitution.higherEducationInstitutionID')
            ->innerJoin('higherEducationMajor','higherEducationApplicationInstitution.higherEducationMajorID=higherEducationMajor.higherEducationMajorID')
            ->where('higherEducationApplicationInstitution.higherEducationApplicationID=:higherEducationApplicationID')
            ->bindValue('higherEducationApplicationID', $higherEducationApplicationID);

        return $this->runQuery($query, $criteria);
    }

}
