<?php

namespace App\Http\Controllers;

use ZipArchive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class ImageController extends Controller
{
    public function index()
    {
        return view('indexarray');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            "images.*" => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            "track-keyword" => 'min:2|max:20',
        ]);

        $imagesArray = $request->file('images');
        $trackKeyword = $request->input('track-keyword');
        $keywordsArray = explode("\r\n", trim($request->input('new-keyword')));

        if(count($keywordsArray) <= 15){
            // Creamos una carpeta temporal
            $tempFolder = sys_get_temp_dir() . '/' . uniqid('bulk_images_', true);
            mkdir($tempFolder);

            // Creamos un ZIP
            $zipName = uniqid('bulk_images_', true) . '.zip';
            $zipFile = $tempFolder . '/' . $zipName;
            $zip = new ZipArchive();
            $zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);

            foreach($keywordsArray as $keyword){

                // Crea los directorios
                $keywordDir = $tempFolder . '/' . $keyword;
                mkdir($keywordDir, 0777, true);

                $zip->addEmptyDir($keyword);

                foreach ($imagesArray as $image) {
                    $originalName = $image->getClientOriginalName();
                    $newName = str_replace($trackKeyword, $keyword, $originalName);
                    $image->storeAs('public/' . $keyword, $newName);
                    $zip->addFile(storage_path('app/public/' . $keyword . '/' . $newName), $keyword . '/' . $newName);
                }
            }

            // Cierra el ZIP
            $zip->close();

            // Devuelve el archivo ZIP al usuario
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . $zipName . '"');
            header('Content-Length: ' . filesize($zipFile));

            readfile($zipFile);

            // Elimina la carpeta temporal y su contenido
            $this->deleteDir($tempFolder);

            //Eliminamos las carpetas
            foreach($keywordsArray as $keyword){
                Storage::deleteDirectory('public/' . $keyword);
            }
        }else{
            return redirect()->back();
        }

    }

    private function deleteDir($dirPath) 
    {
        if (!is_dir($dirPath)) {
            throw new InvalidArgumentException("$dirPath must be a directory");
        }
        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
            $dirPath .= '/';
        }
        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                $this->deleteDir($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dirPath);
    }
}

