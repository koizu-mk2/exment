<?php

namespace Exceedone\Exment\Model;

use Exceedone\Exment\Services\Uuids;
use Illuminate\Support\Facades\Storage;
use Webpatser\Uuid\Uuid;
use Response;
class File extends ModelBase
{
    use Uuids;
    protected $guarded = ['uuid'];
    // Primary key setting
    protected $primaryKey = 'uuid';
    // increment disable
    public $incrementing = false;

    public function getFileNameAttribute(){
        // get pathinfo
        $pathinfo = pathinfo($this->path) ?? null;
        if(is_null($pathinfo)){return null;}
        return array_get($pathinfo, 'basename');
    }

    /**
     * get the file url
     * @return void
     */
    public static function getUrl($path){
        $file = static::getData($path);
        if (is_null($file)) {
            return null;
        }

        return $file->uuid;
    }

    /**
     * Save file info to database and get file path
     * @param string $fileName
     * @return File saved file path
     */
    public static function saveFileInfo(string $path)
    {
        $file = static::firstOrNew(['path' => $path]);
        $file->uuid = Uuid::generate()->string;
        $file->save();
        return $path;
    }

    /**
     * delete file info to database
     * @param string $fileName
     * @return void
     */
    public static function deleteFileInfo(string $path)
    {
        $file = static::getData($path);
        if(is_null($file)){return;}
        $file->delete();        
    }

    /**
     * Download file
     */
    public static function download($uuid, Closure $authCallback = null){
        $data = static::getData($uuid);
        if(!$data){
            abort(404);
        }
        $path = $data->path;
        $exists = Storage::disk(config('admin.upload.disk'))->exists($path);
        
        if(!$exists){
            abort(404);
        }
        if($authCallback){
            $authCallback($data);
        }

        $file = Storage::disk(config('admin.upload.disk'))->get($path);
        $type = Storage::disk(config('admin.upload.disk'))->mimeType($path);
        // get page name
        $name = rawurlencode($data->filename);

        // create response
        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);
        $response->header('Content-Disposition', "inline; filename*=UTF-8''$name;filename={$data->filename}");

        return $response;
    }

    /**
     * get file
     */
    public static function getFile($uuid, Closure $authCallback = null){
        $data = static::getData($uuid);
        if(!$data){
            return null;
        }
        $path = $data->path;
        $exists = Storage::disk(config('admin.upload.disk'))->exists($path);
        
        if(!$exists){
            return null;
        }
        if($authCallback){
            $authCallback($data);
        }

        return Storage::disk(config('admin.upload.disk'))->get($path);        
    }

    /**
     * Save file table on db and store the uploaded file on a filesystem disk.
     *
     * @param  string  $disk disk name
     * @param  string  $path directory path
     * @param  array|string  $options
     * @return string|false
     */
    public static function put($disk, $path, $content, $options = []){
        Storage::disk($disk)->put($path, $content, $options);
        $path = static::saveFileInfo($path);
        return $path;
    }
    
    /**
     * Save file table on db and store the uploaded file on a filesystem disk.
     *
     * @param  string  $disk disk name
     * @param  string  $path directory path
     * @param  array|string  $options
     * @return string|false
     */
    public static function putAs($disk, $path, $content, $options = []){
        Storage::disk($disk)->put($path, $content, $options);
        $path = static::saveFileInfo($path);
        return $path;
    }

    /**
     * Save file table on db and store the uploaded file on a filesystem disk.
     *
     * @param  string  $disk disk name
     * @param  string  $path directory path
     * @param  array|string  $options
     * @return string|false
     */
    public static function store($content, $disk, $path, $options = []){
        $path = $content->store($path, $disk, $options);
        $path = static::saveFileInfo($path);
        return $path;
    }
    
    /**
     * Save file table on db and store the uploaded file on a filesystem disk.
     *
     * @param  string  $disk disk name
     * @param  string  $path directory path
     * @param  array|string  $options
     * @return string|false
     */
    public static function storeAs($content, $disk, $path, $name, $options = []){
        $path = $content->storeAs($path, $disk, $name, $options);
        $path = static::saveFileInfo($path);
        return $path;
    }

    public static function getData($pathOrUuid){
        $file = static::where(function($query) use($pathOrUuid){
            $query->orWhere('path', $pathOrUuid);
            $query->orWhere('uuid', $pathOrUuid);
        })->first();
        if (is_null($file)) {
            return null;
        }
        return $file;
    }
}
