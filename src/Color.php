<?php

namespace BoomDraw\DummyImage;

use InvalidArgumentException;

class Color
{
    /**
     * @var    array $rgb
     * @access private
     * @desc    array for RGB colors
     */
    private $rgb = ['r' => 0, 'g' => 0, 'b' => 0];

    /**
     * @var    string $hex
     * @access private
     * @desc    variable for HTML HEX color
     */
    private $hex = '';

    /**
     * @var    array $cmyk
     * @access private
     * @desc    array for cmyk colors
     */
    private $cmyk = array('c' => 0, 'm' => 0, 'y' => 0, 'b' => 0);

    /**
     * Sets the RGB values
     * @param int $red number from 0-255 for blue color value
     * @param int $green number from 0-255 for green color value
     * @param int $blue number from 0-255 for blue color value
     * @access public
     * @desc Sets the RGB values
     */
    public function set_rgb($red, $green, $blue)
    {

        $this->rgb['r'] = $red;
        $this->rgb['g'] = $green;
        $this->rgb['b'] = $blue;

        $this->convert_rgb_to_cmyk();
        $this->convert_rgb_to_hex();
    }

    /**
     * Sets the HEX HTML color value
     * @param string $hex 6,3,2, or 1 characters long.
     * @access public
     * @desc Sets the HEX HTML color value like ffff00. It will convert shorthand to a 6 digit hex.
     */
    public function set_hex($hex)
    {
        $hex = strtolower($hex);
        $hex = preg_replace('/#/', '', $hex); //Strips out the # character
        $hexlength = strlen($hex);
        $input = $hex;
        switch ($hexlength) {
            case 1:
                $hex = $input . $input . $input . $input . $input . $input;
                break;
            case 2:
                $hex = $input[0] . $input[1] . $input[0] . $input[1] . $input[0] . $input[1];
                break;
            case 3:
                $hex = $input[0] . $input[0] . $input[1] . $input[1] . $input[2] . $input[2];
                break;
        }
        $this->hex = $hex;

        $this->convert_hex_to_rgb();
        $this->convert_rgb_to_cmyk();
    }

    /**
     * Sets the HTML color name, converting it to a 6 digit hex code.
     * @param string $name The name of the color.
     * @access public
     * @desc Sets the HTML color name, converting it to a 6 digit hex code.
     */
    public function set_name($name)
    {
        $this->set_hex($this->convert_name_to_hex($name));
    }

    /**
     * Sets the CMYK color values
     * @param int $c number from 0-100 for c color value
     * @param int $m number from 0-100 for m color value
     * @param int $y number from 0-100 for y color value
     * @param int $b number from 0-100 for b color value
     * @access public
     * @desc Sets the CMYK color values
     */
    public function set_cmyk($c, $m, $y, $b)
    {
        $this->cmyk['c'] = $c;
        $this->cmyk['m'] = $m;
        $this->cmyk['y'] = $y;
        $this->cmyk['b'] = $b;

        $this->convert_cmyk_to_rgb();
        $this->convert_rgb_to_hex();
    }

    /**
     * Sets the pantone color value
     * @param string $pantone_name name of the pantone color
     * @access public
     * @desc Sets the pantone color value
     */
    public function set_pantone($pantone_name)
    {
        $this->pantone = $pantone_name;
        $this->cmyk['c'] = $this->pantone_pallete[$pantone_name]['c'];
        $this->cmyk['m'] = $this->pantone_pallete[$pantone_name]['m'];
        $this->cmyk['y'] = $this->pantone_pallete[$pantone_name]['y'];
        $this->cmyk['b'] = $this->pantone_pallete[$pantone_name]['b'];

        $this->convert_cmyk_to_rgb();
        $this->convert_rgb_to_hex();
    }

    /**
     * Sets the pantone pc color value
     * @param string $pantone_name_pc name of the pantone pc color
     * @access public
     * @desc Sets the pantone pc color value
     */
    public function set_pantone_pc($pantone_name)
    {
        $this->pantone_pc = $pantone_name;
        $this->cmyk['c'] = $this->pantone_pallete_pc[$pantone_name]['c'];
        $this->cmyk['m'] = $this->pantone_pallete_pc[$pantone_name]['m'];
        $this->cmyk['y'] = $this->pantone_pallete_pc[$pantone_name]['y'];
        $this->cmyk['b'] = $this->pantone_pallete_pc[$pantone_name]['b'];

        $this->convert_cmyk_to_rgb();
        $this->convert_rgb_to_hex();
    }

    //include("pantone.color.class.php");

    /**
     * Returns the RGB values of a set color
     * @return array $rgb color values of red ($rgb['r']), green ($rgb['green') and blue ($rgb['b'])
     * @access public
     * @desc Returns the RGB values of a set color
     */
    public function get_rgb($val)
    {
        if ($val) {
            return $this->rgb[$val];
        } else {
            return $this->rgb;
        }
    }

    /**
     * Returns the HEX HTML color value of a set color
     * @return string $hex HEX HTML color value
     * @access public
     * @desc Returns the HEX HTML color value of a set color
     */
    public function get_hex()
    {
        return $this->hex;
    }

    /**
     * Returns the CMYK values of a set color
     * @return array $cmyk color values of c ($cmyk['c']), m ($cmyk['m'), y ($cmyk['blue']) and b ($cmyk['b'])
     * @access public
     * @desc Returns the CMYK values of a set color
     */
    public function get_cmyk()
    {
        return $this->cmyk;
    }

    /**
     * Converts the RGB colors to HEX HTML colors
     * @access private
     * @desc Converts the RGB colors to HEX HTML colors
     */
    private function convert_rgb_to_hex()
    {
        $this->hex = $this->hex_trip[$this->rgb['r']] . $this->hex_trip[$this->rgb['g']] . $this->hex_trip[$this->rgb['b']];
    }

    /**
     * Converts the RGB colors to CMYK colors
     * @access private
     * @desc Converts the RGB colors to CMYK colors
     */
    private function convert_rgb_to_cmyk()
    {
        $c = (255 - $this->rgb['r']) / 255.0 * 100;
        $m = (255 - $this->rgb['g']) / 255.0 * 100;
        $y = (255 - $this->rgb['b']) / 255.0 * 100;

        $b = min(array($c, $m, $y));
        $c = $c - $b;
        $m = $m - $b;
        $y = $y - $b;

        $this->cmyk = array('c' => $c, 'm' => $m, 'y' => $y, 'b' => $b);
    }

    /**
     * Converts the CMYK colors to RGB colors
     * @access private
     * @desc Converts the CMYK colors to RGB colors
     */
    private function convert_cmyk_to_rgb()
    {
        $red = $this->cmyk['c'] + $this->cmyk['b'];
        $green = $this->cmyk['m'] + $this->cmyk['b'];
        $blue = $this->cmyk['y'] + $this->cmyk['b'];

        $red = ($red - 100) * (-1);
        $green = ($green - 100) * (-1);
        $blue = ($blue - 100) * (-1);

        $red = round($red / 100 * 255, 0);
        $green = round($green / 100 * 255, 0);
        $blue = round($blue / 100 * 255, 0);

        $this->rgb['r'] = $red;
        $this->rgb['g'] = $green;
        $this->rgb['b'] = $blue;
    }

    /**
     * Converts the HTML HEX colors to RGB colors
     * @access private
     * @desc Converts the HTML HEX colors to RGB colors
     * @url http://css-tricks.com/snippets/php/convert-hex-to-rgb/
     */
    private function convert_hex_to_rgb()
    {
        $red = substr($this->hex, 0, 2);
        $green = substr($this->hex, 2, 2);
        $blue = substr($this->hex, 4, 2);
        $this->rgb['r'] = hexdec($red);
        $this->rgb['g'] = hexdec($green);
        $this->rgb['b'] = hexdec($blue);
    }

    /**
     * Converts HTML color name to 6 digit HEX value.
     * @access private
     * @param string $name One of the offical HTML color names.
     * @desc Converts HTML color name to 6 digit HEX value.
     * @url https://www.w3schools.com/colors/colors_names.asp
     * @return string
     */
    private function convert_name_to_hex($name)
    {
        $color_names = array_replace_recursive([
            'aliceblue' => 'f0f8ff',
            'antiquewhite' => 'faebd7',
            'aqua' => '00ffff',
            'aquamarine' => '7fffd4',
            'azure' => 'f0ffff',
            'beige' => 'f5f5dc',
            'bisque' => 'ffe4c4',
            'black' => '000000',
            'blanchedalmond' => 'ffebcd',
            'blue' => '0000ff',
            'blueviolet' => '8a2be2',
            'brown' => 'a52a2a',
            'burlywood' => 'deb887',
            'cadetblue' => '5f9ea0',
            'chartreuse' => '7fff00',
            'chocolate' => 'd2691e',
            'coral' => 'ff7f50',
            'cornflowerblue' => '6495ed',
            'cornsilk' => 'fff8dc',
            'crimson' => 'dc143c',
            'cyan' => '00ffff',
            'darkblue' => '00008b',
            'darkcyan' => '008b8b',
            'darkgoldenrod' => 'b8860b',
            'darkgray' => 'a9a9a9',
            'darkgrey' => 'a9a9a9',
            'darkgreen' => '006400',
            'darkkhaki' => 'bdb76b',
            'darkmagenta' => '8b008b',
            'darkolivegreen' => '556b2f',
            'darkorange' => 'ff8c00',
            'darkorchid' => '9932cc',
            'darkred' => '8b0000',
            'darksalmon' => 'e9967a',
            'darkseagreen' => '8fbc8f',
            'darkslateblue' => '483d8b',
            'darkslategray' => '2f4f4f',
            'darkslategrey' => '2f4f4f',
            'darkturquoise' => '00ced1',
            'darkviolet' => '9400d3',
            'deeppink' => 'ff1493',
            'deepskyblue' => '00bfff',
            'dimgray' => '696969',
            'dimgrey' => '696969',
            'dodgerblue' => '1e90ff',
            'firebrick' => 'b22222',
            'floralwhite' => 'fffaf0',
            'forestgreen' => '228b22',
            'fuchsia' => 'ff00ff',
            'gainsboro' => 'dcdcdc',
            'ghostwhite' => 'f8f8ff',
            'gold' => 'ffd700',
            'goldenrod' => 'daa520',
            'gray' => '808080',
            'grey' => '808080',
            'green' => '008000',
            'greenyellow' => 'adff2f',
            'honeydew' => 'f0fff0',
            'hotpink' => 'ff69b4',
            'indianred ' => 'cd5c5c',
            'indigo ' => '4b0082',
            'ivory' => 'fffff0',
            'khaki' => 'f0e68c',
            'lavender' => 'e6e6fa',
            'lavenderblush' => 'fff0f5',
            'lawngreen' => '7cfc00',
            'lemonchiffon' => 'fffacd',
            'lightblue' => 'add8e6',
            'lightcoral' => 'f08080',
            'lightcyan' => 'e0ffff',
            'lightgoldenrodyellow' => 'fafad2',
            'lightgray' => 'd3d3d3',
            'lightgrey' => 'd3d3d3',
            'lightgreen' => '90ee90',
            'lightpink' => 'ffb6c1',
            'lightsalmon' => 'ffa07a',
            'lightseagreen' => '20b2aa',
            'lightskyblue' => '87cefa',
            'lightslategray' => '778899',
            'lightslategrey' => '778899',
            'lightsteelblue' => 'b0c4de',
            'lightyellow' => 'ffffe0',
            'lime' => '00ff00',
            'limegreen' => '32cd32',
            'linen' => 'faf0e6',
            'magenta' => 'ff00ff',
            'maroon' => '800000',
            'mediumaquamarine' => '66cdaa',
            'mediumblue' => '0000cd',
            'mediumorchid' => 'ba55d3',
            'mediumpurple' => '9370db',
            'mediumseagreen' => '3cb371',
            'mediumslateblue' => '7b68ee',
            'mediumspringgreen' => '00fa9a',
            'mediumturquoise' => '48d1cc',
            'mediumvioletred' => 'c71585',
            'midnightblue' => '191970',
            'mintcream' => 'f5fffa',
            'mistyrose' => 'ffe4e1',
            'moccasin' => 'ffe4b5',
            'navajowhite' => 'ffdead',
            'navy' => '000080',
            'oldlace' => 'fdf5e6',
            'olive' => '808000',
            'olivedrab' => '6b8e23',
            'orange' => 'ffa500',
            'orangered' => 'ff4500',
            'orchid' => 'da70d6',
            'palegoldenrod' => 'eee8aa',
            'palegreen' => '98fb98',
            'paleturquoise' => 'afeeee',
            'palevioletred' => 'db7093',
            'papayawhip' => 'ffefd5',
            'peachpuff' => 'ffdab9',
            'peru' => 'cd853f',
            'pink' => 'ffc0cb',
            'plum' => 'dda0dd',
            'powderblue' => 'b0e0e6',
            'purple' => '800080',
            'rebeccapurple' => '663399',
            'red' => 'ff0000',
            'rosybrown' => 'bc8f8f',
            'royalblue' => '4169e1',
            'saddlebrown' => '8b4513',
            'salmon' => 'fa8072',
            'sandybrown' => 'f4a460',
            'seagreen' => '2e8b57',
            'seashell' => 'fff5ee',
            'sienna' => 'a0522d',
            'silver' => 'c0c0c0',
            'skyblue' => '87ceeb',
            'slateblue' => '6a5acd',
            'slategray' => '708090',
            'slategrey' => '708090',
            'snow' => 'fffafa',
            'springgreen' => '00ff7f',
            'steelblue' => '4682b4',
            'tan' => 'd2b48c',
            'teal' => '008080',
            'thistle' => 'd8bfd8',
            'tomato' => 'ff6347',
            'turquoise' => '40e0d0',
            'violet' => 'ee82ee',
            'wheat' => 'f5deb3',
            'white' => 'ffffff',
            'whitesmoke' => 'f5f5f5',
            'yellow' => 'ffff00',
            'yellowgreen' => '9acd32'
        ], (config('dummyimage.color_names') ?? []));
        if (array_key_exists($name, $color_names)) {
            return $color_names[$name];
        } else {
            throw new InvalidArgumentException('Color name not found!');
        }
    }
}
