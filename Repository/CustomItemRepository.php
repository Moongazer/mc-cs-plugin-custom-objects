<?php

declare(strict_types=1);

/*
 * @copyright   2018 Mautic, Inc. All rights reserved
 * @author      Mautic, Inc.
 *
 * @link        https://mautic.com
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\CustomObjectsBundle\Repository;

use Mautic\CoreBundle\Entity\CommonRepository;
use MauticPlugin\CustomObjectsBundle\DTO\TableConfig;
use Doctrine\ORM\QueryBuilder;
use MauticPlugin\CustomObjectsBundle\Entity\CustomObject;
use Mautic\LeadBundle\Entity\Lead;
use MauticPlugin\CustomObjectsBundle\Entity\CustomItemXrefContact;

class CustomItemRepository extends CommonRepository
{
    const TABLE_ALIAS = 'CustomItem';

    /**
     * @param TableConfig $tableConfig
     * 
     * @return QueryBuilder
     */
    public function getTableDataQuery(TableConfig $tableConfig): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder(self::TABLE_ALIAS, self::TABLE_ALIAS.'.id');

        return $tableConfig->configureSelectQueryBuilder($queryBuilder, $this->getClassMetadata());
    }

    /**
     * @param TableConfig $tableConfig
     * 
     * @return QueryBuilder
     */
    public function getTableCountQuery(TableConfig $tableConfig): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder(self::TABLE_ALIAS, self::TABLE_ALIAS.'.id');

        return $tableConfig->configureCountQueryBuilder($queryBuilder, $this->getClassMetadata());
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param integer $userId
     * 
     * @return QueryBuilder
     */
    public function applyOwnerId(QueryBuilder $queryBuilder, int $userId): QueryBuilder
    {
        return $queryBuilder->andWhere(self::TABLE_ALIAS.'.createdBy', $userId);
    }

    /**
     * @param Lead         $contact
     * @param CustomObject $customObject
     * 
     * @return int
     */
    public function countItemsLinkedToContact(CustomObject $customObject, Lead $contact): int
    {
        $queryBuilder = $this->createQueryBuilder('ci', 'ci.id');
        $queryBuilder->select($queryBuilder->expr()->countDistinct('ci.id'));
        $queryBuilder->innerJoin('ci.contactReferences', 'cixctct');
        $queryBuilder->where('ci.customObject = :customObjectId');
        $queryBuilder->andWhere('cixctct.contact = :contactId');
        $queryBuilder->setParameter('customObjectId', $customObject->getId());
        $queryBuilder->setParameter('contactId', $contact->getId());
        $result = $queryBuilder->getQuery()->getSingleScalarResult();

        return (int) $result;
    }

    /**
     * @return string
     */
    public function getTableAlias()
    {
        return self::TABLE_ALIAS;
    }
}