<?php

namespace BoomDraw\DummyImage;

use Response;
use InvalidArgumentException;
use Illuminate\Support\Facades\Storage;

class DummyImage
{
    private $format = null;
    private $output = null;

    /**
     * Ruquay K Calloway http://ruquay.com/sandbox/imagettf/ made a better function to find the coordinates of the text bounding box so I used it.
     *
     * @param $size
     * @param $text_angle
     * @param $fontfile
     * @param $text
     * @return array
     */
    private function imagettfbbox_t($size, $text_angle, $fontfile, $text)
    {
        // Compute size with a zero angle
        $coords = imagettfbbox($size, 0, $fontfile, $text);

        // Convert angle to radians
        $a = deg2rad($text_angle);

        // Compute some usefull values
        $ca = cos($a);
        $sa = sin($a);
        $ret = array();

        // Perform transformations
        for ($i = 0; $i < 7; $i += 2) {
            $ret[$i] = round($coords[$i] * $ca + $coords[$i + 1] * $sa);
            $ret[$i + 1] = round($coords[$i + 1] * $ca - $coords[$i] * $sa);
        }
        return $ret;
    }

    /**
     * @param string $buffer
     * @return string
     */
    private static function process_output_buffer($buffer = '')
    {
        $buffer = trim($buffer);
        if (strlen($buffer) == 0) {
            return '';
        }
        return $buffer;
    }

    /**
     * @param string $dimensions
     * @param string $format
     * @param string $bg_color
     * @param string $fg_color
     * @param string $text
     * @return $this
     */
    public function generate($dimensions = null, $format = null, $bg_color = null, $fg_color = null, $text = null): DummyImage
    {
        $dimensions = $dimensions ?? '800x600';
        $this->format = $format ?? 'png';
        $bg_color = $bg_color ?? 'ffffff';
        $fg_color = $fg_color ?? '000000';

        $background = new Color();
        $background->set_hex($bg_color);

        $foreground = new Color();
        $foreground->set_hex($fg_color);

        // Find the image dimensions
        if (substr_count($dimensions, ':') > 1) {
            throw new InvalidArgumentException('Too many colons in the dimension paramter! There should be 1 at most.');
        }

        if (strstr($dimensions, ':') && !strstr($dimensions, 'x')) {
            throw new InvalidArgumentException('To calculate a ratio you need to provide a height!');
        }

        $dimensions = explode('x', $dimensions);
        $width = preg_replace('/[^\d:\.]/i', '', $dimensions[0]);
        $height = $width;
        if ($dimensions[1] ?? false) {
            $height = preg_replace('/[^\d:\.]/i', '', $dimensions[1]);
        }
        if ($width < 1 || $height < 1) {
            throw new InvalidArgumentException('Too smal image dimensions!');
        }

        // If one of the dimensions has a colon in it, we can calculate the aspect ratio. Chances are the height will contain a ratio, so we'll check that first.
        if (preg_match('/:/', $height)) {
            $ratio = explode(':', $height);

            // If we only have one ratio value, set the other value to the same value of the first making it a ratio of 1
            if (!$ratio[1]) {
                $ratio[1] = $ratio[0];
            }

            if (!$ratio[0]) {
                $ratio[0] = $ratio[1];
            }

            $height = ($width * $ratio[1]) / $ratio[0];
        } else if (preg_match('/:/', $width)) {
            $ratio = explode(':', $width);
            //If we only have one ratio value, set the other value to the same value of the first making it a ratio of 1
            if (!$ratio[1]) {
                $ratio[1] = $ratio[0];
            }

            if (!$ratio[0]) {
                $ratio[0] = $ratio[1];
            }

            $width = ($height * $ratio[0]) / $ratio[1];
        }

        //Limit the size of the image to no more than an area of 16,000,000
        $area = $width * $height;
        if ($area >= 16000000 || $width > 9999 || $height > 9999) {
            throw new InvalidArgumentException('Too big image dimensions!');
        }

        //Let's round the dimensions to 3 decimal places for aesthetics
        $width = round($width, 3);
        $height = round($height, 3);

        //I don't use this but if you wanted to angle your text you would change it here.
        $text_angle = 0;

        $font = __DIR__ . '/fonts/roboto.ttf';

        // Create an image
        $img = imageCreate($width, $height);
        $bg_color = imageColorAllocate($img, $background->get_rgb('r'), $background->get_rgb('g'), $background->get_rgb('b'));
        $fg_color = imageColorAllocate($img, $foreground->get_rgb('r'), $foreground->get_rgb('g'), $foreground->get_rgb('b'));

        if (!empty($text)) {
            $text = preg_replace_callback(
                "/(0x[0-9A-F]{,3})/ui",
                function ($matches) {
                    return chr(hexdec($matches[0]));
                },
                $text
            );
            $lines = substr_count($text, '|') + 1;
            $text = preg_replace('/\|/i', "\n", $text);
        } else {
            $lines = 1;
            // This is the default text string that will go right in the middle of the rectangle
            // &#215; is the multiplication sign, it is not an 'x'
            $text = $width . " &#215; " . $height;
        }

        // Michael Smith: Added line_spacing variable for caculating center height and fontsize on multiline text
        $line_spacing = $lines > 1 ? 1.3 : 0.75;
        // Ric Ewing: I modified this to behave better with long or narrow images and condensed the resize code to a single line
        $fontsize = max(min($width / strlen($text) * 1.15, $height * 0.5 / $lines / $line_spacing), 5);
        // Pass these variable to a function to calculate the position of the bounding box
        $textBox = $this->imagettfbbox_t($fontsize, $text_angle, $font, $text);
        // Calculate the width of the text box by subtracting the upper right "X" position with the lower left "X" position
        $textWidth = ceil(($textBox[4] - $textBox[0]) * 1.07);
        // Calculate the height of the text box by adding the absolute value of the upper left "Y" position with the lower left "Y" position
        $textHeight = ceil(abs($textBox[7]) + abs($textBox[1]) * 1);

        //Determine where to set the X position of the text box so it is centered
        $textX = ceil(($width - $textWidth) / 2);
        //Determine where to set the Y position of the text box so it is centered
        $textY = ceil(($height - $textHeight) / 2 + ($textHeight / ($lines * $line_spacing * 1.3)));

        //Create the rectangle with the specified background color
        imageFilledRectangle($img, 0, 0, $width, $height, $bg_color);
        //Create and positions the text
        imagettftext($img, $fontsize, $text_angle, $textX, $textY, $fg_color, $font, $text);


        // Start output buffering so we can determine the Content-Length of the file
        ob_start(['self', 'process_output_buffer']);
        // Create the final image based on the provided file format.
        switch ($this->format) {
            case 'gif':
                imagegif($img);
                break;
            case 'jpg':
            case 'jpeg':
                imagejpeg($img);
                break;
            default:
                $this->format = 'png';
                imagepng($img);
                break;
        }
        $this->output = ob_get_contents();
        ob_end_clean();

        return $this;
    }

    /**
     * Return image as base64 string
     *
     * @return string
     */
    public function toBase64()
    {
        if (empty($this->output)) {
            return $this->generate()->toBase64();
        }

        return 'data:image/' . $this->format . ';base64,' . base64_encode($this->output);
    }

    /**
     * Return image as Response
     *
     * @param int $code
     * @param array $headers
     * @return Response
     */
    public function toResponse(int $code = 200, array $headers = [])
    {
        if (empty($this->output)) {
            return $this->generate()->toResponse($code, $headers);
        }

        return Response::make($this->output, $code, array_replace_recursive([
            'Content-type' => 'image/' . $this->format,
            'Content-Length' => strlen($this->output)
        ], config('dummyimage.headers'), $headers));
    }

    /**
     * Put image to storate
     *
     * @param string $path
     * @param string $disk
     * @return string
     */
    public function put(string $path = null, string $disk = null): string
    {
        if (empty($this->output)) {
            return $this->generate(null, pathinfo($path, PATHINFO_EXTENSION))->put($path, $disk);
        }

        $path = $path ?? (config('dummyimage.path') . DIRECTORY_SEPARATOR . uniqid() . '.' . $this->format);
        $disk = $disk ?? config('dummyimage.disk') ?? 'local';
        $storage = Storage::disk($disk);
        $storage->put($path, $this->output);

        return $storage->path($path);
    }
}
