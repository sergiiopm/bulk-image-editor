<?php

namespace App\Http\Controllers;

use ZipArchive;
use Illuminate\Http\Request;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class ImageController extends Controller
{
    public function index()
    {
        return view('index');
    }

    public function store(Request $request)
    {
        // Validamos campos
        $validatedData = $request->validate([
            "images.*" => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            "track-keyword" => 'min: 2|max: 20',
            "new-keyword" => 'min: 0|max: 20'
        ]);

        $oldKeyword = $request->input('track-keyword');
        $newKeyword = $request->input('new-keyword');
        
        // Creamos una carpeta temporal, en esta almacenamos las imágenes
        $tempFolder = sys_get_temp_dir() . '/' . uniqid('bulk_images_', true);
        mkdir($tempFolder);
        
        // Modificamos el nombre de las imágenes
        foreach ($request->file('images') as $image) {
            $originalName = $image->getClientOriginalName();
            $newName = str_replace($oldKeyword, $newKeyword, $originalName);
            $image->move($tempFolder, $newName);
        }
        
        // Creamos un ZIP
        $zipName = uniqid('bulk_images_', true) . '.zip';
        $zipPath = sys_get_temp_dir() . '/' . $zipName;
        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($tempFolder));
        foreach ($files as $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($tempFolder) + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }
        $zip->close();
        
        // Devolvemos el ZIP como descarga
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $zipName . '"');
        header('Content-Length: ' . filesize($zipPath));
        readfile($zipPath);
        
        // Borramos la carpeta temporal y el zip
        unlink($zipPath);
        $this->deleteDirectory($tempFolder);


        return redirect()->back();
    }

    // Función auxiliar para eliminar la carpeta y todo el contenido
    private function deleteDirectory($dir) {
        if (!file_exists($dir)) {
            return true;
        }
        if (!is_dir($dir)) {
            return unlink($dir);
        }
        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }
            if (!$this->deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }
        return rmdir($dir);
    }
}