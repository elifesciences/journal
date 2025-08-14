<?php

namespace eLife\Patterns;

trait ArrayFromProperties
{
    final public function toArray() : array
    {
        $vars = [];

        foreach (get_object_vars($this) as $key => $value) {
            if ('_' === substr($key, 0, 1)) {
                continue;
            }

            $value = $this->handleValue($value);

            if (null !== $value && [] !== $value) {
                $vars[$key] = $value;
            }
        }

        return $vars;
    }

    private function handleValue($value)
    {
        if (is_array($value)) {
            foreach ($value as $subKey => $subValue) {
                $value[$subKey] = $this->handleValue($subValue);
            }
        }

        if ($value instanceof CastsToArray) {
            return $value->toArray();
        }

        return $value;
    }
}
