<?php

namespace Plasticode\Settings\Transformations;

use Plasticode\Interfaces\ArrayTransformationInterface;
use Plasticode\Util\Arrays;

/**
 * Transforms database tables metadata settings ('tables' settings section).
 */
class TablesTransformation implements ArrayTransformationInterface
{
    public function transformArray(array $data): array
    {
        if (!array_key_exists('tables', $data)) {
            return $data;
        }

        // merge public + private => fields
        foreach ($data['tables'] as $table => $tableSettings) {
            $public = $tableSettings['public'] ?? [];
            $private = $tableSettings['private'] ?? [];

            $fields = array_unique(array_merge($private, $public));
            Arrays::set($data, 'tables.' . $table . '.fields', $fields);
        }

        // flatten attributes
        foreach ($data['entities'] as $entity => $entitySettings) {
            $entityPath = 'entities.' . $entity;
            Arrays::set($data, $entityPath . '.alias', $entity);

            foreach ($entitySettings['columns'] as $column => $columnSettings) {
                $attributes = $columnSettings['attributes'] ?? [];

                foreach ($attributes as $attr) {
                    $attrPath = $entityPath . '.columns.' . $column . '.' . $attr;
                    Arrays::set($data, $attrPath, 'true');
                }
            }
        }

        // count sort index
        foreach ($data['entities'] as $entity => $entitySettings) {
            $entityPath = 'tables.' . $entity;
            $sort = Arrays::get($data, $entityPath . '.sort');

            if (is_null($sort)) {
                continue;
            }

            $count = 0;

            foreach ($entitySettings['columns'] as $column => $columnSettings) {
                if ($column == $sort) {
                    Arrays::set($data, $entityPath . '.sort_index', $count);
                    break;
                } elseif (!array_key_exists('hidden', $columnSettings)) {
                    $count++;
                }
            }
        }

        return $data;
    }
}
