<?php

namespace Exceedone\Exment\Controllers;

use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;
use Exceedone\Exment\Model\Define;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Pagination\LengthAwarePaginator;

class SystemLogController extends AdminControllerBase
{
    use CodeTreeTrait;
    
    protected const node_key = Define::SYSTEM_KEY_SESSION_FILE_NODELIST;

    protected $disk;

    /**
     * constructer
     *
     */
    public function __construct()
    {
        $this->setPageInfo(exmtrans("system_log.header"), exmtrans("system_log.header"), exmtrans("system_log.description"), 'fa-file-text-o');
    }

    /**
     * Execute an action on the controller.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function callAction($method, $parameters)
    {
        $this->disk = \Storage::disk(Define::DISKNAME_LOG);
        return parent::callAction($method, $parameters);
    }

    /**
     * Showing code edit page
     *
     * @param Request $request
     * @param Content $content
     * @return Content
     */
    public function index(Request $request, Content $content)
    {
        $this->AdminContent($content);
        session()->forget(static::node_key);

        $content->row(function (Row $row) use($request){
            if($this->disk->exists('laravel.log')){
                // get nodeid
                $json = $this->getTreeDataJson($request);
                $node = collect($json)->first(function($j){
                    return isMatchString(array_get($j, 'path'), '/laravel.log');
                });

                $view = $this->getLogDataForm($request, array_get($node, 'id'));
                $box = new Box('', $view);
                $view = $box->style('info');
            }
            else{
                $view = view('exment::system_log.index', [
                    'url' => admin_url("system_log"),
                    'filepath' => '/',
                ]);
            }
            
            $html = $view->render();
            $html .= view('exment::system_log.script')->render();

            $row->column(9, $html);

            $row->column(3, $this->getJsTreeBox());
        });

        return $content;
    }

    protected function getJsTreeBox()
    {
        $view = view('exment::widgets.jstree', [
            'data_get_url' => "system_log/getTree",
            'file_get_url' => "system_log/selectFile",
        ]);
        $box = new Box('', $view);

        return $box->render();
    }

    /**
     * Get file tree data
     *
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function getTreeData(Request $request)
    {
        return response()->json($this->getTreeDataJson($request));
    }

    
    /**
     * Get file tree data
     *
     * @param Request $request
     * @param int $id
     * @return Response
     */
    protected function getTreeDataJson(Request $request)
    {
        if(session()->has(static::node_key)){
            return session(static::node_key);
        }

        $json = [];
        $this->setDirectoryNodes('/', '#', $json, true);
        
        // set session
        session([static::node_key => $json]);
        return $json;
    }


    /**
     * Get child form html for selected file
     *
     * @param Request $request
     * @param int $id
     * @return array
     */
    public function getFileEditForm(Request $request)
    {
        list($view, $isBox) = $this->getFileFormView($request);

        if ($isBox) {
            $box = new Box('', $view);
            $view = $box->style('info');
        }
        return [
            'editor' => $view->render()
        ];
    }

    
    
    /**
     * Get child form html for selected file
     *
     * @param Request $request
     * @param int $id
     * @return array
     */
    protected function getFileFormView(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'nodeid' => 'required',
        ]);
        if ($validator->fails()) {
            throw new \Exception;
        }
        $nodeid = $request->get('nodeid');
        $nodepath = $this->getNodePath($nodeid);

        try {
            $targetPath = getFullpath($nodepath, $this->disk);
            if (is_dir($targetPath)) {
                return [view('exment::system_log.index', [
                    'url' => admin_url("system_log"),
                    'filepath' => $nodepath,
                ]), false];
            }
            return [$this->getLogDataForm($request, $nodeid), true];
            
        } catch (\League\Flysystem\FileNotFoundException $ex) {
            //Todo:FileNotFoundException
        }
    }

    
    /**
     * getLogDataForm
     *
     * @param Request $request
     * @return array
     */
    protected function getLogDataForm(Request $request, $nodeid)
    {
        try {
            $nodepath = $this->getNodePath($nodeid);
            $filedata = $this->getLogData($nodepath);

            $page = $request->get('page') ?? 1;
            $length = config('exment.system_log_length', 2000);
            $texts = array_slice($filedata, ($page - 1) * $length, $length);

            $paginator = new LengthAwarePaginator($texts, count($filedata), $length, $page, ['path' => admin_urls_query('system_log', 'selectFile', ['nodeid' => $nodeid])]);

            return view('exment::system_log.log', [
                'filepath' => $nodepath,
                'filedata' => implode('', $texts),
                'paginator' => $paginator,
            ]);
            
        } catch (\League\Flysystem\FileNotFoundException $ex) {
            //Todo:FileNotFoundException
        }
    }

    
    /**
     * Get log data
     *
     * @param string $path file relative path
     * @return void
     */
    public function getLogData(string $path)
    {
        $sotrage_path = getFullpath($path, $this->disk);
        return file($sotrage_path);
    }


    protected function getDirectoryPaths($folder){
        return $this->disk->directories($folder);
    }


    protected function getFilePaths($folder){
        return array_filter($this->disk->files($folder), function($file){
            return isMatchString(pathinfo($file, PATHINFO_EXTENSION), 'log');
        });
    }
}
