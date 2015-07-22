<?php

namespace Lemmon\Routing;

abstract class AbstractRouter implements RouterInterface
{
    private $_options = [
        'mod_rewrite' => FALSE,
    ];
    private $_scheme;
    private $_host;
    private $_root;
    private $_route = '';
    private $_routePrefix = '';
    private $_query;
    private $_params = [];
    private $_definedLinks = [];
    private $_definedClosures = [];


    public function __construct(array $options = NULL)
    {
        $this->_options = $o = array_merge($this->_options, $options ?: []);
        $this->_scheme = @$o['scheme'] ?: (@$_SERVER['REQUEST_SCHEME'] ?: (empty($_SERVER['HTTPS']) || 'off' === $_SERVER['HTTPS'] ? 'http' : 'https'));
        $this->_host = @$o['host'] ?: $_SERVER['HTTP_HOST'];
        $this->_root = (@$o['root']) ?: (rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/');
        $this->_routePrefix = @$o['route_prefix'] ?: ((FALSE === @$o['mod_rewrite']) ? basename($_SERVER['SCRIPT_NAME']) . '/' : '');
        $this->_route = @$o['route'] ?: trim(@$_SERVER['PATH_INFO'], '/');
        $this->_params = explode('/', $this->_route);
        parse_str($_SERVER['QUERY_STRING'], $this->_query);
    }


    public function offsetExists($offset)
    {
        return (is_numeric($offset) and $offset = intval($offset) - 1 and isset($this->_params[$offset])) ? true : false;
    }


    public function offsetGet($offset)
    {
        return (is_numeric($offset) and $offset = intval($offset) - 1 and isset($this->_params[$offset])) ? $this->_params[$offset] : null;
    }


    public function offsetSet($offset, $value) { return false; }
    public function offsetUnset($offset) { return false; }


    public function getParams()
    {
        return $this->_params;
    }


    public function getScheme()
    {
        return $this->_scheme;
    }


    public function getHost()
    {
        return $this->_host;
    }


    public function getRoot()
    {
        return $this->_root;
    }


    public function getRoute()
    {
        return $this->_route;
    }


    public function getHome()
    {
        return isset($this->_definedLinks[':home']) ? $this->to($this->_definedLinks[':home']) : ($this->getRoot() . ('index.php/' != $this->_routePrefix ? rtrim($this->_routePrefix, '/') : ''));
    }


    public function getSelf()
    {
        return $this->_root . ($this->_route ? $this->_routePrefix . $this->_route : '');
    }


    public function getQuery()
    {
        return $this->_query;
    }


    public function uri($link, $_ = NULL)
    {
        return new Uri($this, call_user_func_array([$this, 'to'], func_get_args()));
    }


    final public function define($key, $link = NULL)
    {
        /*
        if ($link) {
        */
        if (is_string($key)) {
            $this->_definedLinks[":$key"] = $link;
        } elseif ($key instanceof \Closure) {
            $this->_definedClosures[] = func_get_args();
        } else {
            throw new \Exception('Invalid definition');
        }
        return $this;
        /*
        } else {
            return @$this->_definedLinks[$key] ?: FALSE;
        }
        */
    }


    public function to($link, ...$args)
    {
        // uri
        if (is_object($link) and $link instanceof Uri) {
            return $link;
        }
        
        // validate link
        while (is_string($link) and ':' == $link{0}) {
            switch ($link) {
                case ':self': return $this->getSelf();
                case ':root': return $this->getRoot();
                case ':home': return $this->getHome();
                default:
                    if (isset($this->_definedLinks[$link])) {
                        $link = $this->_definedLinks[$link];
                    } else {
                        trigger_error(sprintf('Route not defined (%s)', $link));
                        return '#';
                    }
            }
        }
        
        if (is_callable($link)) {
            $link = $link($this, ...$args);
        } elseif (is_object($link)) {
            foreach ($this->_definedClosures as $_c) {
                if ($_res = $_c[0]($link)) {
                    return $this->to($_c[1], $link);
                }
            }
        }
        
        if (!is_string($link)) {
            return '#';
        }
        
        // chained arguments
        $link = preg_replace_callback('#%(?<from>\d+)(?<sep>.)?\.\.(%?(?<to>\d+))?#', function($m) use ($args){
            return '%' . join(@$m['sep'] . '%', range($m['from'], @$m['to'] ?: count($args)));
        }, $link);
        
        // match link variables with params
        $link = preg_replace_callback('#(?<keep>@)?({((?<match>[\w\.]+)|%(?<arg>\d+))(=(?<default>\w+))?}|%(?<arg0>\d+))#', function($m) use ($args){
            // argument
            $res = !empty($args) ? $args[(($i = (int)@$m['arg0'] or $i = (int)@$m['arg']) and isset($args[$i - 1])) ? $i - 1 : 0] : '';
            // match
            if (!empty($m['match'])) {
                $_res = $res;
                $_match = explode('.', $m['match']);
                foreach ($_match as $_m) {
                    if (is_array($_res) and isset($_res[$_m])) {
                        $_res = $_res[$_m];
                    } elseif (is_object($_res) and isset($_res->{$_m})) {
                        $_res = $_res->{$_m};
                    } elseif (is_object($_res) and method_exists($_res, 'get' . $_m)) {
                        $_res = $_res->{'get' . $_m}();
                    } else {
                        $_res = '';
                        break;
                    }
                }
                if (is_string($_res) or is_int($_res)) {
                    $res = $_res;
                } elseif (is_object($_res) and method_exists($_res, '__toString')) {
                    $res = $_res->__toString();
                } else {
                    $res = '';
                }
            }
            // res
            if (is_object($res) and method_exists($res, '__toString')) {
                $res = $res->__toString();
            }
            // default
            if (!empty($m['default']) and $m['default'] == $res) {
                $res = NULL;
            }
            //
            return ((is_string($res) or is_int($res)) and !empty($res)) ? $res : $m['keep'];
        }, $link);
        
        // paste current route params
        if (FALSE !== strpos($link, '@')) {
            $link = explode('/', $link);
            foreach ($link as $i => $_param) {
                if ($_param and '@' == $_param{0}) {
                    $link[$i] = isset($this->_params[$i]) ? $this->_params[$i] : '';
                }
            }
            $link = join('/', $link);
        }
        
        //
        
        if ('' == $link or ('/' !== $link{0} and FALSE === strpos($link, '://'))) {
            $link = $this->_root . $this->normalize($this->_routePrefix . rtrim($link, '/'));
        }
        
        return $link;
    }


    function normalize($uri)
    {
        $uri = '/' . $uri;
        $uri = preg_replace('#/[^/]+/([^/]+\.[^\.]+/)?\.\.#', '', $uri);
        $uri = preg_replace('#/([^/]+\.[^\.]+/)?\.#', '', $uri);
        return substr($uri, 1);
    }


    protected function matchPattern($pattern, $mask = [], $method = NULL, &$matches = [], &$defaults = [])
    {
        // match method
        if ($method and !($method & constant('self::METHOD_' . $_SERVER['REQUEST_METHOD']))) {
            return FALSE;
        }
        // match route
        $pattern = preg_replace('#\)(?!\!)#', ')?', $pattern);
        $pattern = str_replace(')!', ')', $pattern);
        $pattern = str_replace('.', '\.', $pattern);
        $pattern = str_replace('*', '.+', $pattern);
        $pattern = preg_replace_callback('#{(?<name>(\w+))(:(?<pattern>.+)(:(?<length>.+))?)?(=(?<default>.+))?}#U', function($m) use ($mask, &$defaults){
            if (@$m['pattern']) {
                switch ($m['pattern']) {
                    case 'num':      $_pattern = '\d'; break;
                    case 'alpha':    $_pattern = '[A-Za-z\-]'; break;
                    case 'alphanum': $_pattern = '[\w\-]'; break;
                    case 'word':     $_pattern = '[A-Za-z]([\w\-]+)?'; break;
                    case 'hex':      $_pattern = '[0-9A-Za-z]'; break;
                    default:         $_pattern = $m['pattern'];
                }
                $_pattern .= @$m['length'] ? "{{$m['length']}}" : '+';
            } elseif (array_key_exists($m['name'], $mask)) {
                $_pattern = $mask[$m['name']];
            } else {
                $_pattern = '[^/]+';
            }
            if (isset($m['default'])) {
                @$defaults[$m['name']] = $m['default'];
            }
            return "(?<{$m['name']}>{$_pattern})";
        }, $pattern);
        return preg_match("#^{$pattern}$#", $this->_route, $matches) ? TRUE : FALSE;
    }
}