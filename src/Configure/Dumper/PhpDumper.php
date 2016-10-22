<?php

namespace Rad\Configure\Dumper;

use Rad\Configure\DumperInterface;
use Rad\Configure\Exception;
use SplFileInfo;

/**
 * Php Dumper
 *
 * @package Rad\Configure\Dumper
 */
class PhpDumper implements DumperInterface
{
    /**
     * @var string
     */
    protected $filename;

    /**
     * PhpDumper constructor.
     *
     * @param string $filename File name
     */
    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    /**
     * {@inheritdoc}
     */
    public function dump(array $data)
    {
        $dir = dirname($this->filename);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $fileInfo = new SplFileInfo($this->filename);

        if ($fileInfo->isFile()) {
            if ($fileInfo->isWritable()) {
                $fileObject = $fileInfo->openFile('w');
            } else {
                throw new Exception(sprintf('File "%s" is not writable.', $this->filename));
            }
        } else {
            $fileObject = $fileInfo->openFile('w');
        }

        if (null !== $fileObject->fwrite('<?php return ' . var_export($data, true) . ';')) {
            return true;
        }

        return false;
    }
}
