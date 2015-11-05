<?php

namespace ActionDispatch\Dispatcher\Route;

use ArrayObject;
use Closure;

/**
 * Route
 *
 * Parses the request and compares to a specified route
 *
 * @author Benjamin J. Anderson <andeb2804@gmail.com>
 * @package ActionDispatch\Dispatcher\Route
 * @since Nov 4th, 2015
 * @version v0.1.0
 */
class Route extends \ActionDispatch\Dispatcher\Spec\AbstractSpec
{
  const FAILED_ROUTABLE = 'FAILED_ROUTABLE';
  const FAILED_SECURE = 'FAILED_SECURE';
  const FAILED_REGEX = 'FAILED_REGEX';
  const FAILED_METHOD = 'FAILED_METHOD';
  const FAILED_ACCEPT = 'FAILED_ACCEPT';
  const FAILED_SERVER = 'FAILED_SERVER';
  const FAILED_CUSTOM = 'FAILED_CUSTOM';
  protected $name;
  protected $path;
  protected $params = array();
  protected $regex;
  protected $matches;
  protected $debug;
  protected $score = 0;
  protected $failed = null;
  
  public function __construct( \ActionDispatch\Dispatcher\Regex\Regex $regex, $path, $name = null )
  {
    $this->regex = $regex;
    $this->path = $path;
    $this->name = $name;
  }
  
  public function __get($key)
  {
    return $this->$key;
  }
  
  public function __isset($key)
  {
    return isset($this->$key);
  }
  
  public function isMatch( $path, array $server )
  {
    $this->debug = array();
    $this->params = array();
    $this->score = 0;
    $this->failed = null;
    
    if ($this->isFullMatch($path, $server)) 
    {
      $this->setParams();
      return true;
    }
    
    return false;
  }
  
  protected function isFullMatch( $path, array $server )
  {
    return $this->isRoutableMatch()
    && $this->isSecureMatch($server)
    && $this->isRegexMatch($path)
    && $this->isMethodMatch($server)
    && $this->isAcceptMatch($server)
    && $this->isServerMatch($server)
    && $this->isCustomMatch($server);
  }
  
  protected function pass()
  {
    $this->score ++;
    return true;
  }
  
  protected function fail( $failed, $append = null )
  {
    $this->debug[] = $failed . $append;
    $this->failed = $failed;
    return false;
  }
  
  public function failedAccept()
  {
    return $this->failed == self::FAILED_ACCEPT;
  }
  
  public function failedMethod()
  {
    return $this->failed == self::FAILED_METHOD;
  }
  
  protected function isRoutableMatch()
  {
    if ($this->routable) 
    {
      return $this->pass();
    }
  
    return $this->fail(self::FAILED_ROUTABLE);
  }
  
  protected function isSecureMatch($server)
  {
    if ($this->secure === null)
    {
      return $this->pass();
    }
  
    if ($this->secure != $this->serverIsSecure($server)) 
    {
      return $this->fail(self::FAILED_SECURE);
    }
    
    return $this->pass();
  }
  
  protected function serverIsSecure( $server )
  {
    return (isset($server['HTTPS']) && $server['HTTPS'] == 'on')
    || (isset($server['SERVER_PORT']) && $server['SERVER_PORT'] == 443);
  }
  
  protected function isRegexMatch( $path )
  {
    $regex = clone $this->regex;
    $match = $regex->match($this, $path);
    
    if (! $match) {
      return $this->fail(self::FAILED_REGEX);
    }
    
    $this->matches = new ArrayObject($regex->getMatches());
    return $this->pass();
  }
  
  protected function isMethodMatch( $server )
  {
    if (! $this->method) {
      return $this->pass();
      }
  
    $pass = isset($server['REQUEST_METHOD'])
    && in_array($server['REQUEST_METHOD'], $this->method);
    return $pass
    ? $this->pass()
    : $this->fail(self::FAILED_METHOD);
  }
  
  
  protected function isAcceptMatch( $server )
  {
    if (! $this->accept || ! isset($server['HTTP_ACCEPT'])) 
    {
      return $this->pass();
    }
  
    $header = str_replace(' ', '', $server['HTTP_ACCEPT']);
  
    if ($this->isAcceptMatchHeader('*/*', $header)) 
    {
      return $this->pass();
    }
  
    foreach ($this->accept as $type) 
    {
      if ($this->isAcceptMatchHeader($type, $header)) 
      {
        return $this->pass();
      }
    }
  
    return $this->fail(self::FAILED_ACCEPT);
  }
  
  protected function isAcceptMatchHeader( $type, $header )
  {
  
    list($type, $subtype) = explode('/', $type);
    
    $type = preg_quote($type);
    $subtype = preg_quote($subtype);
    $regex = "#$type/($subtype|\*)(;q=(\d\.\d))?#";
    $found = preg_match($regex, $header, $matches);
    
    if (! $found) 
    {
      return false;
    }
  
    return isset($matches[3]) && $matches[3] !== '0.0';
  
  }
  
  protected function isServerMatch( $server )
  {
    foreach ($this->server as $name => $regex) 
    {
      $matches = $this->isServerMatchRegex($server, $name, $regex);
      if (! $matches) 
      {
        return $this->fail(self::FAILED_SERVER, " ($name)");
      }
    
      $this->matches[$name] = $matches[$name];
    }
    
    return $this->pass();
  
  }
  
  protected function isServerMatchRegex( $server, $name, $regex )
  {
    $value = isset($server[$name])
    ? $server[$name]
    : '';
    
    $regex = "#(?P<{$name}>{$regex})#";
    preg_match($regex, $value, $matches);
    
    return $matches;
  }
  
  protected function isCustomMatch( $server )
  {
    if (! $this->is_match) 
    {
      return $this->pass();
    }
  
    // attempt the match
    $result = call_user_func($this->is_match, $server, $this->matches);
    
    // did it match?
    if (! $result) {
    return $this->fail(self::FAILED_CUSTOM);
    }
    
    return $this->pass();
  }
  
  protected function setParams()
  {
    $this->params = $this->values;
    $this->setParamsWithMatches();
    $this->setParamsWithWildcard();
  }
  
  protected function setParamsWithMatches()
  {
  
    // populate the path matches into the route values. if the path match
    // is exactly an empty string, treat it as missing/unset. (this is
    // to support optional ".format" param values.)
    foreach ($this->matches as $key => $val) 
    {
  
      if (is_string($key) && $val !== '') 
      {
        $this->params[$key] = rawurldecode($val);
      }
    }
  }
  
  protected function setParamsWithWildcard()
  {
    if (! $this->wildcard) 
    {
      return;
    }
  
    if (empty($this->params[$this->wildcard])) 
    {
      $this->params[$this->wildcard] = array();
      return;
    }
    
    $this->params[$this->wildcard] = array_map(
      'rawurldecode',
      explode('/', $this->params[$this->wildcard])
    );
  }
  
}