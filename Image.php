<?php
/**
 * Handles image manipulations.
 *
 * @copyright  Copyright (c) 2007-2010 Jakub Pelák
 * @author     Jakub Pelák <jpelak@gmail.com>
 * @link       http://www.lemmonjuice.com
 * @package    lemmon
 */
class Lemmon_Image extends Application
{
	protected $imageSource;
	protected $imageDim;
	protected $imageW;
	protected $imageH;
	
	protected $cacheBase = 'images/';
	protected $parseCacheDir = '$sourceMD5/';
	protected $parseCacheFile = '$imageDim$fileExt';
	protected $maxCachedFiles = 200;
	
	static function newest($a, $b)
	{
	    return (filemtime($a) > filemtime($b)) ? -1 : 1;
	}
	
	public function index()
	{
		$this->imageSource = $this->route->image;
		$this->imageDim = $this->route->dim;
		
		return self::processImage();
	}

	private function _parseDim()
	{
		preg_match('/(\d+)?x(\d+)?([a-z])?([a-z]+)?/', $this->imageDim, $m);
		return array($m[1], $m[2], $m[3], $m[4]);
	}
	
	protected function processImage()
	{
		$source = $this->route->getUploadDir() . $this->imageSource;
		
		if ($this->imageSource)
		{
			if (file_exists($source))
			{
				$image_size = getimagesize($source);
				if ($image_size[2])
				{
					if ($this->imageDim)
					{
						$cache_dir = $this->cacheBase . $this->route->parse(
							$this->parseCacheDir,
							array(
								'source' => $this->imageSource,
								'sourceMD5' => md5($this->imageSource),
							)
						);
						if (!is_dir(Lemmon_Cache::getBase() . $cache_dir)) @mkdir(Lemmon_Cache::getBase() . $cache_dir, 0777, true);
						if (is_dir(Lemmon_Cache::getBase() . $cache_dir))
						{
							$cache_file = $this->route->parse(
								$this->parseCacheFile,
								array(
									'source' => $this->imageSource,
									'sourceMD5' => md5($this->imageSource),
									'fileName' => $this->route->fileName,
									'fileExt' => $this->route->fileExt,
									'imageDim' => $this->imageDim,
									'timestamp' => time(),
								)
							);
							$cache = Lemmon_Cache::getBase().$cache_dir.$cache_file;
							if (!file_exists($cache))
							{
								if ($this->imageDim)
								{
									list($w, $h, $param, $value) = $this->_parseDim();
								}
								else
								{
									$w = $image_size[0];
									$h = $image_size[1];
								}
								$ratio = $image_size[0]/$image_size[1];
								// center
								switch ($param) {
									case 'm':
										// copute to longer proportion (max. size)
										if ($image_size[0]>$w or $image_size[1]>$h)
										{
											// copute to longer proportion (box)
											if ($w/$h>$ratio)
											{
												$w0 = round($image_size[0]/$image_size[1]*$h);
												$h0 = $h;
											}
											else
											{
												$w0 = $w;
												$h0 = round($image_size[1]/$image_size[0]*$w);
											}
										}
										else
										{
											// box as image
											$w0 = $image_size[0];
											$h0 = $image_size[1];
										}
										// fit
										$w = $w0;
										$h = $h0;
										break;
									case 'b':
										// copute to longer proportion (box)
										if ($w/$h>$ratio)
										{
											$w0 = round($image_size[0]/$image_size[1]*$h);
											$h0 = $h;
										}
										else
										{
											$w0 = $w;
											$h0 = round($image_size[1]/$image_size[0]*$w);
										}
										// center
										$left = 0-round(($w0-$w)/2);
										$top = 0-round(($h0-$h)/2);
										$fill = array(255, 255, 255);
										break;
									case 'c':
									default:
										// compute
										if ($w and $h)
										{
											// copute to shorter proportion
											if ($w/$h>$ratio)
											{
												$w0 = $w;
												$h0 = round($image_size[1]/$image_size[0]*$w);
											}
											else
											{
												$w0 = round($image_size[0]/$image_size[1]*$h);
												$h0 = $h;
											}
											// align
											if ($value{0}=='l')
											{
												$left = 0;
											}
											elseif ($value{0}=='r')
											{
												$left = 0-($w0-$w);
											}
											else
											{
												$left = 0-round(($w0-$w)/2);
											}
											if ($value{0}=='t' or $value{1}=='t')
											{
												$top = 0;
											}
											elseif ($value{0}=='b' or $value{1}=='b')
											{
												$top = 0-($h0-$h);
											}
											else
											{
												$top = 0-round(($h0-$h)/2);
											}
										}
										elseif ($w)
										{
											$w0 = $w;
											$h0 = $h = round($w/$ratio);
											$left = 0;
											$top = 0;
										}
										elseif ($h)
										{
											$w0 = $w = round($h*$ratio);
											$h0 = $h;
											$left = 0;
											$top = 0;
										}
										break;
								}
								/*
								echo "{$image_size[0]} x {$image_size[1]}<br>";
								echo "{$w}+{$left} x {$h}+{$top}<br>";
								echo "{$w0} x {$h0}<br>";
								echo $image_size['mime'];
								die;
								*/
								// load image
								switch ($image_size[2])
								{
									case 1: $im = @ImageCreateFromGIF($source); break;
									case 2: $im = @ImageCreateFromJPEG($source); break;
									case 3: $im = @ImageCreateFromPNG($source); break;
									default: return $this->box('Unsupported image type', $w, $h);
								}
								// process image
								if ($im)
								{
									// prepare thumbnail
									$thumb = @ImageCreateTrueColor($w, $h);
									// fill background
									if ($fill)
									{
										$_color = imagecolorallocate($thumb, $fill[0], $fill[1], $fill[2]);
										imagefill($thumb, 1, 1, $_color);
									}
									// resize thumbnail
									@ImageAlphaBlending($thumb, false);
									@ImageCopyResampled($thumb, $im, $left, $top, 0, 0, $w0, $h0, $image_size[0], $image_size[1]);
									// output image
									switch ($image_size['mime'])
									{
										case 'image/jpeg':
											$res = ImageJPEG($thumb, $cache);
											break;
										case 'image/png':
											ImageSaveAlpha($thumb, true);
											$res = ImagePNG($thumb, $cache);
											break;
										case 'image/gif':
											$res = ImageGIF($thumb, $cache);
											break;
									}
									imagedestroy($thumb);
									imagedestroy($im);
								}
								else
								{
									return $this->box('Not a valid image', $w, $h);
								}
							}
							else
							{
								Lemmon_Cache::headers( $cache, filemtime($cache) );
							}
							// print image
							header('Content-Type: '.$image_size['mime']);
							echo file_get_contents($cache);
						}
						else
						{
							list($w, $h) = $this->_parseDim();
							return $this->box('Cache directory not writable', $w, $h);
						}
					}
					else
					{
						// print untouched image
						header('Content-Type: '.$image_size['mime']);
						echo file_get_contents($source);
					}
				}
				else
				{
					list($w, $h) = $this->_parseDim();
					return $this->box('Not an image', $w, $h);
				}
			}
			else
			{
				list($w, $h) = $this->_parseDim();
				return $this->box('Image not found', $w, $h);
			}
		}
		else
		{
			list($w, $h) = $this->_parseDim();
			return $this->box("{$w}x{$h}", $w, $h);
		}
		
		return false;
	}
	
	static public function box($text, $w=null, $h=null)
	{
		if ($w and !$h) $h = $w;
		elseif (!$w and $h) $w = $h;
		elseif (!$w and !$h) $w = $h = 250;
		$im = @imagecreate($w, $h);
		$background_color = imagecolorallocate($im, 200, 200, 200);
		$text_color = imagecolorallocate($im, 32, 32, 32);
		$text_w = strlen($text)*5-1;
		if ($text_w>$w)
		{
			$text = 'n/a';
			$text_w = 17;
		}
		imagestring($im, 1, round(($w-$text_w)/2), round($h/2)-4,  $text, $text_color);
		header("Content-type: image/png");
		imagepng($im);
		imagedestroy($im);
		return false;
	}
}
