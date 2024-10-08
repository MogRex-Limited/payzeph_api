<?php

namespace App\Services\Media;

use Exception;

class ImageService
{
    public static function compress(string $file_path, $width = null, $height = null)
    {
        return \Image::make($file_path)->resize($width, $height)->save($file_path, 60);
    }

    public static function resizeWithCanvas(string $file_path, $width = null, $height = null)
    {
        $canvas = \Image::canvas($width, $height);
        $image = \Image::make($file_path)->resize($width, $height, function ($constraint) {
            $constraint->aspectRatio();
        });

        $canvas->insert($image, 'center');
        $canvas->save($file_path, 60);
    }

    public static function watermark(string $file_path, $watermark_path = null)
    {
        $img = \Image::make($file_path);
        $watermark = \Image::make($watermark_path ?? public_path("watermark.png"));
        $img->insert($watermark, 'bottom-left', 20, 20);
        $img->save($file_path);
    }

    public static function pngToBase64($file_path)
    {
        try {
            $type = pathinfo($file_path, PATHINFO_EXTENSION);
            $data = file_get_contents($file_path);
            return 'data:image/' . $type . ';base64,' . base64_encode($data);
        } catch (Exception $e) {
        }
    }

    public function convertBase64Image($base64_image)
    {
        try {
            $base64_image = str_replace('data:image/png;base64,', '', $base64_image);
            return $base64_image;
        } catch (\Exception $th) {
            //throw $th;
        }
    }
}
