<?php

namespace Plasticode\Util;

use Plasticode\Traits\PropertyAccess;
use Webmozart\Assert\Assert;

class Sort
{
    use PropertyAccess;

    const ASC = 'asc';
    const DESC = 'desc';
    
    const NUMBER = 'number';
    const STRING = 'string';
    const NULL = 'null';
    const BOOL = 'bool';
    const DATE = 'date';
    
    /**
     * Sorts array by multiple fields.
     * 
     * @param SortStep[] $steps
     */
    public static function multi(array $array, array $steps) : array
    {
        if (empty($array)) {
            return [];
        }

        if (empty($steps)) {
            return $array;
        }

        Assert::allIsInstanceOf($steps, SortStep::class);

        usort(
            $array,
            function($itemA, $itemB) use ($steps) {
                foreach ($steps as $step) {
                    $dir = $step->isDesc() ? self::DESC : self::ASC;
                    $type = $step->getType();

                    $propA = $step->getValue($itemA);
                    $propB = $step->getValue($itemB);
                    
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
     * @param string|\Closure $by
     * @param string|null $dir Sort::ASC (default) or Sort::DESC
     * @param string|null $type null = Sort::NUMBER
     * 
     * @return array
     */
    public static function by(
        array $array,
        $by,
        ?string $dir = null,
        ?string $type = null
    ) : array
    {
        /** @var string|null */
        $field = null;

        /** @var \Closure|null */
        $closure = null;

        if ($by instanceof \Closure) {
            $closure = $by;
        } else {
            $field = $by;
        }

        $steps = [
            new SortStep(
                $field,
                $closure,
                $dir === self::DESC,
                $type
            )
        ];
        
        return self::multi($array, $steps);
    }
    
    /**
     * Alias for by($array, $field).
     * 
     * @param string|\Closure $by
     */
    public static function asc(array $array, $by, ?string $type = null) : array
    {
        return self::by($array, $by, null, $type);
    }
    
    /**
     * Shortcut for by($array, $field, Sort::DESC).
     * 
     * @param string|\Closure $by
     */
    public static function desc(array $array, $by, ?string $type = null) : array
    {
        return self::by($array, $by, self::DESC, $type);
    }
    
    /**
     * Sort by $field as strings.
     * 
     * @param string|\Closure $by
     */
    public static function byStr(array $array, $by, ?string $dir = null) : array
    {
        return self::by($array, $by, $dir, self::STRING);
    }
    
    /**
     * Sort ascending by $field as strings.
     * 
     * @param string|\Closure $by
     */
    public static function ascStr(array $array, $by) : array
    {
        return self::byStr($array, $by);
    }
    
    /**
     * Sort descending by $field as strings.
     * 
     * @param string|\Closure $by
     */
    public static function descStr(array $array, $by) : array
    {
        return self::byStr($array, $by, self::DESC);
    }
}
