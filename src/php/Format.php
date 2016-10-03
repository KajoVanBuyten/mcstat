<?php
namespace randomhost\Minecraft;

/**
 * Provides formatting for strings returned by the Minecraft server.
 *
 * @author    Anders G. Jørgensen <anders@spirit55555.dk>
 * @author    Ch'Ih-Yu <chi-yu@web.de>
 * @copyright 2016 random-host.com
 * @license   http://opensource.org/licenses/mit-license.html The MIT License (MIT)
 * @link      http://github.random-host.com/mcstat/
 */
class Format
{
    const REGEX = '/§([0-9a-fklmnor])/i';
    const START_TAG = '<span style="%s">';
    const CLOSE_TAG = '</span>';
    const CSS_COLOR = 'color: #';
    const EMPTY_TAGS = '/<[^\/>]*>([\s]?)*<\/[^>]*>/';

    /**
     * Maps Minecraft color codes to hexadecimal HTML color codes.
     *
     * @var string[]
     */
    private static $colors
        = array(
            '0' => '000000', // black
            '1' => '0000AA', // dark blue
            '2' => '00AA00', // dark green
            '3' => '00AAAA', // dark aqua
            '4' => 'AA0000', // dark red
            '5' => 'AA00AA', // dark purple
            '6' => 'FFAA00', // gold
            '7' => 'AAAAAA', // gray
            '8' => '555555', // dark gray
            '9' => '5555FF', // blue
            'a' => '55FF55', // green
            'b' => '55FFFF', // aqua
            'c' => 'FF5555', // red
            'd' => 'FF55FF', // light purple
            'e' => 'FFFF55', // yellow
            'f' => 'FFFFFF'  // white
        );

    /**
     * Maps Minecraft format codes to CSS styles.
     *
     * @var string[]
     */
    private static $formatting
        = array(
            'k' => '', // obfuscated
            'l' => 'font-weight: bold;', // bold
            'm' => 'text-decoration: line-through;', // strike through
            'n' => 'text-decoration: underline;', // underline
            'o' => 'font-style: italic;', // italic
            'r' => '' // reset
        );

    /**
     * Returns $string with all Minecraft color codes removed.
     *
     * @param string $string String containing Minecraft color codes.
     *
     * @return string
     */
    public static function clean($string)
    {
        $string = self::utf8Encode($string);
        $string = htmlspecialchars($string);

        return preg_replace(self::REGEX, '', $string);
    }

    /**
     * Encodes $string in UTF-8 if it's not already encoded.
     *
     * @param string $string Input string.
     *
     * @return string
     */
    private static function utf8Encode($string)
    {
        if (mb_detect_encoding($string) != 'UTF-8') {
            $string = utf8_encode($string);
        }
        return $string;
    }

    /**
     * Converts Minecraft formatting codes to HTML.
     *
     * @param string $text String containing Minecraft formatting codes.
     *
     * @return string
     */
    public static function convertToHTML($text)
    {
        $matches = array();

        $text = self::utf8Encode($text);
        $text = htmlspecialchars($text);

        // search for Minecraft formatting control codes
        preg_match_all(self::REGEX, $text, $matches);

        // all strings starting with a formatting control code
        $format = $matches[0];

        // formatting control codes only
        $formatCodes = $matches[1];

        // return plain text without control codes unmodified
        if (empty($format)) {
            return $text;
        }

        $openTags = 0;
        foreach ($format as $index => $color) {
            $formatCode = strtolower($formatCodes[$index]);

            if (isset(self::$colors[$formatCode])) {
                // normal color code
                $html = sprintf(
                    self::START_TAG,
                    self::CSS_COLOR . self::$colors[$formatCode]
                );
                // new color clears the other colors and formatting
                if ($openTags !== 0) {
                    $html = str_repeat(self::CLOSE_TAG, $openTags) . $html;
                    $openTags = 0;
                }
                $openTags++;
            } else {
                // other type of formatting
                switch ($formatCode) {
                    // "reset" code causes all open tags to be closed
                    case 'r':
                        $html = '';
                        if ($openTags !== 0) {
                            $html = str_repeat(self::CLOSE_TAG, $openTags);
                            $openTags = 0;
                        }
                        break;
                    // "obfuscated" won't work in CSS
                    case 'k':
                        $html = '';
                        break;
                    default:
                        $html = sprintf(
                            self::START_TAG,
                            self::$formatting[$formatCode]
                        );
                        $openTags++;
                        break;
                }
            }
            /*
             * Replace the color with the HTML code. We use preg_replace because
             * of the limit parameter.
             */
            $text = preg_replace('/' . $color . '/', $html, $text, 1);
        }

        // close remaining open tags
        if ($openTags !== 0) {
            $text = $text . str_repeat(self::CLOSE_TAG, $openTags);
        }

        /*
         * Return the text without empty HTML tags.
         *
         * This should clean up bad color formatting from the user.
         */
        return preg_replace(self::EMPTY_TAGS, '', $text);
    }
}
