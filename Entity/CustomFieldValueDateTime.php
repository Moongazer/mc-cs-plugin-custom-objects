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

namespace MauticPlugin\CustomObjectsBundle\Entity;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Mapping as ORM;
use Mautic\CoreBundle\Doctrine\Mapping\ClassMetadataBuilder;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use MauticPlugin\CustomObjectsBundle\Entity\CustomItemValue;
use MauticPlugin\CustomObjectsBundle\Entity\CustomFieldValueStandard;
use DateTimeInterface;

class CustomFieldValueDateTime extends CustomFieldValueStandard
{
    /**
     * @var DateTimeInterface|null
     */
    private $value;

    /**
     * @param CustomField            $customField
     * @param CustomItem             $customItem
     * @param DateTimeInterface|null $value
     */
    public function __construct(CustomField $customField, CustomItem $customItem, ?DateTimeInterface $value = null)
    {
        parent::__construct($customField, $customItem);

        $this->value = $value;
    }

    /**
     * @param ORM\ClassMetadata $metadata
     */
    public static function loadMetadata(ORM\ClassMetadata $metadata)
    {
        $builder = new ClassMetadataBuilder($metadata);
        $builder->setTable('custom_field_value_datetime');
        $builder->addIndex(['value'], 'value_index');
        $builder->addNullableField('value', Type::DATETIME);
        
        parent::addReferenceColumns($builder);
    }

    /**
     * @param DateTimeInterface|null $value
     */
    public function setValue($value = null)
    {
        $this->value = $value;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getValue()
    {
        return $this->value;
    }
}