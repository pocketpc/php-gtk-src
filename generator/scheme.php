<?php

require "definitions.php";

$char_table = str_repeat(' ', 256);
foreach (range(0, 255) as $i)
	$char_table[$i] = chr($i);
$translation_table = preg_replace('![^a-zA-Z_]!', '_', $char_table);

function parse($fp)
{
	$stack = array(array());
	while ($line = fgets($fp, 4096)) {
		while (($line = ltrim($line)) != '') {
			if ($line{0} == '(') {
				array_push($stack, array());
				$line = substr($line, 1);
			} else if ($line{0} == ')') {
				$closed = array_pop($stack);
				array_push($stack[count($stack)-1], $closed);
				$line = substr($line, 1);
			} else if ($line{0} == '"') {
				$pos = strpos($line, '"', 1);
				array_push($stack[count($stack)-1], substr($line, 1, $pos-1));
				$line = substr($line, $pos+1);
			} else if (strpos($line{0}, '0123456789') !== false) {
				$span = strspn($line, '0123456789+-.');
				$numstr = substr($line, 0, $span);
				$line = substr($line, $span);
				array_push($stack[count($stack)-1], (double)$numstr);
			} else if ($line{0} == ';')
				break;
			else {
				$span = strcspn($line, " \t();\n\r");
				$str = substr($line, 0, $span);
				$line = substr($line, $span);
				array_push($stack[count($stack)-1], $str);
			}
		}
	}
	if (count($stack) != 1)
		trigger_error("Parentheses don't match");
	return $stack[0];
}

class Defs_Parser {
	var $parse_tree 	= null;
	var $file_path  	= null;
	var $file_name  	= null;
	var $objects		= array();	// objects
	var $functions		= array();	// module functions
	var $constuctors	= array();  // object constructors
	var $methods		= array();  // object methods
	var $enums			= array();	// enums and flags
	var $c_name			= array();  // C names of entities

	
	function Defs_Parser($arg)
	{
		switch (gettype($arg)) {
			case 'string':
				print("Parsing file \"$arg\".\n");
				$this->file_name = $arg;
				$this->file_path = dirname($this->file_name);
				$this->parse_tree = parse(fopen($arg, 'r'));
				break;

			case 'array':
				$this->parse_tree = $arg;
				break;

			default:
				trigger_error('Constructor argument must be filename or array');
				break;
		}
	}

	function start_parsing($tree = NULL)
	{
		if (!isset($tree))
			$tree = $this->parse_tree;
		foreach ($tree as $node)
			$this->handle($node);
	}

	function handle($node)
	{
		global	$char_table,
				$translation_table;

		$cmd = "handle_" . strtr($node[0], $char_table, $translation_table);
		if (method_exists($this, $cmd))
			$this->$cmd(array_slice($node, 1));
		else
			$this->handle_unknown($node);
	}

	function handle_enum($arg)
	{
		$enum_def 		= &new Enum_Def($arg);
		$this->enums[] 	= &$enum_def;
		$this->c_name[] = &$enum_def->c_name;
	}

	function handle_flags($arg)
	{
		$flag_def 		= &new Flag_Def($arg);
		$this->enums[] 	= &$flag_def;
		$this->c_name[] = &$flag_def->c_name;
	}

	function handle_function($arg)
	{
		$function_def 		= &new Function_Def($arg);
		if (isset($function_def->is_constructor_of))
			$this->constuctors[] = &$function_def;
		else
			$this->functions[] = &$function_def;
		$this->c_name[] 	= &$function_def->c_name;
	}

	function handle_method($arg)
	{
		$method_def 		= &new Method_Def($arg);
		$this->methods[] 	= &$method_def;
		$this->c_name[] 	= &$method_def->c_name;
	}

	function handle_object($arg)
	{
		$object_def			= &new Object_Def($arg);
		$this->objects[]	= &$object_def;
		$this->c_name[] 	= &$object_def->c_name;
	}

	function handle_include($arg)
	{
		$include_file = $this->file_path . "/" . $arg[0];
		print("Parsing file \"$include_file\".\n");
		$include_tree = parse(fopen($include_file, 'r'));
		$this->start_parsing($include_tree);
	}

	function handle_unknown($node)
	{
		/* noop */
	}

	function find_methods($obj)
	{
		$obj_signature = array($obj->name, $obj->in_module);
		$obj_methods = array();

		foreach ($this->methods as $method) {
			if ($method->of_object == $obj_signature)
				$obj_methods[] = $method;
		}

		return $obj_methods;
	}

	function find_constructor($obj)
	{
		$obj_constructor = null;
		
		foreach ($this->constuctors as $constructor) {
			if ($constructor->is_constructor_of == $obj->name || $constructor->is_constructor_of == "Gtk$obj->name")
				$obj_constructor = $constructor;
		}

		return $obj_constructor;
	}

	function find_parent($obj)
	{
		$obj_parent = null;
		
		foreach ($this->objects as $object) {
			if ($object->name == $obj->parent)
				$obj_parent = $object;
		}

		return $obj_parent;
	}
}

?>
