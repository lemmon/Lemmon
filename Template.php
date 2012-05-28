<?php

/*
 * This file is part of the Lemmon package.
 *
 * (c) Jakub Pelák <jpelak@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lemmon;

/**
 * Handles templates.
 */
class Template
{
	private static $_filesystem;
	private static $_filesystemAppended = array();
	private static $_environment = array();
	private static $_cache = 'tpl';


	static function getFilesystem()
	{
		if (!($filesystem=self::$_filesystem))
		{
			$filesystem = array( ROOT_DIR . '/app/views', LIBS_DIR . '/Lemmon/Template' );
		}
		if ($filesystem_appended=self::$_filesystemAppended)
		{
			$filesystem = array_merge($filesystem_appended, $filesystem);
		}
		return $filesystem;
	}


	static function setFilesystem($filesystem, $include_lemmon=false)
	{
		$filesystem = array(ROOT_DIR . '/' . $filesystem);
		if ($include_lemmon) $filesystem[] = LIBS_DIR . '/Lemmon/Template';
		self::$_filesystem = $filesystem;
	}


	static function appendFilesystem($filesystem_to_append)
	{
		array_unshift(self::$_filesystemAppended, ROOT_DIR . '/' . $filesystem_to_append);
	}


	static function getEnvironment()
	{
		$environment = self::$_environment;
		$environment['cache'] = \Lemmon_Cache::getBase() . self::$_cache;
		return $environment;
	}


	static function setEnvironment($environment)
	{
		self::$_environment = $environment;
	}


	/**
	 * Renders and displays templat file.
	 * @param string $file
	 * @param array  $data
	 */
	static function display($file, array $data=null)
	{
		// setup template filesystem
		if ($file{0}=='/')
		{
			$template_loader = new \Twig_Loader_Filesystem(dirname($file));
			$file_name = basename($file);
		}
		else
		{
			$template_loader = new \Twig_Loader_Filesystem(self::getFilesystem());
			$file_name = $file . '.html';
		}
		
		// environment
		$template_environment = new \Twig_Environment($template_loader, self::getEnvironment());
		$template_environment->addExtension(new \Lemmon_Template_Extension());

		// template
		$template = $template_environment->loadTemplate($file_name);

		// display
		return $template->render($data);
	}


	static function debug($dump)
	{
		return '<pre class="debug">' . print_r($dump, 1) . '</pre>';
	}


	static function varDump($dump)
	{
		return '<pre class="debug">' . var_export($dump, 1) . '</pre>';
	}
}