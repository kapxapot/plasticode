<?php

namespace Plasticode\Util;

use Plasticode\Util\Traits\PropertyAccess;

class Sort
{
    use PropertyAccess;

    const ASC = 'asc';
    const DESC = 'desc';
    
    const STRING = 'string';
    const NULL = 'null';
    const BOOL = 'bool';
    const DATE = 'date';
    
    /**
     * Sorts array by multiple fields.
     * 
     * Example config:
     *
     * $sorts = [
     *  'remote_online' => [], // dir = asc by default
     *  'priority' => [ 'dir' => 'desc' ], // type is numeric by default
     *  'priority_game' => [ 'dir' => 'desc' ],
     *  'remote_viewers' => [ 'dir' => 'desc' ],
     *  'title' => [ 'type' => 'string' ],
     * ];
     */
    public static function multi(array $array, array $sorts) : array
    {
        if (empty($array)) {
            return [];
        }

        if (empty($sorts)) {
            return $array;
        }

        usort(
            $array,
            function($itemA, $itemB) use ($sorts) {
                foreach ($sorts as $field => $settings) {
                    $dir = $settings['dir'] ?? self::ASC;
                    $type = $settings['type'] ?? null;

                    $propA = self::getProperty($itemA, $field);
                    $propB = self::getProperty($itemB, $field);
                    
                    switch ($type) {
                        case self::STRING:
                            $cmp = strcasecmp($propA, $propB);
                            break;
                        
                        case self::NULL:
                            $a = is_null($propA);
                            $b = is_null($propB);
                            
                            if ($a && $b || !$a && !$b) {
                                $cmp = 0;
                            } elseif ($a && !$b) {
                                $cmp = -1;
                            } elseif (!$a && $b) {
                                $cmp = 1;
                            }
                            break;
                        
                        case self::BOOL:
                            $a = $propA;
                            $b = $propB;
                            
                            if ($a && !$b) {
                                $cmp = 1;
                            } elseif (!$a && $b) {
                                $cmp = -1;
                            } else {
                                $cmp = 0;
                            }
                            break;
                        
                        case self::DATE:
                            $a = strtotime($propA);
                            $b = strtotime($propB);

                            $cmp = $a - $b;
                            break;
                        
                        default:
                            $cmp = $propA - $propB;
                    }

                    if ($cmp != 0) {
                        if ($dir == self::DESC) {
                            $cmp = -$cmp;
                        }
                        
                        return ($cmp > 0) ? 1 : -1;
                    }
                }
                
                return 0;
            }
        );
        
        return $array;
    }

    /**
     * Sorts array by $field asc/desc.
     * 
     * @param array $array
     * @param string $field
     * @param string|null $dir 'asc' or 'desc'. null = 'asc'
     * @param string|null $type null = numeric
     * 
     * @return array
     */
    public static function by(array $array, string $field, ?string $dir = null, ?string $type = null) : array
    {
        $sorts = [
            $field => [
                'dir' => $dir ?? self::ASC,
                'type' => $type
            ],
        ];
        
        return self::multi($array, $sorts);
    }
    
    /**
     * Alias for by($array, $field).
     */
    public static function asc(array $array, string $field, ?string $type = null) : array
    {
        return self::by($array, $field, null, $type);
    }
    
    /**
     * Shortcut for by($array, $field, 'desc').
     */
    public static function desc(array $array, string $field, ?string $type = null) : array
    {
        return self::by($array, $field, self::DESC, $type);
    }
    
    /**
     * Sort by $field as strings.
     */
    public static function byStr(array $array, string $field, ?string $dir = null) : array
    {
        return self::by($array, $field, $dir, self::STRING);
    }
    
    /**
     * Sort ascending by $field as strings.
     */
    public static function ascStr(array $array, string $field) : array
    {
        return self::byStr($array, $field);
    }
    
    /**
     * Sort descending by $field as strings.
     */
    public static function descStr(array $array, string $field) : array
    {
        return self::byStr($array, $field, self::DESC);
    }
}
