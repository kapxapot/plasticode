<?php

namespace Plasticode\Models;

class Tag extends DbModel
{
	public static function getIdsByTag($entityType, $tag) {
		return self::getMany(function ($q) use ($entityType, $tag) {
			return $q
				->where('entity_type', $entityType)
				->where('tag', $tag);
		})->extract('entity_id');
	}
	
	public static function getByEntity($entityType, $entityId)
	{
	    return self::getMany(function ($q) use ($entityType, $entityId) {
            return $q
        		->where('entity_type', $entityType)
        		->where('entity_id', $entityId);
	    });
	}
	
	public static function deleteByEntity($entityType, $entityId)
	{
	    self::deleteBy(function ($q) use ($entityType, $entityId) {
            return $q
        		->where('entity_type', $entityType)
        		->where('entity_id', $entityId);
	    });
	}
}
