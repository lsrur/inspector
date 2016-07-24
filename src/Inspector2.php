<?php

namespace Lsrur\Inspector;

use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Debug\Dumper;
use Illuminate\Support\Debug\HtmlDumper;

class Inspector
{
    private $data = [];
    private $sql = [];
    private $exception = null;
    public $is_on = true;
    private $outputType = 'script';

    public function getDump($v)
    {
        ob_start();
        $dumper = new Dumper;
        $dumper->dump($v);
        $result = ob_get_clean();
        return $result;    
    }

    public function isOn()
    {
        return $this->is_on;
    }

    
    public function turnOff()
    {
        $this->is_on = false;
    }

    public function addException($e)
    {
        $this->exception = $e;
    }

    protected function isJsonRequest(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            return true;
        }
        $acceptable = $request->getAcceptableContentTypes();
        return (isset($acceptable[0]) && $acceptable[0] == 'application/json');
    }

    private function renderScript($debugInfo)
    {
        if($this->outputType == 'json' || (\Config::get('inspector.force_json_output')))
        {
            return $this->renderJson();

        } else {
            return preg_replace('~[\r\n]+~', '', view('inspector::debugscript', $debugInfo));
        }
    }

    private function getDebugInfo()
    {
        return [
            'viewData'   =>  [],
            'title'      => '',
            'allocRam'   => $this->getAllocatedRAM(),
            'isScript'   => false,
            'isRedirect' => false,
            'data'       => $this->data,
            'request'    => [
                'URL' => request()->url(),
                'INPUT' => request()->all(), 
                'HEADERS' => request()->header()
                ], 
            'server'     => collect($_SERVER)->except(config('inspector.hide_server_keys',[])),
            'session'    => session()->all(),
            'exception'  => $this->exception, 
            'sql'        => $this->sql
        ];
    }

    public function injectInspection(Request $request, Response $response)
    {
      
        $debugInfo = $this->getDebugInfo();
    
        if($this->isJsonRequest($request) || $response->headers->get('content-type') == "application/json")
        {
            $content = json_decode($response->getContent(), true) ?: [];
            $debugInfo['title'] = $request->getMethod().':'.request()->url(). ' STATUS:'.$response->status();
            $content['_DEBUG'] = $this->renderScript($debugInfo);
            $response->setContent(json_encode($content));

        } else {
            if($response->isRedirect())
            {
                $debugInfo['title'] = 'REDIRECT:'.$request->Url()." -> ".$response->getTargetUrl();
                $debugInfo['isScript'] = true;
                $debugInfo['isRedirect'] = true;
                $script = $this->renderScript($debugInfo);
                $request->session()->flash('_DEBUG', $script);
        
            } else {

                if(isset($this->exception))
                {  //PROBAR CON EXCEPCIONES DURING AJAX REQUEST :TODO

                    $debugInfo['title']    = 'EXCEPTION '.get_class($this->exception);
                    $debugInfo['isScript'] = true;
                    $script = $this->renderScript($debugInfo);
                    
                    if(is_array($script)) $script = json_encode($script);
                    
                    $content = str_replace('</body>', $script, $response->getContent());   

                    $response->setContent($content);

                } elseif(gettype($response->getOriginalContent())=="object" && get_class($response->getOriginalContent()) == 'Illuminate\View\View')
                {
                    // View response
                    $debugInfo['viewData'] = $response->getOriginalContent()->getData();
                    $debugInfo['title']    = 'VIEW:'.$response->getOriginalContent()->getName();
                    $debugInfo['isScript'] = true;
                    $script = $this->renderScript($debugInfo);

                    if($request->session()->has('_DEBUG'))
                        $script = $request->session()->get('_DEBUG').$script;

                    $content = str_replace('</body>', $script, $response->getContent());
                    
                    $response->setContent($content);
                } else {
                    // type Response
                    $debugInfo['title']    = '(STRING RETURN)';
                    $debugInfo['isScript'] = true;
                    $script = $this->renderScript($debugInfo);

                    if($request->session()->has('_DEBUG'))
                        $script = $request->session()->get('_DEBUG').$script;
        
                    $content = '<body>'.$response->getContent().$script;
                
                    $response->setContent($content);                    
                }
         
        
            }

        }
    }

    public function dd()
    {
        $debugInfo = $this->getDebugInfo();
        if($this->isJsonRequest(request()) || request()->wantsJson())
        {
            echo json_encode($this->data);
            halt();
        } else {
            echo view('inspector::fullscreen', $debugInfo);
            exit;
        }
    }

    private function add($p1, $p2, $style, $steps = 3)
    {

        $name = null;
        $value = $p1; 
        if(isset($p2))
        {
            $name = $p1;
            $value = $p2;
        }

        $name .= $this->getTrace($steps);
        $this->data[] = ['name'=>$name,'value'=>$value, 'style'=>$style];
  
    }

    public function getAllocatedRAM()
    {
        return tb()->formatMemSize(memory_get_usage());
    }

    public function getData()
    {
        return $this->data;
    }    

    public function getSql()
    {
        return $this->sql;
    }

    public function group($name)
    {
        $this->data[] = ['name'=>$name, 'style'=>'group', 'start'=>microtime(true), 'end'=>0];
        
    }

    public function endGroup()
    {
        $end = microtime(true);

        for($i=count($this->data)-1; $i>=0; $i--)
        {
            if($this->data[$i]['style']=='group' && $this->data[$i]['end']==0)
                $this->data[$i]['end'] = $end;
        }
        $this->data[] = ['style'=>'endgroup'];
    }

    private function getTrace($steps = 2)
    {
        if(!isset(debug_backtrace()[$steps]['file'])) $steps--;
    
        $file = collect(explode('/', debug_backtrace()[$steps]['file']))->last();
        return " [".$file." #".debug_backtrace()[$steps]['line']."]";
    
    }

    public function table($p1, $p2=null)
    {
        $cnt = '';

        $name = 'unnamed'; 
        $values = $p1;
        if(isset($p2))
        {
            $name = $p1;
            $values = $p2;
        }
        $name .= $this->getTrace();

        if(is_array($values) || is_object($values))
        {
            $info = ' count:'.count($values).', json:'.tb()->formatMemSize(mb_strlen(json_encode($values)));
            $this->data[] = ['name'=>$name. $info,'style'=>'group', 'start'=>0, 'end'=>-1];
            $this->data[] = ['name'=>null,'value'=>$values, 'style'=>'table'];
            $this->data[] = ['style'=>'endgroup'];
         } else {
            $this->log($name." (cannot build table)", $values) ;
         }            
    }

    public function log($p1, $p2=null)
    {
        $this->add($p1,$p2,'log');
    }

    public function error($p1, $p2=null)
    {
        $this->add($p1,$p2,'error');
    }
    
    public function info($p1, $p2=null)
    {
        $this->add($p1,$p2,'info');
    }

    public function dump()
    {
        var_dump($this->data);
    }

    public function addSql($sql)
    {
        $this->sql[] = $sql;
    }
        
    /**
     * Used in timers
     * @return float
     */
    private function microtime_float()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

    public function toJson()
    {
        $this->outputType ='json';

    }

    private function renderJson()
    {
    //   dd($this->data);
        $result = []; $group='DEBUG';
        foreach ($this->data as $item) {
            if($item['style'] == 'group')
            {
                $group = $group=='' ? $item['name'] : $group.'.'.$item['name'];
                array_set($result, $group.'.profile', round( ($item['end'] - $item['start']) * 1000,2 ).'ms');
            } elseif($item['style']=='endgroup') 
            {
                $group = substr($group,0, strrpos($group, '.'));

            } else {
                $keyName = $group == '' ? '' : $group.'.';
                $keyName .= trim(str_replace('.',';',$item['name']));
                
                //dd($keyName);
                if(isset($item['value']))
                    array_set($result, $keyName, json_decode($item['value']));
            }

            $total=0; $sqls=[];
            foreach ($this->sql as $item) {
                $query = $item->sql;
                foreach ($item->bindings as $value) 
                {               
                    $query = preg_replace('/\?/', $value, $query, 1);
                }

                $sqls[] = $query.' ('.strval($item->time).')';
                $total = $total + $item->time;     
            }
            if(count($sqls>0))
            {
                array_set($result, 'SQLs', $sqls);
                array_set($result, 'SQLs.Total time', $total);
            }

            array_set($result, 'REQUEST.URL',request()->url()); 
            array_set($result, 'REQUEST.ROUTE',request()->route()->getPath().' ('.request()->route()->getName().') -> '.request()->route()->getActionName()); 
            array_set($result, 'REQUEST.INPUT', request()->all()); 
            array_set($result, 'REQUEST.HEADERS', request()->header()); 
            array_set($result, 'SERVER',collect($_SERVER)->except(config('inspector.hide_server_keys'))); 
            array_set($result, 'SESSION',session()->all()); 
        
        }

        return $result;
    }
}