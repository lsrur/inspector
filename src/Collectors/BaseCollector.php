<?php 

namespace Lsrur\Inspector\Collectors;


abstract class BaseCollector
{
    public $title;
    public $showCounter = true;
    private $defaultStyle = "font-size:11px; line-height:1.5em;";

    protected function genericToScript($data)
    {
        $tit = strtoupper(str_slug($this->title));
        $result = "console.groupCollapsed('$tit');";
        foreach ($data as $key => $value) 
        {
            $result .= $this->cl('log', $key.':', $value);
        }
        $result .= "console.groupEnd();";
        return $result;
    }

    protected function clGroup($name)
    {
        return "console.groupCollapsed('%c".$name."','".$this->defaultStyle. "'); ";
    }

    protected function clGroupEnd()
    {
        return "console.groupEnd(); ";
    }

    protected function clTable($data)
    {
        return "console.table(".json_encode($data).");";
    }

    /**
     * "Console log" helper     
     * @param  string $cmd   
     * @param  string $title 
     * @param  mixed $data  
     * @return string        
     */
    protected function cl($cmd, $title, $data)
    {
        if(substr($title,-1)!==':') $title.=':';
        $styles = [
            'info' => $this->defaultStyle.'border-radius:3px;padding:2px 3px;color:white; background-color: #3498DB',
            'warning' => $this->defaultStyle.'border-radius:3px;padding:2px 3px;color:white; background-color: #F39C12',
            'success' => $this->defaultStyle.'border-radius:3px;padding:2px 3px;color:white; background-color: #18BC9C',
            'error' => $this->defaultStyle.'border-radius:3px;padding:2px 3px;color:white; background-color: #E74C3C',
        ];
        
        if(in_array($cmd, ['info','warning', 'success', 'error']))
        {
            $title ="'%c".strtoupper($cmd)."%c $title'".
                ",'".$styles[$cmd]."', 'font-size:10px; font-weight:bold'";
        } else {
               $title ="'%c $title', 'font-size:10px; font-weight:bold'";
        }
        return "console.log(".$title.",".json_encode($data)."); ";
    }

    /**
     * Return file and line number
     * @param  integer $steps 
     * @return string        
     */
    protected function getTrace($steps = 3)
    {
        if(!isset(debug_backtrace()[$steps]['file'])) $steps--;
        $file = collect(explode('/', debug_backtrace()[$steps]['file']))->last();
        return $file." #".debug_backtrace()[$steps]['line'];
    }
    
    /**  
     *  Extract source code file 
     * @param  array $files 
     * @return array        
     */
  	protected function getSourceCode($files)
    {        
        for($j=0;$j<count($files);$j++)
        {
            $src=[]; $txt='';
            if(isset($files[$j]['file']))
            {
                $sourceFile = $files[$j]['file'];
                $fromLine = $files[$j]['line'] - 3;
                $toLine = $fromLine + 6;
                $i=0;
                $handle = fopen($sourceFile, "r");
                if ($handle) {
                    $src[] = '<?php'.PHP_EOL;
                    while (($line = fgets($handle)) !== false) 
                    {
                        $line = str_replace("\r\n", "\n", $line);
                        $line = str_replace("\r", "\n", $line);
                        $i++; 
                        if($i>=$fromLine && $i<=$toLine)
                        {     
                            $txt .= $i.':'.$line;
                            if($i == $files[$j]['line'])
                            {
                                $src [] = '-@'.$i.':'.substr($line,0,-1).'@-';
                            } else {                  
                                $src [] = $i.':'.$line;
                            }
                        }
                    }
                    fclose($handle);

                    $src = highlight_string(implode("",$src), true);
                    $src = str_replace('-@', '<div style="background-color:#FFDFD8 !important">', $src);
                    $src = str_replace('@-', '</div>', $src);
                    $src = str_replace('&lt;?php<br />', '', $src);
                    $src = str_replace('\n', '', $src);
                    $files[$j]['src'] = $src;
                    $files[$j]['source'] = $txt;
                    $files[$j]['fileName'] = '..'.substr($files[$j]['file'],strlen(base_path()));
                    $files[$j]['tag'] = strpos($files[$j]['file'], app_path()) === false ? 'vendor' : 'my code';
                }
            }        
        } 
        return $files;
    }

    protected function removeSrc(&$items)
    {
        foreach ($items as &$item) 
        {
            foreach ($item['files'] as &$file) {
                unset($file['src']);
                unset($file['fileName']);
            }
        }
    }

    /**
     * Escape quotes
     * @param  string $str 
     * @return string      
     */
    protected function e($str)
    {
        return str_replace("'",'`', $str);
    }

    abstract protected function get();
    abstract protected function getPreJson();
    abstract protected function getScript();
    abstract protected function count();
 
}