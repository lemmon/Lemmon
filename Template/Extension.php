<?php
/**
 * General Twig templating framework extension object.
 *
 * @copyright  Copyright (c) 2007-2010 Jakub Pelák
 * @author     Jakub Pelák <jpelak@gmail.com>
 * @link       http://www.lemmonjuice.com
 * @package    lemmon
 */
class Lemmon_Template_Extension extends Twig_Extension
{

	
	public function getFilters()
	{
		return array(
			
			// I18n
			't' => new Twig_Filter_Function('Lemmon_I18n::t'),
			'tn' => new Twig_Filter_Function('Lemmon_I18n::tn'),
			'tDate' => new Twig_Filter_Function('Lemmon_I18n::date'),
			'tTime' => new Twig_Filter_Function('Lemmon_I18n::time'),
			'tDateTime' => new Twig_Filter_Function('Lemmon_I18n::datetime'),
			'tPrice' => new Twig_Filter_Function('Lemmon_I18n::price'),
			'tPriceInt' => new Twig_Filter_Function('Lemmon_I18n::priceInt'),
			'tNum' => new Twig_Filter_Function('Lemmon_I18n::num'),
			'fileSize' => new Twig_Filter_Function('Lemmon_I18n::fileSize'),
			
			// Arrays
			'first' => new Twig_Filter_Function('Lemmon_Array::first'),
			'last' => new Twig_Filter_Function('Lemmon_Array::last'),
			'assoc' => new Twig_Filter_Function('Lemmon_Array::assoc'),
			'hasKey' => new Twig_Filter_Function('Lemmon_Array::hasKey'),
			'isIn' => new Twig_Filter_Function('Lemmon_Array::isIn'),
			
			// Files
			'fileName' => new Twig_Filter_Function('basename'),
			
			// Strings
			'asciize'       => new Twig_Filter_Function('Lemmon\String::asciize'),
			'human'         => new Twig_Filter_Function('Lemmon\String::human'),
			'nl2br'         => new Twig_Filter_Function('Lemmon\String::nl2br'),
			'html'          => new Twig_Filter_Function('Lemmon\String::text', ['pre_escape' => 'html', 'is_safe' => ['html']]),
			'html_sanitize' => new Twig_Filter_Function('Lemmon\String::sanitizeHtml', ['is_safe' => ['html']]),
			'line'          => new Twig_Filter_Function('Lemmon\String::line'),
			'p'             => new Twig_Filter_Function('Lemmon\String::paragraph'),
			
			// Numbers
			'round'        => new Twig_Filter_Function('round'),

			// Debug
			'dump'         => new Twig_Filter_Function('Lemmon\Debugger::dump', ['pre_escape' => 'html', 'is_safe' => ['html']]),
			
		);
	}


	public function getName()
	{
		return 'project';
	}
}
