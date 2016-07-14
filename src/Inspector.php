<?php

namespace Lsrur\Inspector;

use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\Response;

class Inspector
{
    private $data = [];
    private $sql = [];
    private $exception = null;

    private function getSize($size, $precision = 2) {
        $units = array('Bytes','kB','MB','GB','TB','PB','EB','ZB','YB');
        $step = 1024;
        $i = 0;
        while (($size / $step) > 0.9) {
            $size = $size / $step;
            $i++;
        }
        return round($size, $precision).$units[$i];
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
        return preg_replace('~[\r\n]+~', '', view('inspector::debugscript', $debugInfo));
    }

    public function injectScript(Request $request, Response $response)
    {
        $debugInfo = [
            'viewData'   =>  [],
            'isScript'   => false,
            'isRedirect' => false,
            'data'       => $this->data,
            'exception'  => $this->exception, 
            'sql'        => $this->sql
        ];

        if($this->isJsonRequest($request))
        {
            $content = json_decode($response->getContent()) ?: [];
            $debugInfo['title'] = $request->getMethod().':'.request()->url(). ' STATUS:'.$response->status();
            $script = $this->renderScript($debugInfo);
            $content['_DEBUG'] = $script;            
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
                {
                    
                    $debugInfo['title']    = 'EXCEPTION '.get_class($this->exception);
                    $debugInfo['isScript'] = true;
                    $script = $this->renderScript($debugInfo);

                    $content = str_replace('</body>', $script, $response->getContent());
                    
                    $response->setContent($content);
                } elseif(is_object($response->getOriginalContent())) {
                    
                    $debugInfo['viewData'] = $response->getOriginalContent()->getData();
                    $debugInfo['title']    = 'VIEW:'.$response->getOriginalContent()->getName();
                    $debugInfo['isScript'] = true;
                    $script = $this->renderScript($debugInfo);

                    if($request->session()->has('_DEBUG'))
                        $script = $request->session()->get('_DEBUG').$script;

                    $content = str_replace('</body>', $script, $response->getContent());
                    
                    $response->setContent($content);
                } else {
                    
                    $debugInfo['title']    = '(STRING RETURN)';
                    $debugInfo['isScript'] = true;
                    $script = $this->renderScript($debugInfo);

                    if($request->session()->has('_DEBUG'))
                        $script = $request->session()->get('_DEBUG').$script;

                    $content = $response->getContent().$script;
                    
                    $response->setContent($content);                    
                }
         
        
            }

        }
    }

    private function add($p1, $p2, $style)
    {

        $name = null;
        $value = $p1; 
        if(isset($p2))
        {
            $name = $p1;
            $value = $p2;
        }
        if( is_array($value) || is_object($value) ) 
        {
            $value = json_encode($value,0);
        } else {
            $value = "'$value'";
        }
        $name .= $this->getTrace(3);
        $this->data[] = ['name'=>$name,'value'=>$value, 'style'=>$style];
  
    }

    public function getAllocatedRAM()
    {
        return $this->getSize(memory_get_usage());
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
            $info = ' count:'.count($values).', json:'.$this->getSize(mb_strlen(json_encode($values)));
            $this->data[] = ['name'=>$name. $info,'style'=>'group', 'start'=>0, 'end'=>-1];
            $this->data[] = ['name'=>null,'value'=>json_encode($values), 'style'=>'table'];
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

}