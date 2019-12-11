<?php

namespace Plasticode;

class Contained extends SettingsProvider
{
    /**
     * Returns object / array from container by property name.
     *
     * @param string $property
     * @return mixed
     */
    public function __get(string $property)
    {
        if ($this->container->{$property}
            || is_array($this->container->{$property})
        ) {
            return $this->container->{$property};
        }
    }
}
