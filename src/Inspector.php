<?php

namespace Lsrur\Inspector;

use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\Response;

class inspector
{

    private $is_on = true;
    private $response;
    private $request;
    private $injectorType;
    private $collectorMan;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->collectorMan = new CollectorManager($this);
    }
    
    /**
     * Analize 
     * @param  [type] $request  [description]
     * @param  [type] $response [description]
     * @return [type]           [description]
     */
    public function analize($request, $response)
    {
        // Needed by ResponseCollector and RequestCollctor
        $this->response = $response;
        $this->request = $request;

        // Make dd with the "analizeResponse" flag
        $this->dd(206,true);
    }

    /**
     * Show inspector full screen page and die
     * @return [type] [description]
     */
    public function dd($status = 206, $analizeResponse=false) // partial content?
    {
        // Try to take these values as soon as posible
        $time = microtime(true);
        $memoryUsage = formatMemSize(memory_get_usage());

        // CLI response
        if(\App::runningInConsole())
        {
            $result = $this->collectorMan->getRaw();
            dump($result);
            return;
        }

        // Json respnse 
        if (request()->wantsJson()) {
            $title = $status == 206 ? 'DD' : "UNCAUGHT EXCEPTION";
            header("status: $status", true);
            header("Content-Type: application/json", true);
            $collectorData = request()->headers->has('laravel-inspector') ?
                $this->collectorMan->getScripts('inspector', $title, $status) :
                $this->collectorMan->getPreJson('inspector');
            if($analizeResponse)
            {
                // Respond the payload also
                $collectorData = array_merge(json_decode($this->response->getContent(),true), ['LARAVEL_INSPECTOR'=>$collectorData]);
            } else {
                $collectorData = ['LARAVEL_INSPECTOR'=>$collectorData];
            }
            echo json_encode($collectorData);
            die();
        } else {
            // Fullscreen dd
            // Get collectors bag ready for fullscreen view
            $collectorData = $this->collectorMan->getFs();
            try {
                $view = (string)view('inspector::fullscreen', 
                [
                    'analizeView'  => $analizeResponse,
                    'collectors'   => $collectorData,
                    'memoryUsage'  => $memoryUsage,
                    'time'         => round(($time-LARAVEL_START)*1000,2)
                ]);
                echo $view;
                die();
            } catch (\Exception $e) {
                dump($e);
                die();
            }
        }
    }

    /**
     * Turn Inspector Off
     * @return [type] [description]
     */
    public function turnOff()
    {
        $this->is_on = false;
    }

    /**
     * IsOn
     * @return boolean
     */
    public function isOn()
    {

        return config('app.debug') && $this->is_on && env('APP_ENV') != 'testing';
    }

    /**
     * Magic methods
     * 
     * @param  [type] $method [description]
     * @param  [type] $args   [description]
     * @return [type]         [description]
     */
    public function __call($method, $args)
    {
        if(! $this->isOn()) return;
        // if the called is a MessageCollector method, redirect to MessageCollector->add  
        if (in_array($method, ['table', 'info', 'warning', 'error', 'log', 'success'])) {
            $collector = $this->collectorMan->get('MessageCollector');
            array_unshift($args, $method);
            $method = 'add';
        } elseif ($collector = $this->collectorMan->getMethod($method)) {
            $method = 'b_'.$method;
        } else {
            die("Method $method not found in collector classes");
        }

        return call_user_func_array(array(&$collector, $method), $args);
    }

    /**
     * Determine the injector type
     *
     * @param  [type] $request  [description]
     * @param  [type] $response [description]
     * @return [type]           [description]
     */
    protected function getInjectorType($request, $response)
    {
        if ($request->wantsJson() || get_class($response)=="Illuminate\Http\JsonResponse") {
            $this->injectorType = 'json';
        } elseif ($response->isRedirect()) {
            $this->injectorType = 'redirect';
        } elseif (get_class($response)=="Illuminate\Http\Response" && is_object($response->getOriginalContent()) && get_class($response->getOriginalContent()) == 'Illuminate\View\View') {
            $this->injectorType = 'view';
        }
        return isset($this->injectorType);
    }

    /**
     * GetResponse
     * @return [type] [description]
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * GetRequest
     * @return [type] [description]
     */
    public function getRequest()
    {
        return $this->request;
    }
    /**
     * Inject Inspector into Response
     * @param  [type] $request  [description]
     * @param  [type] $response [description]
     * @return [type]           [description]
     */
    public function injectInspection($request, $response)
    {
        $this->response = $response;

        if (!$this->getInjectorType($request, $response)) 
            return;
        
        switch ($this->injectorType) {
            case 'redirect':
                // Put the collectors bag into a session flash
                $collectorsData = $this->collectorMan->getScripts('inspector',  'REDIRECT:'.$request->url().' -> '.$response->getTargetUrl(), $response->getStatusCode());
                $request->session()->flash('LARAVEL_INSPECTOR_REDIRECT', $collectorsData);
                break;
            case 'view':
                $collectorsData = $this->collectorMan->getScripts('inspector', 'VIEW:'.$response->getOriginalContent()->getName(), $response->getStatusCode());
                // if there is a flashed inspection (redirect), append it to this response
                $redirectData = null;
                if(isset($request->session ) && $request->session()->has('LARAVEL_INSPECTOR_REDIRECT')) {
                    $redirectData = $request->session()->get('LARAVEL_INSPECTOR_REDIRECT');
                    session()->forget('LARAVEL_INSPECTOR_REDIRECT');
                }

                $inspectionBag = (string)view('inspector::view_console', [
                    'redirectData' => $redirectData,
                    'collectorsData' => $collectorsData,
                    ]);

                $content = $response->getContent();
                // Ensure string content
                if (is_string($content)) {
                    $content = str_replace('</body>', $inspectionBag.'</body>', $response->getContent());
                    $response->setContent($content);
                }
                break;

            case 'json':
                header('Content-Type: application/json', true);
                $content = json_decode($this->response->getContent(), true) ?: [];
                if ($request->headers->has('laravel-inspector')) {
                    $content['LARAVEL_INSPECTOR'] = $this->collectorMan->getScripts('inspector', $request->method().':'.$request->url(), $response->getStatusCode());
                } else {
                    $content['LARAVEL_INSPECTOR'] = $this->collectorMan->getPreJson('inspector');
                }
                $response->setContent(json_encode($content));
                break;
        }
    }

    public function getDump($v)
    {
        $styles = [
            'default' => 'background-color:white; color:#222; line-height:1.2em; font-weight:normal; font:13px Monaco, Consolas, monospace; word-wrap: break-word; white-space: pre-wrap; position:relative; z-index:1000; border:0px;',
            'num' => 'color:#a71d5d',
            'const' => 'color:#795da3',
            'str' => 'color:#df5000',
            'cchr' => 'color:#222',
            'note' => 'color:#a71d5d',
            'ref' => 'color:#a0a0a0',
            'public' => 'color:#795da3',
            'protected' => 'color:#795da3',
            'private' => 'color:#795da3',
            'meta' => 'color:#b729d9',
            'key' => 'color:#df5000',
            'index' => 'color:#a71d5d',
        ];
        ob_start();

        $dumper = new \Symfony\Component\VarDumper\Dumper\HtmlDumper;
        $dumper->setStyles($styles);

        $dumper->dump((new \Symfony\Component\VarDumper\Cloner\VarCloner)->cloneVar($v));

        //$dumper->dump($v);
        $result = ob_get_clean();

        return $result;
    }

    public function getIlluminateAncestor($obj)
    {
        $result = '';
        $ancestors = get_ancestors(get_class($obj));
        foreach ($ancestors as $value) {
            if(starts_with($value, 'Illuminate')) {
                $result = $value;   
                break;
            }
        }
        return $result;

    }

}
