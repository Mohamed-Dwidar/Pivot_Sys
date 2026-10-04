<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\File;

trait UploaderHelper
{
    private $prefx_nu = 0;
    /**
     * upload file through $request, Compress it.
     * to the server in public folder: /public/images/{categoryNameFolder}.
     * if_not_exist : create it with 775 permission.
     *
     * @param Request $request
     * @return String
     */
    public function uploadImage($imageFromRequest, $imageFolder, $prefx = '', $resize_w = 0, $resize_h = 0, $thumb = false, $merge = false)
    {
        if (!file_exists(public_path('uploads/' . $imageFolder))) {
            mkdir(public_path('uploads/' . $imageFolder), 0777, true);
            if ($thumb == true) mkdir(public_path('uploads/' . $imageFolder . '/thumb'), 0777, true);
        }

        $prefx = ($prefx != '') ? $prefx . '_' : '';
        $dir_path = 'uploads/' . $imageFolder;
        // $extension = $imageFromRequest->getClientOriginalExtension();
        $extension = "png";

        //$fileName = $prefx . time() . '.' . $imageFromRequest->getClientOriginalExtension();
        $fileName = $this->makeFileName($prefx, $dir_path, $extension);

        $location = public_path($dir_path . '/' . $fileName);

        // generate merged image  
        if ($merge == true) {
            $image = $this->mergeImage($imageFromRequest);
        } else {
            $image = Image::make($imageFromRequest);
        }

        if ($resize_w > 0 && $resize_h > 0) {
            $image->resize($resize_w, $resize_h, function ($constraint) {
                $constraint->aspectRatio();
            });
        }
        // dd($image);
        $image->save($location, 100);

        # Optional Resize.
        if ($thumb == true) {
            $image->thumb(100, 100);
            $newlocation = public_path('uploads/' . $imageFolder . '/thumb' . '/' . $fileName);
            $image->save($newlocation, 100);
        }

        return $fileName;
    }

    public function uploadFile($fileFromRequest, $fileFolder, $prefx = '')
    {
        $prefx = ($prefx != '') ? $prefx . '_' : '';
        // $fileName = $prefx . time() . '.' . $fileFromRequest->getClientOriginalExtension();
        $dir_path = 'uploads/' . $fileFolder;
        // dd($prefx);
        $extension = $fileFromRequest->getClientOriginalExtension();
        // $extension='pdf';
        $fileName = $this->makeFileName($prefx, $dir_path, $extension);

        // $location = public_path($dir_path . '/'.$fileName);
        $location = public_path($dir_path);
        // dd($fileFromRequest);
        $fileFromRequest->move($location, $fileName);

        return $fileName;
    }

    /**
     * Call uploadImage() func to upload photo album.
     *
     * @param [type] $photos
     * @return void
     */
    public function uploadAlbum($photos, $folder)
    {
        foreach ($photos as $key => $album) {
            $imageName = $this->uploadImage($album, $folder);
            $photos[$key] = $imageName;
        }
        return $photos;
    }


    public function moveFile($oldPath, $newPath,$fileName)
    {
        if (!file_exists($newPath)) {
            mkdir($newPath, 0777, true);
        }
        // dd($oldPath);
        File::move($oldPath."/".$fileName, $newPath."/".$fileName);
    }
    /**
     * Move local image Compress it.
     * to the server in public folder: /public/images/{categoryNameFolder}.
     * if_not_exist : create it with 775 permission.
     *
     * @param Request $request
     * @return String
     */
    public function uploadLocalImage($sourceFile, $imageFolder, $prefx = '', $resize_w = 0, $resize_h = 0, $thumb = false, $merge = false)
    {
        if (!file_exists(public_path('uploads/' . $imageFolder))) {
            mkdir(public_path('uploads/' . $imageFolder), 0777, true);
            if ($thumb == true) mkdir(public_path('uploads/' . $imageFolder . '/thumb'), 0777, true);
        }

        $source_file_info = pathinfo($sourceFile);
        $source_file_full_path = $source_file_info['dirname'] . '/' . $source_file_info['basename'];

        $prefx = ($prefx != '') ? $prefx . '_' : '';
        $dir_path = 'uploads/' . $imageFolder;
        // $extension = $source_file_info['extension'];
        $extension = "png";

        $fileName = $this->makeFileName($prefx, $dir_path, $extension);

        $location = public_path($dir_path . '/' . $fileName);


        // generate merged image here 

        if ($merge == true) {
            $image = $this->mergeImage($source_file_full_path);
        } else {
            $image = Image::make($source_file_full_path);
        }

        if ($resize_w > 0 && $resize_h > 0) {
            $image->resize($resize_w, $resize_h, function ($constraint) {
                $constraint->aspectRatio();
            });
        }
        $image->save($location, 100);

        # Optional Resize.
        if ($thumb == true) {
            $image->thumb(100, 100);
            $newlocation = public_path('uploads/' . $imageFolder . '/thumb' . '/' . $fileName);
            $image->save($newlocation, 100);
        }

        return $fileName;
    }

    function makeFileName($prefx, $dir_path, $extension)
    {
        $filename = $prefx . time() . "." . $extension;

        $actual_name = $prefx . time();
        $original_name = $actual_name;

        $i = 1;
        while (file_exists(public_path($dir_path . '/') . $actual_name . "." . $extension)) {

            $actual_name = (string)$original_name . '_' . $i;
            $filename = $actual_name . "." . $extension;

            $i++;
        }
        return $filename;
    }
    
    public function mergeImage($uploadImage)
    {
        $image = Image::canvas(600, 600);
        // $image->insert(Image::make(public_path('admin_assets/assets/images/logo.png'))->resize(600, 600));
        $image->insert(Image::make($uploadImage)->resize(500, 500), 'center'); // add offset
        // dd($image);
        return $image;
    }

    public function move_uploaded_file($file, $destination)
    {
        if (file_exists($file)) {
            if (!file_exists($destination)) {
                mkdir($destination, 0777, true);
            }
            $file_name = basename($file);
            $destination = $destination . '/' . $file_name;
            File::move($file, $destination);
            return $destination;
        }
        return false;
    }

    function copy_all_files_to_new_dir($source_path, $destination_path)
    {
        /* check if the destination directory is not empty */
        if (File::isDirectory($destination_path)) {
            File::deleteDirectory($destination_path);
        }

        /* check if the destination directory is not exist */
        if (!file_exists($destination_path)) {
            mkdir($destination_path, 0777, true); // create the directory if it does not exist
        }

        /* copy all files from source directory to destination directory */
        File::copyDirectory($source_path, $destination_path);
    }
}
