<?php

namespace Exceedone\Exment\Controllers;

use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Pagination\LengthAwarePaginator;

class ServerLogController extends AdminControllerBase
{
    /**
     * constructer
     *
     */
    public function __construct()
    {
        $this->setPageInfo(exmtrans("plugincode.header"), exmtrans("plugincode.header"), exmtrans("plugincode.description"), 'fa-plug');
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
        if(config('logging.default', 'stack') != 'stack'){
            admin_warning(trans('admin.alert'), 'ログの出力先が変更されています。その場合、この画面では正常にログを確認できない場合があります。');
        }

        $this->AdminContent($content);
        $content->row(function (Row $row) use($request){
            if(\File::exists($this->getLogFullPath('laravel.log'))){
                $view = $this->getLogDataForm($request, '/laravel.log');
                $box = new Box('', $view);
                $view = $box->style('info');
            }
            else{
                $view = view('exment::log.index', [
                    'url' => admin_url("server_log"),
                    'filepath' => '/',
                    'message' => exmtrans('plugincode.message.upload_file'),
                ]);
            }
            
            $html = $view->render();
            $html .= view('exment::log.script')->render();

            $row->column(9, $html);

            $row->column(3, $this->getJsTreeBox());
        });

        return $content;
    }

    protected function getJsTreeBox()
    {
        $view = view('exment::widgets.jstree', [
            'data_get_url' => "server_log/getTree",
            'file_get_url' => "server_log/selectFile",
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
        $json = [];
        $node_idx = 0;
        $this->setDirectoryNodes('/', '#', $node_idx, $json);
        return response()->json($json);
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
            'nodepath' => 'required',
        ]);
        if ($validator->fails()) {
            return [view('exment::plugin.editor.info'), false];
        }

        $nodepath = str_replace('//', '/', $request->get('nodepath'));
        try {
            if (is_dir($this->getLogFullPath($nodepath))) {
                return [view('exment::log.index', [
                    'url' => admin_url("server_log"),
                    'filepath' => $nodepath,
                    'message' => exmtrans('plugincode.message.upload_file'),
                ]), false];
            }
            return [$this->getLogDataForm($request, $nodepath), true];
            
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
    protected function getLogDataForm(Request $request, $nodepath)
    {
        try {
            $filedata = $this->getLogData($nodepath);
            $page = $request->get('page') ?? 1;
            $texts = array_slice($filedata, ($page - 1) * 1000, 1000);

            $paginator = new LengthAwarePaginator($texts, count($filedata), 1000, $page, ['path' => admin_urls_query('server_log', 'selectFile', ['nodepath' => $nodepath])]);

            return view('exment::log.log', [
                'filepath' => $nodepath,
                'filedata' => implode('', $texts),
                'paginator' => $paginator,
            ]);
            
        } catch (\League\Flysystem\FileNotFoundException $ex) {
            //Todo:FileNotFoundException
        }
    }

    
    /**
     * Get and set file and directory nodes in target folder
     *
     * @param string $folder
     * @param string $parent
     * @param int &$node_idx
     * @param array &$json
     * @param bool $isFullPath if true, this path is full path
     * @param string $folderName root folder name.
     */
    protected function setDirectoryNodes($folder, $parent, &$node_idx, &$json, bool $isFullPath = false)
    {
        $node_idx++;
        $directory_node = "node_$node_idx";
        $json[] = [
            'id' => $directory_node,
            'parent' => $parent,
            'text' => isMatchString($folder, '/') ? '/' : basename($folder),
            'state' => [
                'opened' => $parent == '#',
                'selected' => $node_idx == 1
            ]
        ];

        $base_path = $isFullPath ? $folder : $this->getLogFullPath($folder);
        $directories = \File::directories($base_path);
        foreach ($directories as $directory) {
            $this->setDirectoryNodes($directory, $directory_node, $node_idx, $json, true);
        }

        $files = \File::files($base_path);
        foreach ($files as $file) {
            if(!isMatchString($file->getExtension(), 'log')){
                continue;
            }

            $node_idx++;
            $json[] = [
                'id' => "node_$node_idx",
                'parent' => $directory_node,
                'icon' => 'jstree-file',
                'text' => basename($file),
            ];
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
        $sotrage_path = $this->getLogFullPath($path);
        return file($sotrage_path);
    }


    protected function getLogFullPath($path)
    {
        return storage_path(path_join('logs', $path));
    }
}
