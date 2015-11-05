<?php

namespace ActionDispatch\Dispatcher\Factory;

/**
 * Route Factory
 *
 * Default route constructor
 *
 * @author Benjamin J. Anderson <andeb2804@gmail.com>
 * @package ActionDispatch\Dispatcher\Factory
 * @since Nov 4th, 2015
 * @version v0.1.0
 */
class RouteFactory
{
  
  protected $spec = array(
    'tokens' => array(),
    'server' => array(),
    'method' => array(),
    'accept' => array(),
    'values' => array(),
    'secure' => null,
    'wildcard' => null,
    'routable' => true,
    'is_match' => null,
    'generate' => null,
    'name_prefix' => null,
    'path_prefix' => null
  );
  
  protected $class = '\ActionDispatch\Dispatcher\Route\Route';
  protected $regex;
  
  public function __construct( $class='\ActionDispatch\Dispatcher\Route\Route', $regex=null )
  {
    $this->class = $class;
    $this->regex = is_null($regex) ? new \ActionDispatch\Dispatcher\Regex\Regex() : $regex;
  }
  
  public function newInstance( $path, $name = null, array $spec = array() )
  {
    $spec = array_merge($this->spec, $spec);
    $path = $spec['path_prefix'] . $path;
    
    $name = ($spec['name_prefix'] && $name) ? $spec['name_prefix'] . '.' . $name : $name;
    
    $class = $this->class;
    $route = new $class($this->regex, $path, $name);
    $route->addTokens($spec['tokens']);
    $route->addServer($spec['server']);
    $route->addMethod($spec['method']);
    $route->addAccept($spec['accept']);
    $route->addValues($spec['values']);
    $route->setSecure($spec['secure']);
    $route->setWildcard($spec['wildcard']);
    $route->setRoutable($spec['routable']);
    $route->setIsMatchCallable($spec['is_match']);
    $route->setGenerateCallable($spec['generate']);
    
    return $route;
  
  }
}