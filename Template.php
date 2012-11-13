<?php

/*
 * This file is part of the Lemmon Framework (http://framework.lemmonjuice.com).
 *
 * Copyright (c) 2007 Jakub Pelák (http://jakubpelak.com)
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


	static function setFilesystem($filesystem, $include_lemmon = false)
	{
		$filesystem = array(ROOT_DIR . '/' . $filesystem);
		if ($include_lemmon) $filesystem[] = LIBS_DIR . '/Lemmon/Template';
		self::$_filesystem = $filesystem;
	}


	static function appendFilesystem($filesystem_to_append)
	{
		// array
		if (!is_array($filesystem_to_append)) $filesystem_to_append = [$filesystem_to_append];
		// append
		foreach ($filesystem_to_append as $dir)
		{
			$dir = ($dir{0}=='/') ? $dir : ROOT_DIR . '/' . $dir;
			array_unshift(self::$_filesystemAppended, $dir);
		}
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
	static function display($file, array $data = [])
	{
		// setup template filesystem
		if ($file{0} == '/')
		{
			$template_loader = new \Twig_Loader_Filesystem(dirname($file));
			$file_name = basename($file);
		}
		else
		{
			// append more filesystems if file is nested
			if (($n=count($file_path=explode('/', $file))-1) and $tpl_base=substr(self::getFilesystem()[0], strlen(ROOT_DIR . '/')))
			{
				array_pop($file_path);
				for ($i=1; $i <= $n; $i++)
				{
					self::appendFilesystem($tpl_base . '/' . join('/', array_slice($file_path, 0, $i)));
				}
			}
			//
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
}