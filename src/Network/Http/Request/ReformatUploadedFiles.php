<?php

namespace Rad\Network\Http\Request;

use ArrayObject;

/**
 * Reformat Uploaded Files
 *
 * @package Rad\Network\Http
 */
class ReformatUploadedFiles extends ArrayObject
{
    /**
     * {$@inheritdoc}
     *
     * @return ReformatUploadedFiles
     */
    public function offsetGet($offset)
    {
        return $this->normalize(parent::offsetGet($offset));
    }

    /**
     * Normalize entry
     *
     * @param array $entry
     *
     * @return ReformatUploadedFiles
     */
    protected function normalize(array $entry)
    {
        if (isset($entry['name']) && is_array($entry['name'])) {
            $files = [];
            foreach ($entry['name'] as $index => $name) {
                if ($entry['error'][$index] === UPLOAD_ERR_NO_FILE) {
                    continue;
                }

                $files[$index] = [
                    'name' => $name,
                    'tmp_name' => $entry['tmp_name'][$index],
                    'size' => $entry['size'][$index],
                    'type' => $entry['type'][$index],
                    'error' => $entry['error'][$index]
                ];
            }

            return new self($files);
        }

        return $entry;
    }
}
