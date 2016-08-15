<?php 

namespace Lsrur\Inspector\Collectors;

class ExceptionCollector extends BaseCollector
{

    public $title = 'Exceptions';

	private $exceptions = [];
    
    public function getPreJson()
    {
        $result = $this->get();

        $this->removeSrc($result);

        return $result;
    }


    public function getScript()
    {
        if($this->count() == 0) return "";
        $script = "console.group('EXCEPTIONS (".$this->count().")');"; 
        $styleWarning = 'font-size:10px;line-height:1.8em;border-radius:3px;padding:3px 5px;color:white; background-color: #F39C12';
        $styleError = 'font-size:10px;line-height:1.8em;border-radius:3px;padding:3px 5px;color:white; background-color: #E74C3C';
        $styleClass = 'font-size:10px; line-height:1.8em;';
        $styleMessage = 'font-size:10px; color:red; line-height:1.8em;';
        $styleFile = 'font-size:10px; font-weight:normal; line-height:1.8em;';

        foreach ($this->exceptions as $e) {
            if($e['caught'])
            {
                $caught = 'CAUGTH';
                $styleTag = 'font-size:10px;line-height:1.8em;border-radius:3px;padding:3px 5px;color:white; background-color: #18BC9C';
            } else {
                $caught = 'UNCAUGHT';
                $styleTag = 'font-size:10px;line-height:1.8em;border-radius:3px;padding:3px 5px;color:white; background-color: #E74C3C';
            }

            $fileName = $e['files'][0]['fileName'];
            
            if(count(explode('/', $fileName))>3)
                $fileName = '../'.collect(explode('/', $fileName))->slice(3)->implode('/');
            $title = "%c".$e['class']." %c".$this->e($e['message']).' %c('.$fileName.' #'.$e['files'][0]['line'].') %c'.$caught;
          
            //$title = $e['class'].' '.$e['files'][0]['fileName'].' #'.$e['files'][0]['line'].' (code '.$e['code'].') '.$caught;
            $script .= "console.groupCollapsed('".$title."','$styleClass','$styleMessage','$styleFile','$styleTag');";
    
            foreach ($e['files'] as $file) 
            {
                $title = "%c".$file['fileName'].' #'.$file['line'].' %c'.strtoupper($file['tag']);
                $styleTag = $file['tag'] == 'vendor' ? $styleTag = $styleWarning : $styleError;
                $script .= "console.groupCollapsed('$title','$styleClass', '$styleTag');";   
               // dd(explode('\n',$file['txt']));
                $l=0;
                foreach (explode(PHP_EOL,$file['source']) as $line)
                {
                    if($l++==3)
                    {
                       $script .= "console.warn('%c".$this->e($line)."','font-size:11px');";
                    } else {
                       $script .= "console.log('%c".$this->e($line)."','font-size:11px;');";
                       

                    }
                }

//                $script .= $this->cl('log', 'src', str_replace('&nbsp;', ' ',strip_tags($file['txt'])));   
                $script .= $this->clGroupEnd();
            }
            $script .= $this->clGroupEnd();

        }
        $script .= 'console.groupEnd();';
        return $script;
     
    }
    
    public function count()
    {
        return count($this->exceptions);
    }

    public function get()
    {
        return $this->exceptions;
    }

    public function b_addException($exception)
    {
        
        return $this->handleException($exception, true);
        
    }

    public function b_renderException($exception)
    {
        // forgive exceptions from formrequest validations
        if(get_class($exception) == 'Illuminate\Http\Exception\HttpResponseException' &&
            ends_with($exception->getFile(), 'FormRequest.php')) {
            return;
        }

        if(get_class($exception) == 'Illuminate\Foundation\Validation\ValidationException' &&
            ends_with($exception->getFile(), 'ValidatesRequests.php')) {
            return;
        }
        
        $this->handleException($exception, false);

    }

    public function handleException($exception, $caught=false)
    {

        if(! app('Inspector')->isOn()) return;

        $files = [['file'=>$exception->getFile(), 'line'=>$exception->getLine()]];
        
        foreach ($exception->getTrace() as $item) {
            if(isset($item['file']) && str_is(app_path().'*', $item['file']))
                $files[] = ['file'=>$item['file'],'line'=>$item['line']];
        }

        $files = $this->getSourceCode( collect($files)->unique('file')->toArray() );

        $this->exceptions[] = [ 
            'message' => $exception->getMessage(),
            'code'    => $exception->getCode(),
            'trace'   => $exception->getTrace(),
            'class'   => get_class($exception),
            'files'   => $files,
            'caught' => $caught
            ];    
         //   dd($collectorClass, $methodName);
         //   && config('inspector.exception_render', false)
        if(!$caught )
        {
            $status = (in_array('getStatusCode', get_class_methods(get_class($exception)))) ? $exception->getStatusCode() : 500;
            
            app('Inspector')->dd($status);

            
        }
    }
}
