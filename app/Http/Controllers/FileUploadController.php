<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FileUpload;
use File,DB;

class FileUploadController extends Controller
{
    public function upload($req,$field,$dir) {
        try{
            $fileModel = new FileUpload;
            if($req->file()) {
                $fileName = time().'_'.$req->file($field)->getClientOriginalName();
                $filePath = $req->file($field)->storeAs($dir, $fileName, 'public');
                $name = $req->file($field)->getClientOriginalName();
                $imageSize = $req->file($field)->getSize();
                $file_path = '/storage/' . $filePath;
                $fileModel->hash_id = getHashid();
                $fileModel->file_name = $name;
                $fileModel->file_path = $file_path;
                $fileModel->file_size_mb = $imageSize;
                $fileModel->save();
                return $fileModel->id;
            }else{
                return '';
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function removeFromFolder($req,$folder_name) {
        try{
            $folder_path =  $req->file_path;
            if ($folder_path) {
                \Storage::disk('public')->delete($folder_name.'/' . $req->file_name);
                DB::table('files')->where('id',$req->id)->delete(); 
            }
            return 1;        
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }
}
