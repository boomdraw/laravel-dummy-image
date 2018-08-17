# Generate dummy images

This package allows you to generate dynamic dummy image in Laravel framework.

It's based on the [Russell Heimlich's dummy image generator](https://github.com/kingkool68/dummyimage)

Once installed you can do stuff like this:

```php
// Generate and store an image
DummyImage::put($path, $disk);

// Generate and return image as response
DummyImage::toResponse($code, $headers);

// Generate and return image as base64
DummyImage::toBase64($code, $headers);
```

## Installation
### Laravel

This package can be used in Laravel 5.4 or higher.

You can install the package via composer:

``` bash
composer require boomdraw/laravel-dummy-image
```

In Laravel 5.5 the service provider will automatically get registered. In older versions of the framework just add the service provider in `config/app.php` file:

```php
'providers' => [
    // ...
    BoomDraw\DummyImage\DummyImageServiceProvider::class,
];
```

And add a class aliases to the aliases array of config/app.php:
```php
'aliases' => [
    // ...
    'DummyImage' => BoomDraw\DummyImage\Facades\DummyImage::class,
];
```

You can publish the config file with:

```bash
php artisan vendor:publish --provider="BoomDraw\DummyImage\DummyImageServiceProvider" --tag="config"
```

When published, [the `config/dummyimage.php` config file](https://github.com/boomdraw/laravel-dummy-image/blob/master/config/dummyimage.php) contains:

```php
return [

    /*
     * Default disk for generated image
     */
    'disk' => 'local',

    /*
     * Default path for generated image
     */
    'path' => 'dummyimage',

    /*
     * Additional headers for response
     */
    'headers' => [
        //
    ],

    /*
     * Additional html color names with hex value for name to hex convertation
     */
    'color_names' => [
        //'color_name' => '00ffff'
    ],
];
```

## Usage

```php
// Generate an image with custom params
$image = DummyImage::generate($dimensions = '200x1:5', $format = 'gif', $bg_color = 'ff00cc', $fg_color = '00ffcc', $text = 'I am image text');

// Generate and store an image
$image->put($path, $disk);

// Generate and return image as response
$image->toResponse($code, $headers);

// Generate and return image as base64 string
$image->toBase64($code, $headers);
```

If any of the methods will be used without generate() one, an image will be generated with default params.

You can provide your own parameters for generating image:
- $dimensions - image dimensions.<br/>
You can provide as dimensions image size and/or ratio. By default used '800x600'.<br/>
Examples:<br/>
'800x600' will generate an image with width 800px and height 600px<br/>
'800' will generate an image with 800px width and height<br/>
'250x1:2' will generate an image with 250px width and 500px height<br/>
'1:3x300' will generate an image with width 100px and height 300px
- $format - generated image format ('jpg', 'gif', 'png'). Default format is 'png'.
- $bg-color - image background color in hex. Default is white ('ffffff').
- $fg_color - image text color in hex. Default is black ('000000').
- $text variable provides text that will be written on the image. Default is image dimensions.

## Resources

- [Online dynamic dummy image generator](https://dummyimage.com/)

## License

The MIT License (MIT).

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
