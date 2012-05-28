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
			'asciize' => new Twig_Filter_Function('Lemmon\String::asciize'),
			'human' => new Twig_Filter_Function('Lemmon\String::human'),
			'nl2br' => new Twig_Filter_Function('Lemmon\String::nl2br'),
			'text' => new Twig_Filter_Function('Lemmon\String::text'),
			'line' => new Twig_Filter_Function('Lemmon\String::line'),
			'url_link' => new Twig_Filter_Function('Lemmon\String::urlLink'),
			'url_caption' => new Twig_Filter_Function('Lemmon\String::urlCaption'),
			'email_hide' => new Twig_Filter_Function('Lemmon\String::emailHide'),
			'entities' => new Twig_Filter_Function('Lemmon\String::entities'),
			'indent' => new Twig_Filter_Function('Lemmon\String::indent'),
			'removeIndent' => new Twig_Filter_Function('Lemmon\String::removeIndent'),
			
			// Numbers
			'round' => new Twig_Filter_Function('round'),

			// Debug
			'dump' => new Twig_Filter_Function('Lemmon\Template::debug'),
			'vardump' => new Twig_Filter_Function('Lemmon\Template::varDump'),
			
		);
	}


	public function getName()
	{
		return 'project';
	}
}
