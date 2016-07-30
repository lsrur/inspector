<?php

namespace Lsrur\Inspector;

use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\Response;

class inspector
{
    private $collector;

    private $is_on = true;
    private $condition;
    public $response;
    private $injectorType;
    private $collectorMan;

    public function __construct()
    {
        $this->collectorMan = new CollectorManager($this);
    }

    /**
     * Show inspector full screen page and die
     * @return [type] [description]
     */
    public function dd($status = 206) // partial content?
    {
        $time = microtime(true);
        $memoryUsage = formatMemSize(memory_get_usage());
        if(\App::runningInConsole())
        {
            $result = $this->collectorMan->getRaw();
            dump($result);
            return;
        }

        if (request()->wantsJson()) {
            $title = $status == 206 ? 'DD' : "UNCAUGHT EXCEPTION";
            header("status: $status", true);
            $collectorData = request()->headers->ps('laravel-inspector') ?
                $this->collectorMan->getScripts('inspector', $title, $status) :
                $this->collectorMan->getPreJson('inspector');

            header("Content-Type: application/json", true);
            echo json_encode(['LARAVEL_INSPECTOR'=>$collectorData]);
            die();
        } else {
            $collectorData = $this->collectorMan->getFs();
            try {
                $view = (string)view('inspector::fullscreen', 
                [
                    'collectors'   => $collectorData,
                    'memoryUsage'  => $memoryUsage,
                    'time'         => round(($time-LARAVEL_START)*1000,2)
                ]);
            } catch (Exception $e) {
                dump($e);
                die();
            }
            echo $view;
            die();
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
     * InOn
     * @return boolean
     */
    public function isOn()
    {
        return config('app.debug') && $this->is_on;
    }

    /**
     * Conditional logging
     * @param  bool   $condition [description]
     * @return [type]            [description]
     */
    public function if (bool $condition) {
     $this->condition = $condition===false ? $condition : null;

     return $this;
 }

    /**
     * Magic methods
     * @param  [type] $method [description]
     * @param  [type] $args   [description]
     * @return [type]         [description]
     */
    public function __call($method, $args)
    {
        if ($this->condition === false) {
            $this->condition = null;
            return;
        }

        $this->condition = null;

        if (in_array($method, ['table', 'info', 'warning', 'error', 'log', 'success'])) {
            $collector = $this->collectorMan->get('MessageCollector');
            array_unshift($args, $method);
            $method = 'add';
        } elseif ($collector = $this->collectorMan->getMethod($method)) {
            $method = 'b_'.$method;
        } else {
            trigger_error("Method $method not found in collector classes", E_USER_ERROR);
        }

        return call_user_func_array(array(&$collector, $method), $args);

//    	$this->onMethodExecuted(substr(strrchr(get_class($collector), "\\"), 1), $method);
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

    public function getResponse()
    {
        return $this->response;
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

        if (!$this->getInjectorType($request, $response)) {
            return;
        }

        switch ($this->injectorType) {
            case 'redirect':
                $collectorsData = $this->collectorMan->getScripts('inspector',  'REDIRECT:'.$request->url().' -> '.$response->getTargetUrl(), $response->getStatusCode());
                $request->session()->flash('LARAVEL_INSPECTOR_REDIRECT', $collectorsData);
                break;
            case 'view':
                $collectorsData = $this->collectorMan->getScripts('inspector', 'VIEW:'.$response->getOriginalContent()->getName(), $response->getStatusCode());
                // if there is a flashed inspection (redirect), append it to this response
                $redirectData = null;
                if ($request->session()->has('LARAVEL_INSPECTOR_REDIRECT')) {
                    $redirectData = $request->session()->get('LARAVEL_INSPECTOR_REDIRECT');
                    session()->forget('LARAVEL_INSPECTOR_REDIRECT');
                }

                $inspectionBag = (string)view('inspector::view_console', [
                    'redirectData' => $redirectData,
                    'collectorsData' => $collectorsData,
                    ]);

            //	$inspectionBag = str_replace(array("\r\n", "\r", "\n", "\t"), '', $inspectionBag);
                $content = $response->getContent();
                // Ensure string content
                if (is_string($content)) {
                    $content = str_replace('</body>', $inspectionBag, $response->getContent());
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
            'default' => 'background-color:white; color:#222; line-height:1.2em; font-weight:normal; font:13px Monaco, Consolas, monospace; word-wrap: break-word; white-space: pre-wrap; position:relative; z-index:100000; border:0',
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
}
