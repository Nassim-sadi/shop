<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class ImageUpload
{
  public static function uploadImage($image, $directory)
  {

    debugbar()->log("image in upload fn : " . $image);
    $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
    $extension = $image->extension();
    $imageName = Str::slug($originalName) . '-' . time() . '.' . $extension;
    $destinationPath = public_path("/storage/images/$directory");
    if (!is_dir($destinationPath)) {
      mkdir($destinationPath, 0755, true);
    }
    $image->move($destinationPath, $imageName);
    return $imageName;
  }
}
