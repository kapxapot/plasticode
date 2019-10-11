<?php

namespace Plasticode\Validation\Rules;

class Unchanged extends ContainerRule
{
    private $table;
    private $id;

    /**
     * Creates new instance.
     */
    public function __construct($table, $id)
    {
        $this->table = $table;
        $this->id = $id;
    }
    
    public function validate($input)
    {
        parent::validate($input);
        
        $valid = true;
        
        if ($this->id) {
            $item = $this->container->db->getObj($this->table, $this->id);
            
            if ($item) {
                $valid = ($item->updated_at == $input);
            }
        }

        return $valid;
    }
}
