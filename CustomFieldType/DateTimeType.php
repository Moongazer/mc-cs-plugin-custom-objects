<?php

declare(strict_types=1);

/*
 * @copyright   2019 Mautic, Inc. All rights reserved
 * @author      Mautic, Inc.
 *
 * @link        https://mautic.com
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\CustomObjectsBundle\CustomFieldType;

use MauticPlugin\CustomObjectsBundle\CustomFieldType\DataTransformer\DateTimeTransformer;
use MauticPlugin\CustomObjectsBundle\Entity\CustomField;
use MauticPlugin\CustomObjectsBundle\Entity\CustomFieldValueDateTime;
use MauticPlugin\CustomObjectsBundle\Entity\CustomFieldValueInterface;
use MauticPlugin\CustomObjectsBundle\Entity\CustomItem;
use MauticPlugin\CustomObjectsBundle\Exception\InvalidValueException;
use Symfony\Component\Form\DataTransformerInterface;

class DateTimeType extends AbstractCustomFieldType
{
    /**
     * @var string
     */
    public const NAME = 'custom.field.type.datetime';

    public const TABLE_NAME = 'custom_field_value_datetime';

    /**
     * @var string
     */
    protected $key = 'datetime';

    /**
     * {@inheritdoc}
     */
    protected $formTypeOptions = [
        'widget' => 'single_text',
        'format' => 'yyyy-MM-dd HH:mm',
        'attr'   => [
            'data-toggle' => 'datetime',
        ],
    ];

    /**
     * @param CustomField $customField
     * @param CustomItem  $customItem
     * @param mixed|null  $value
     *
     * @return CustomFieldValueInterface
     *
     * @throws InvalidValueException
     */
    public function createValueEntity(CustomField $customField, CustomItem $customItem, $value = null): CustomFieldValueInterface
    {
        if ($value) {
            try {
                $value = new \DateTimeImmutable($value);
            } catch (\Throwable $e) {
                throw new InvalidValueException($e->getMessage());
            }
        } else {
            $value = null;
        }

        return new CustomFieldValueDateTime($customField, $customItem, $value);
    }

    /**
     * @return string
     */
    public function getSymfonyFormFieldType(): string
    {
        return \Symfony\Component\Form\Extension\Core\Type\DateTimeType::class;
    }

    /**
     * @return string
     */
    public function getEntityClass(): string
    {
        return CustomFieldValueDateTime::class;
    }

    /**
     * @return mixed[]
     */
    public function getOperators(): array
    {
        $allOperators     = parent::getOperators();
        $allowedOperators = array_flip(['=', '!=', 'gt', 'gte', 'lt', 'lte', 'empty', '!empty']);

        return array_intersect_key($allOperators, $allowedOperators);
    }

    /**
     * {@inheritdoc}
     */
    public function createDefaultValueTransformer(): DataTransformerInterface
    {
        return new DateTimeTransformer();
    }
}
