<?php

namespace Rad\Network\Session\Handler;

use SessionHandler;

/**
 * Encrypted Session Handler
 *
 * @package Rad\Network\Session\Handler
 */
class EncryptedSessionHandler extends SessionHandler
{
    protected $cipher;
    protected $cipherMode;
    protected $secretKey;

    /**
     * Rad\Network\Session\Handler\EncryptedSessionHandler constructor
     *
     * @param string $secretKey
     * @param string $cipher
     * @param string $cipherMode
     */
    public function __construct($secretKey, $cipher = MCRYPT_RIJNDAEL_256, $cipherMode = MCRYPT_MODE_ECB)
    {
        $this->secretKey = $secretKey;
        $this->cipher = $cipher;
        $this->cipherMode = $cipherMode;
    }

    /**
     * Read session data
     *
     * @param string $sessionId The session id to read data for.
     *
     * @link http://php.net/manual/en/sessionhandlerinterface.read.php
     * @return string Returns an encoded string of the read data.
     * If nothing was read, it must return an empty string.
     * Note this value is returned internally to PHP for processing.
     */
    public function read($sessionId)
    {
        $iv = mcrypt_create_iv(mcrypt_get_iv_size($this->cipher, $this->cipherMode), MCRYPT_RAND);

        return mcrypt_decrypt($this->cipher, $this->secretKey, parent::read($sessionId), $this->cipherMode, $iv);
    }

    /**
     * Write session data
     *
     * @param string $sessionId    The session id.
     * @param string $sessionData  The encoded session data. This data is the
     *                             result of the PHP internally encoding
     *                             the $_SESSION superglobal to a serialized
     *                             string and passing it as this parameter.
     *                             Please note sessions use an alternative serialization method.
     *
     * @link http://php.net/manual/en/sessionhandlerinterface.write.php
     * @return bool The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     */
    public function write($sessionId, $sessionData)
    {
        $iv = mcrypt_create_iv(mcrypt_get_iv_size($this->cipher, $this->cipherMode), MCRYPT_RAND);

        return parent::write(
            $sessionId,
            mcrypt_encrypt($this->cipher, $this->secretKey, $sessionData, $this->cipherMode, $iv)
        );
    }
}
