<?php

namespace Plasticode\Validation\Rules;

class NonRecursiveParent extends ContainerRule
{
    private $table;
    private $id;
    private $parentField;

    /**
     * Creates new instance.
     */
    public function __construct($table, $id, $parentField = null)
    {
        $this->table = $table;
        $this->id = $id;
        $this->parentField = $parentField;
    }
    
    public function validate($input)
    {
        parent::validate($input);

        return ($this->id == null) || !$this->container->db->isRecursiveParent(
            $this->table, $this->id, $input, $this->parentField
        );
    }
}
