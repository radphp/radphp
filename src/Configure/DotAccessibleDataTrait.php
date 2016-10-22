<?php

namespace Rad\Configure;

/**
 * DotAccessibleData Trait
 *
 * @package Rad\Configure
 */
trait DotAccessibleDataTrait
{
    public function set(array &$data, $identifier, $value)
    {
        $keys = explode('.', $identifier);

        while (count($keys) > 1) {
            $key = array_shift($keys);

            if (!isset($data[$key]) || !is_array($data[$key])) {
                $data[$key] = [];
            }

            $data = &$data[$key];
        }

        $data[array_shift($keys)] = $value;

        return $this;
    }

    public function get(array &$data, $identifier)
    {
        $ids = explode('.', $identifier);

        while ($current = array_shift($ids)) {
            if (is_array($data) && array_key_exists($current, $data)) {
                $data = &$data[$current];
            } else {
                return null;
            }
        }

        return $data;
    }

    private function merge($newData, &$data)
    {
        if (is_array($newData)) {
            foreach ($newData as $key => $value) {
                if (isset($data[$key])) {
                    $this->merge($value, $data[$key]);
                } else {
                    $data[$key] = $value;
                }
            }
        } else {
            $data = $newData;
        }
    }
}
