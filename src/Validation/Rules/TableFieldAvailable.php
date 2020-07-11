<?php

namespace Plasticode\Validation\Rules;

abstract class TableFieldAvailable extends ContainerRule
{
    private $table;
    private $field;
    private $id;

    /**
     * Creates new instance.
     */
    public function __construct($table, $field, $id = null)
    {
        $this->table = $table;
        $this->field = $field;
        $this->id = $id;
    }

    public function validate($input)
    {
        parent::validate($input);

        $query = $this->container->db
            ->forTable($this->table)
            ->where($this->field, $input);

        if ($this->id) {
            $query = $query->whereNotEqual('id', $this->id);
        }

        return $query->count() == 0;
    }
}
