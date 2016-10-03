<?php
namespace randomhost\Minecraft;

use UnexpectedValueException;

/**
 * Provides common functionality shared between Mcstat classes.
 *
 * @author    Winston Weinert <WinstonOne@fastmail.fm>
 * @author    Ch'Ih-Yu <chi-yu@web.de>
 * @copyright 2016 random-host.com
 * @license   http://opensource.org/licenses/mit-license.html The MIT License (MIT)
 * @link      http://github.random-host.com/mcstat/
 */
class Common
{
    /**
     * Default network timeout.
     *
     * @const int
     */
    const NETWORK_TIMEOUT = 5;

    /**
     * Throws an Exception if given data is not found within stream resource.
     *
     * @param resource $fp     Stream resource.
     * @param string   $string String to expect.
     *
     * @throws UnexpectedValueException Thrown if given data is not found.
     */
    public static function expect($fp, $string)
    {
        $receivedString = '';
        for ($bytes = strlen($string), $cur = 0; $cur < $bytes; $cur++) {
            $receivedByte = fread($fp, 1);
            $expectedByte = $string[$cur];
            $receivedString .= $receivedByte;
            if ($receivedByte !== $expectedByte) {
                $errorMessage
                    = 'Expected ' . bin2hex($string) . ' but received ' .
                    bin2hex($receivedString);
                $errorMessage
                    .= ' problem byte: ' . bin2hex($receivedByte) .
                    ' (position ' . $cur . ')';
                throw new UnexpectedValueException($errorMessage);
            }
        }
    }
}
