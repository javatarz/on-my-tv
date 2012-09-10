<?
/*

Eze Template Class 1.25B
Slight Optimization - no case matching = fastER

Miscellaneous - Alternating Text Replace: {*string1|string2*} - alternately replaced with <string1> and <string2> between loops
Template::NONE - Normal Variable Replace: {$variablename} - replaced with <variablename> value
Template::BOOL - Boolean Variable Replace: {^variablename^string^} - will output string only if assigned <variablename> is true
Template::REPEAT - Repetition Variable Replace: {!variablename!string!} - will repeat string <variablename> times

Boolean / Repetition can be combined with Normal replaces, or other booleans, allowing nesting (replaces within replaces)

*/
class Template
{
	var $content;

	var $callback;

	var $lang = array();

	const NONE = 0;
	const REPEAT = 1;
	const BOOL = 2;

	function Template($_contentsfile) 
	{
		$this->content = @file_get_contents(TEMPLATE_DIR . '/' . $_contentsfile . ".tpl");
		if(strlen($this->content) < 1) {
			$this->content = 'Empty Template. (Missing file or wrong template dir - ' . $_contentsfile . ".tpl" . '?)';
		}
	}

	function setLanguage($_langArray) {
		if(is_array($_langArray)) {
			$this->lang = $_langArray;
		}
	}

    function assign_assoc($array) {
        foreach($array as $key=>$value) {
            if(is_array($value)) {
                $this->assign($key,$value[0],$value[1]);
            } else {
                $this->assign($key,$value);
            }
        }
    }
    
	function assign($_varname,$_value,$_type = self::NONE) 
	{
	
		switch($_type) {
			
			case self::BOOL:
				
				preg_match_all('%\{\^' . $_varname . '\^(.+)(\^(.*))?\^\}%UmsS',$this->content,$m);
				if($_value == true) {
					$this->content = str_replace($m[0],$m[1],$this->content);
				} else {
					$this->content = str_replace($m[0],$m[3],$this->content);
				}

			break;

			case self::REPEAT:

				preg_match('%\{!' . $_varname . '!(.+)!\}%UmsS',$this->content,$m);
				$this->content = str_replace($m[0],str_repeat($m[1],$_value),$this->content);
				
			break;

			case self::NONE:
				$this->content = str_replace('{$' . $_varname . '}',$_value,$this->content);
			break;
		
		}

	}

	function startLoop($_loopname) 
	{
		preg_match("/{\%" . $_loopname . "\%}(.+){\%" . $_loopname . "\%}/UmsS",$this->content,$m);
		return new TemplateLoop($_loopname,$m[1]);
	}

	function endLoop($_templateLoop) 
	{

		if(isset($this->callback) && function_exists($this->callback)) {
			$_value = call_user_func_array($this->callback,array($_templateLoop->returnContent()));
		} else {
			$_value = $_templateLoop->returnContent();
		}


		$this->content = preg_replace('/{\%' . $_templateLoop->loopname . '\%}.+{\%' . $_templateLoop->loopname . '\%}/UmsS',$_value,$this->content);
		$_templateLoop = null;
	}

	function output() 
	{
		return $this->content;
	}



}

class TemplateLoop
{
	var $outputcontent;
	var $content;
	var $origcontent;
	var $loopname;
	var $lodd;

	function TemplateLoop($_loopname,$_content) 
	{
		$this->lodd = 0;
		$this->outputcontent = "";
		$this->origcontent = $_content;
		$this->content = $_content;
		$this->loopname = $_loopname;

	}

	function doAlternate() {
		$v = (($this->lodd % 2) == 0)? 1 : 2;
		$this->content = preg_replace('%\{\*(.+)\|(.+)\*\}%UmsS','${' . ($v) . '}',$this->content); 
		$this->lodd++;
	}

	function nextLoop() 
	{
		$this->doAlternate();
		$this->outputcontent .= $this->content;
		$this->content = $this->origcontent;
	}

	function assign($_varname,$_value,$_type = Template::NONE) 
	{

		switch($_type) {
			
			case Template::BOOL:
				preg_match_all('%\{\^' . $_varname . '\^(.+)(\^(.*))?\^\}%UmsS',$this->content,$m);
				if($_value == true) {
					$this->content = str_replace($m[0],$m[1],$this->content);
				} else {
					$this->content = str_replace($m[0],$m[3],$this->content);
				}

			break;

			case Template::REPEAT:

				preg_match('%\{!' . $_varname . '!(.+)!\}%UmsS',$this->content,$m);
				$this->content = str_replace($m[0],str_repeat($m[1],$_value),$this->content);
				
			break;

			case Template::NONE:
				$this->content = str_replace('{$' . $_varname . '}',$_value,$this->content);
			break;
		
		}
	}

	function returnContent()
	{
		return $this->outputcontent;
	}

}

?>