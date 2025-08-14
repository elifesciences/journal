<?php

namespace eLife\Patterns;

trait ComposedViewModel
{
    use ReadOnlyArrayAccess;

    public function toArray() : array
    {
        return $this->getViewModel()->toArray();
    }

    public function offsetExists($offset) : bool
    {
        return $this->getViewModel()->offsetExists($offset);
    }

    public function offsetGet($offset)
    {
        return $this->getViewModel()->offsetGet($offset);
    }

    abstract protected function getViewModel() : ViewModel;
}
