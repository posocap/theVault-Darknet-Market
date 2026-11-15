<?php

namespace App\Marketplace\Utility;

use Gregwar\Captcha\CaptchaBuilder;


/**
 * Creating and Verifying Captcha
 */
class Captcha
{

    public static function Build()
    {

        $width = 200;
        $height = 50;
        $char_number = 6;
        $builder =  new CaptchaBuilder($char_number);
        $builder->build($width,$height);
        session()->put('captcha',$builder->getPhrase());
        return $builder->inline();
    }
    public static function Verify($input)
    {
        if (!session()->has('captcha')) {
            return false;
        }
        // One-time use: consume the phrase whether or not the guess is right
        $phrase = session()->pull('captcha');
        return is_string($input) && hash_equals((string) $phrase, $input);
    }
}