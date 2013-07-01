<?php

namespace Lemmon;

/**
 * General strings helper functions.
 *
 * @copyright  Copyright (c) 2007-2012 Jakub Pelák
 * @author     Jakub Pelák <jpelak@gmail.com>
 * @link       http://www.lemmonjuice.com
 * @package    lemmon
 */
class String
{


    /**
     * Converts variable name to human representation.
     */
    static public function human($str)
    {
        $str = str_replace('_id', '', $str);
        $str = str_replace(array('-', '_', '.'), ' ', $str);
        $str = trim($str);
        $str = ucwords($str);
        return $str;
    }


    /**
     * Returns html.
     * @param  string  $html
     * @return string
     * @todo   it just returns $html variable
     */
    public static function html($html)
    {
        return $html;
    }


    private static function _sanitizeImageBlock($block)
    {
        preg_match_all('#([^=\s]+)="([^"]*)"#i', $block[2], $m, PREG_SET_ORDER);
        foreach ($m as $_arg) $args[$_arg[1]] = $_arg[2];
        if (preg_match('#(left|right|center)#i', $block[1], $m)) $align = $m[1];
        $src = preg_replace('#uploads(/0\d*x\d*[a-z]*)?#i', 'uploads/0' . $args['width'] . 'x', $args['src']);
        $res = '<div class="image' . ($align ? ' ' . $align : '') . '" style="width:' . $args['width'] . 'px"><img src="'  . $src .'" width="' . $args['width'] . '"></div>';
        return $res;
    }


    public static function sanitizeHtml($html)
    {
        $html = preg_replace('#[ ]*\r?\n[ ]*#', "\n", $html); // remove \r's
        $html = preg_replace('#\xEF\xBB\xBF#', '', $html); // remove stupid characters
        $html = preg_replace('#<(\w+)>(\xC2\xA0|\s+)*</\\1>#', '', $html); // remove whitespace including nbsp's
        $html = preg_replace('#<(\w+)[^>]*>(\xC2\xA0|\s+)*</\1>#', '', $html); // remove whitespace including nbsp's
        $html = preg_replace('#</(p|h\d|ol|ul|dl|div|table)>#', "\n\n", $html); // paragraphs
        $html = preg_replace('#[\t ]*<br(\s*/)?>[\t ]*#', "\n", $html); // new lines
        $html = preg_replace('#[\t ]*\n[\t ]*#', "\n", $html); // whitespace remove
        $html = trim($html);
        // split blocks/paragraphs
        $html = preg_split('#\n{2,}#', $html);
        //
        foreach ($html as $i => $line)
        {
            // process images
            preg_match('#class="([^"]+)".+<img([^>]*)>#i', $line, $m);
            if ($m)
            {
                $line = self::_sanitizeImageBlock($m);
            }
            // rest
            else
            {
                preg_match('#^<(p|h\d|ol|ul|dl|div|table)#', $line, $tag);
                $tag_open = $tag[1];
                // more cleanup
                if (!$tag_open or ($tag_open!='ul' and $tag_open!='ul')) $line = preg_replace('#</?li[^>]*>#i', '', $line); // remove LIs from non lists
                elseif (!$tag_open or ($tag_open!='dl')) $line = preg_replace('#</?(dt|dd)[^>]*>#i', '', $line); // remove DTs and DDs from non definition lists
                else $line = str_replace("\n", "<br>\n", $line); // newlines to BRs
                // wrap blocks
                if ($tag_open)
                {
                    $tag_close = '</' .$tag_open . '>';
                    if (substr($line, -strlen($tag_close)) != $tag_close) $line .= $tag_close;
                }
                else
                {
                    $line = '<p>' . $line . '</p>';
                }
            }
            //
            $html[$i] = str_replace("\n", "<br>\n", $line);
        }
        //
        return join("\n\n", $html);
    }


    /**
     * Converts plain text to html.
     * @param  string  $text
     * @return string
     * @todo   probably merge text() & html() to textToHtml()
     */
    public static function text($text)
    {
        if ($text)
        {
            $text = trim($text);
            $text = preg_replace('#[ ]*\r?\n[ ]*#', "\n", $text);
            $text = preg_replace('/\n{2,}/', '</p><p>', $text);
            $text = preg_replace('/\n/', '<br>', $text);
            $text = $text ? ('<p>' . $text . '</p>') : '';
            // match E-mails
            $text = preg_replace('/([\w]+)@([\w]{2,})\.([\w\.]{2,})/i', "$1[at]$2[dot]$3", $text);
            // match URLs
            $text = preg_replace('/(https?:\/\/)?(([\w\-]+\.)*([a-z]{2,}\.)([a-z]{2,4})(\/[\w\.\-\?=\/&;,#!:]*)?)(\W)/i', "<a href=\"http://$2\" target=\"_blank\" rel=\"external nofollow\">$2</a>$7", $text);
        }
        return $text;
    }


    static function html2text($html)
    {
        $html = preg_replace('/[\s]+/', ' ', $html);
        $html = preg_replace('/\s+(<(p|h1|h2|h3|h4|h5|h6|table))/', "\n\n$1", $html);
        $html = preg_replace('/\s+(<(tr))/', "\n$1", $html);
        $html = preg_replace('/(<br>)/', "$1\n", $html);
        $text = strip_tags($html);
        $text = trim($text);
        $text = preg_replace('/[ ]*\n[ ]*/', "\n", $text);
        $text = preg_replace('/[\n]{3,}/', "\n\n", $text);
        return $text;
    }


    /**
     * Converts plain text or html to single line string.
     * @param  string  $text
     * @param  int     $len
     * @return string
     * @todo   perhaps better is to convert "..." to &hellip; or test it with Twig what's best
     */
    static public function line($str, $len = 150)
    {
        $str = strip_tags($str);
        $str = trim(preg_replace('/\s+/', ' ', $str));
        if ($len>0 and strlen($str)>$len) $str = preg_replace('/\W+$/', '', substr($str, 0, $len), 1) . '...';
        return $str;
    }


    static function paragraph($text)
    {
        $text = trim($text);
        $text = str_replace("\r\n", "\n", $text);
        $text = preg_replace('/[ ]*\n[ ]*/', "\n", $text);
        $text = explode("\n\n", $text);
        return $text[0];
    }


    static function str2byte($str)
    {
        if(is_numeric($str))
            return (int)$str;
        
        if(!preg_match('/^([0-9]+) ?([KMGTPEZY])?B?$/i', trim($str), $match))
            return 0;
        
        if(!empty($match[2]))
            return $match[1] * pow(1024, 1 + (int) stripos('KMGTPEZY', $match[2]));
        
        return (int)$match[1];
    }


    /**
     * Converts plural string to singular.
     * @param  string  $pl
     * @return string
     */
    static public function sg($pl)
    {
        if (substr($pl, -4)=='vies')
        {
            $sg = substr($pl, 0, -1);
        }
        elseif (substr($pl, -3)=='ies')
        {
            $sg = substr($pl, 0, -3) . 'y';
        }
        elseif (substr($pl, -2)=='xes')
        {
            $sg = substr($pl, 0, -2);
        }
        else
        {
            $sg = substr($pl, 0, -1);
        }
        return $sg;
    }


    /**
     * Converts singular string to plural.
     * @param  string  $sg
     * @return string
     */
    static public function pl($sg)
    {
        if (substr($sg, -1)=='y')
        {
            $pl = substr($sg, 0, -1) . 'ies';
        }
        elseif (substr($sg, -1)=='x')
        {
            $pl = $sg . 'es';
        }
        elseif (substr($sg, -2)=='en')
        {
            $pl = substr($sg, 0, -3);
        }
        else
        {
            $pl = $sg . 's';
        }
        return $pl;
    }


    /**
     * Converts class name to filename.
     * @param  string  $str
     * @return string
     */
    static public function classToFileName($str)
    {
        return (str_replace('__', DIRECTORY_SEPARATOR, preg_replace('/(.)([A-Z])/u', '$1_$2', $str)));
    }


    /**
     * Converts class name to table name.
     * @param  string  $str
     * @return string
     */
    static public function classToTableName($str)
    {
        return strtolower(self::classToFileName($str));
    }


    /**
     * Converts table name to class name.
     * @param  string  $str
     * @param  string  $table
     * @return string
     */
    static public function tableToClassName($str, $table='')
    {
        $str = str_replace('@', $table . ' ', $str);
        $str = str_replace(' ', '', ucwords(str_replace('_', ' ', $str)));
        return $str;
    }


    /**
     * Asciizes string.
     * @param  string  $str
     * @param  string  $sep
     * @param  string  $lower
     * @return string
     */
    static public function asciize($str, $sep = '-', $lower = true)
    {
        // asciize
        $str = preg_replace('/[àáâäæãåā]/u', 'a', $str);
        $str = preg_replace('/[ÀÁÂÄÆÃÅĀ]/u', 'A', $str);
        $str = preg_replace('/[çćč]/u',      'c', $str);
        $str = preg_replace('/[ÇĆČ]/u',      'C', $str);
        $str = preg_replace('/[ď]/u',        'd', $str);
        $str = preg_replace('/[Ď]/u',        'D', $str);
        $str = preg_replace('/[èéêëēėę]/u',  'e', $str);
        $str = preg_replace('/[ÈÉÊËĒĖĘ]/u',  'E', $str);
        $str = preg_replace('/[îïíīįì]/u',   'i', $str);
        $str = preg_replace('/[ÎÏÍĪĮÌ]/u',   'I', $str);
        $str = preg_replace('/[ľĺł]/u',      'l', $str);
        $str = preg_replace('/[ĽĹŁ]/u',      'L', $str);
        $str = preg_replace('/[ňñń]/u',      'n', $str);
        $str = preg_replace('/[ŇÑŃ]/u',      'N', $str);
        $str = preg_replace('/[ôöòóœøōõ]/u', 'o', $str);
        $str = preg_replace('/[ÔÖÒÓŒØŌÕ]/u', 'O', $str);
        $str = preg_replace('/[ŕř]/u',       'r', $str);
        $str = preg_replace('/[ŔŘ]/u',       'R', $str);
        $str = preg_replace('/[śšß]/u',      's', $str);
        $str = preg_replace('/[ŚŠ]/u',       'S', $str);
        $str = preg_replace('/[ť]/u',        't', $str);
        $str = preg_replace('/[Ť]/u',        'T', $str);
        $str = preg_replace('/[ůûüùúū]/u',   'u', $str);
        $str = preg_replace('/[ŮÛÜÙÚŪ]/u',   'U', $str);
        $str = preg_replace('/[ýÿ]/u',       'y', $str);
        $str = preg_replace('/[ÝŸ]/u',       'Y', $str);
        $str = preg_replace('/[žźż]/u',      'z', $str);
        $str = preg_replace('/[ŽŹŻ]/u',      'Z', $str);
        $str = preg_replace('/[\W_]+/', $sep, $str);
        // lowecase everything?
        if ($lower) $str = strtolower($str);
        // trim
        $str = trim($str, $sep);
        //
        return $str;
    }    
}
