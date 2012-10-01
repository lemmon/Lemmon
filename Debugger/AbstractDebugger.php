<?php

/*
 * This file is part of the Lemmon Framework (http://framework.lemmonjuice.com).
 *
 * Copyright (c) 2007 Jakub Pelák (http://jakubpelak.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lemmon\Debugger;

/**
 * Handles debugging.
 */
abstract class AbstractDebugger
{
	static $jQuery  = '//ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js';
	#static $onError = null;

	static private $_headersIncluded = false;
	static private $_objectsVisited  = [];


	static function loop($data, $level=0, $recursion=[], $more_recursion=[])
	{
		#if ($level==5) return PHP_EOL;
		$res = '';
		$indent0 = sprintf('%' . (($level+0) * 4) . 's', '');
		$indent1 = sprintf('%' . (($level+1) * 4) . 's', '');
		$indent2 = sprintf('%' . (($level+2) * 4) . 's', '');
		switch ($_=gettype($data))
		{
			case 'NULL':
				$res .= 'NULL';
				break;
			case 'string':
				$res .= '"' . htmlspecialchars($data) . '" <span class="note">(' . strlen($data) . ')</span>';
				break;
			case 'integer':
			case 'double':
				$res .= '' . $data . '';
				break;
			case 'boolean':
				$res .= '' . ($data ? 'true' : 'false') . '';
				break;
			case 'array':
				if ($data)
				{
					$res .= '<a class="LemmonDebugerExpander" href="#"><span class="mark">array</span><span class="note">(' . count($data) . ')</span> ';
					$res .= '<span class="note">{</span><span class="more' . (($level>1) ? '' : ' hide') . '">&hellip;</span></a>';
					$res .= '<span class="collapse' . (($level>1) ? '' : ' expand') . '">';
					$res .= PHP_EOL;
					foreach ($data as $key => $value)
					{
						$res .= $indent1
						        . (is_numeric($key) ? '#' : '$') . $key . ' <span class="note">=></span> '
						        . self::loop($value, $level+1, array_merge($recursion, $more_recursion), $data)
						        ;
					}
					$res .= $indent0;
					$res .= '<span class="note">}</span></span>';
				}
				else
				{
					$res .= '<span class="mark">array</span><span class="note">(0)</span> <span class="note">{}</span>';
				}
				break;
			case 'object':
				if (is_a($data, 'stdClass'))
				{
					if ($data)
					{
						$data_array = (array)$data;
						$res .= '<a class="LemmonDebugerExpander" href="#"><span class="mark">' . get_class($data) . '</span><span class="note">(' . count($data_array) . ')</span> ';
						$res .= '<span class="note">{</span><span class="more' . (($level>1) ? '' : ' hide') . '">&hellip;</span></a>';
						$res .= '<span class="collapse' . (($level>1) ? '' : ' expand') . '">';
						$res .= PHP_EOL;
						foreach ($data_array as $key => $value)
						{
							$res .= $indent1
							        . (is_numeric($key) ? '#' : '$') . $key . ' <span class="note">=></span> '
							        . self::loop($value, $level+1, array_merge($recursion, $more_recursion), $data_array)
							        ;
						}
						$res .= $indent0;
						$res .= '<span class="note">}</span></span>';
					}
					else
					{
						$res .= '<span class="mark">array</span><span class="note">(0)</span> <span class="note">{}</span>';
					}
				}
				elseif (!in_array($data, $recursion))
				{
					$refClass      = new \ReflectionClass($data);
					$refClass_name = $refClass->getName();
					$properties    = $refClass->getProperties();
					/*
					$methods       = $refClass->getMethods();
					*/
					/*
					$_refClass_parent = $refClass;
					$_properties = [];
					while ($_refClass_parent=$_refClass_parent->getParentClass())
					{
						$_properties = array_merge($_properties, $_refClass_parent->getProperties());
					}
					if ($_properties)
					{
						$properties = array_merge($properties, ['MORE'], $_properties);
					}
					*/
					$res .= '<a class="LemmonDebugerExpander" href="#"><span class="mark">' . $refClass_name . '</span><span class="note">(' . count($properties) . ')</span> ';
					#$res .= '<abbr>&#x25bc;</abbr> ';
					$res .= '<span class="note">{</span><span class="more' . (($level>1) ? '' : ' hide') . '">&hellip;</span></a>';
					$res .= '<span class="collapse' . (($level>1) ? '' : ' expand') . '">';
					$res .= PHP_EOL;
					foreach ($properties as $property)
					{
						if ($property!='MORE')
						{
							$property->setAccessible(true);
							$res .= $indent1
							        . '$' . $property->name
							        . ($property->class!=$refClass_name ? '<span class="note">:' . $property->class . '</span>' : '')
							        . ($property->isStatic() ? ' <span class="mark">static</span>' : '')
							        . ($property->isProtected() ? ' <span class="mark">protected</span>' : '')
							        . ($property->isPrivate() ? ' <span class="mark">private</span>' : '')
							        . ' <span class="note">=></span> '
							        #. $property->getValue($data)
							        . self::loop($property->getValue($data), $level+1, array_merge($recursion, $more_recursion, [$data]))
							        ;
						}
						else
						{
							$res .= $indent1 . '<a class="LemmonDebugerExpander" href="#"><span class="mark">more</span><span class="more">&hellip;</span></a>' . PHP_EOL . '<span class="collapse">';
							$_more = true;
						}
					}
					if ($_more)
					{
						$res .= '</span>';
						$_more = false;
					}
					/*
					if ($methods)
					{
						$res .= $indent1 . '<a class="LemmonDebugerExpander" href="#"><span class="mark">methods</span> ';
						$res .= '<span class="note">{</span><span class="more">&hellip;</span></a>' . PHP_EOL;
						$res .= '<span class="collapse">';
						foreach ($methods as $method)
						{
							if (!$_more and $method->class!=$refClass_name)
							{
								$res .= $indent2 . '<a class="LemmonDebugerExpander" href="#"><span class="mark">more</span><span class="more">&hellip;</span></a>' . PHP_EOL . '<span class="collapse">';
								$_more = true;
							}
							$res .= $indent2 . $method->name
							        . ($method->class!=$refClass_name ? '<span class="note">:' . $method->class . '</span>' : '')
							        . PHP_EOL
							        ;
						}
						if ($_more)
						{
							$res .= '</span>';
							$_more = false;
						}
						$res .= $indent1 . '<span class="note">}</span>' . PHP_EOL . '</span>';
					}
					*/
					$res .= $indent0;
					$res .= '<span class="note">}</span></span>';
				}
				else
				{
					$res .= '** RECURSION **';
				}
				break;
			default:
				$res .= "!! {$_} !!";
				break;
		}
		return $res . PHP_EOL;
	}


	static function printSource($file, $line=null)
	{
		// load source
		$source = htmlspecialchars(rtrim(file_get_contents($file)));
		
		// syntax highlight
		$source = preg_replace('/(\"[^\"]*\")/', '<span class="string">$1</span>', $source); // "" strings
		$source = preg_replace('/(\'[^\']*\')/', '<span class="string">$1</span>', $source); // '' strings
		$source = preg_replace('#/\*(.*?)\*/#sm', '<span class="note">/*$1*/</span>', $source); // /* comments
		$source = preg_replace('/(\/\/[^\n]*)/', '<span class="note">$1</span>', $source); // // comments
		$source = preg_replace('/(\#[^\n]*)/', '<span class="note">$1</span>', $source); // # comments
		#$source = preg_replace('/(define|function)/', '<span class="mark">$1</span>', $source); // special expressions

		// split to lines
		$source = explode("\n", $source);
		$l = strlen(count($source));
		
		// slice lines
		if ($line-8<0)
		{
			$cut_line = 0;
		}
		elseif ($line+8>count($source))
		{
			$cut_line = count($source) - 15;
		}
		else
		{
			$cut_line = $line - 8;
		}
		$source = array_slice($source, $cut_line, 15, true);

		// dump source
		foreach ($source as $i => $str)
		{
			if ($i+1==$line) echo '<span class="error">';
			echo sprintf('<span class="note">%' . $l . 'd:</span> %s', $i+1, $str);
			if ($i+1==$line) echo '</span>';
			echo PHP_EOL;
		}
	}


	static function includeHeaders($force=false)
	{
		if ($force or !self::$_headersIncluded)
		{
			include __DIR__ . '/_headers.php';
			self::$_headersIncluded = true;
		}
	}
}