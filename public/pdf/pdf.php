<?php
/** 
 * office文档转换为PDF类
 */  

class office2pdf  
{  
    private $osm;  

    public function __construct()  
    {  
        $this->osm = new COM("com.sun.star.ServiceManager") or 
        die ("Please be sure that OpenOffice.org is installed.n");   
    }  

    public function MakePropertyValue($name,$value)  
    {  
        $oStruct = $this->osm->Bridge_GetStruct("com.sun.star.beans.PropertyValue");  
        $oStruct->Name = $name;  
        $oStruct->Value = $value;  
        return $oStruct;  
    }  

    public function transform($input_url, $output_url)  
    {  
        $args = array($this->MakePropertyValue("Hidden",true));    
        $oDesktop = $this->osm->createInstance("com.sun.star.frame.Desktop");    
        $oWriterDoc = $oDesktop->loadComponentFromURL($input_url,"_blank", 0, $args) or die('{"status":0}'); 
        $export_args = array($this->MakePropertyValue("FilterName","writer_pdf_Export"));    
        $oWriterDoc->storeToURL($output_url,$export_args); 
        $oWriterDoc->close(true);  
        //return $this->getPdfPages($output_url);  
    }  

    public function run($input,$output)  
    {  
        $input = "file:///" . str_replace("\\","/",$input);  
        $output = "file:///" . str_replace("\\","/",$output);
        return $this->transform($input, $output);  
    }  

    /** 
     * 获取PDF文件页数的函数获取 
     * 文件应当对当前用户可读（linux下） 
     * @param  [string] $path [文件路径] 
     * @return int 
     */  
    public function getPdfPages($path)  
    {  
        if(!file_exists($path)) return 0;  
        if(!is_readable($path)) return 0;  
        // 打开文件  
        $fp=@fopen($path,"r");  
        if (!$fp)   
        {  
            return 0;  
        }  
        else   
        {  
            $max=0;  
            while(!feof($fp))   
            {  
                $line = fgets($fp,255);  
                if (preg_match('/\/Count [0-9]+/', $line, $matches))  
                {  
                    preg_match('/[0-9]+/',$matches[0], $matches2);  
                    if ($max<$matches2[0]) $max=$matches2[0];  
                }  
            }  
            fclose($fp);  
            // 返回页数  
            return $max;  
        }  
    }  

}