<?php

namespace Rad\Utility;

/**
 * Inflection is a string transformation library.
 *
 * @package Rad\Utility
 */
class Inflection
{
    /**
     * Convert strings to CamelCase.
     *
     * @param string $word
     * @param bool   $uppercaseFirstLetter
     *
     * @return mixed|string
     */
    public static function camelize($word, $uppercaseFirstLetter = true)
    {
        $word = self::underscore($word);
        $word = str_replace(' ', '', ucwords(str_replace('_', ' ', $word)));

        if ($uppercaseFirstLetter === false) {
            $word = lcfirst($word);
        }

        return $word;
    }

    /**
     * Replace underscores with dashes in the string.
     *
     * @param string $word
     *
     * @return mixed
     */
    public static function dasherize($word)
    {
        return str_replace('_', '-', $word);
    }

    /**
     * Make an underscored, lowercase form from the expression in the string.
     *
     * @param string $word
     *
     * @return mixed
     */
    public static function underscore($word)
    {
        $word = preg_replace('/([A-Z]+)([A-Z][a-z])/', '\1_\2', $word);
        $word = preg_replace('/([a-z])([A-Z])/', '\1_\2', $word);

        return str_replace('-', '_', strtolower($word));
    }
}
