<?php

namespace ActionDispatch\Dispatcher\Route;

use ArrayObject;

/**
 * Route Generator
 *
 * Generates the route url from a given route object
 *
 * @author Benjamin J. Anderson <andeb2804@gmail.com>
 * @package ActionDispatch\Dispatcher\Route
 * @since Nov 4th, 2015
 * @version v0.1.0
 */
class RouteGenerator
{
  
  protected $route;
  protected $path;
  protected $data;
  protected $repl;
  protected $raw;
  
  public function generate( Route $route, $data = array() )
  {
    $this->raw = false;
    return $this->buildPath($route, $data);
  }
  
  
  public function generateRaw( Route $route, $data = array() )
  {
    $this->raw = true;
    return $this->buildPath($route, $data);
  }
  
  protected function buildPath( Route $route, $data = array() )
  {
    $this->route = $route;
    $this->data = $data;
    $this->path = $this->route->path;
    $this->repl = array();
    
    $this->buildData();
    $this->buildTokenReplacements();
    $this->buildOptionalReplacements();
    $this->path = strtr($this->path, $this->repl);
    $this->buildWildcardReplacement();
    
    return $this->path;
  }
  
  
  protected function buildData()
  {
    // the data for replacements
    $this->data = array_merge($this->route->values, $this->data);
    
    // use a callable to modify the data?
    if ($this->route->generate) 
    {
      // pass the data as an object, not as an array, so we can avoid
      // tricky hacks for references
      $arrobj = new ArrayObject($this->data);
      // modify
      call_user_func($this->route->generate, $arrobj);
      // convert back to array
      $this->data = $arrobj->getArrayCopy();
    }
  }
  
  protected function buildTokenReplacements()
  {
    foreach ($this->data as $key => $val) 
    {
      $this->repl["{{$key}}"] = $this->encode($val);
    }
  }
  
  
  protected function buildOptionalReplacements()
  {
    // replacements for optional params, if any
    preg_match('#{/([a-z][a-zA-Z0-9_,]*)}#', $this->path, $matches);
    if (! $matches) 
    {
      return;
    }
  
    // this is the full token to replace in the path
    $key = $matches[0];
    
    // start with an empty replacement
    $this->repl[$key] = '';
    
    // the optional param names in the token
    $names = explode(',', $matches[1]);
    
    // look for data for each of the param names
    foreach ($names as $name) 
    {
      // is there data for this optional param?
      if (! isset($this->data[$name])) 
      {
          // options are *sequentially* optional, so if one is
          // missing, we're done
          break;
      }
      // encode the optional value
      $this->repl[$key] .= '/' . $this->encode($this->data[$name]);
    }
  }
  
  protected function buildWildcardReplacement()
  {
    $wildcard = $this->route->wildcard;
    if ($wildcard && isset($this->data[$wildcard])) 
    {
      $this->path = rtrim($this->path, '/');
      foreach ($this->data[$wildcard] as $val) 
      {
        $this->path .= '/' . $this->encode($val);
      }
    }
  }
  
  protected function encode($val)
  {    
    if ($this->raw) {
      return $val;
    }   
    return is_scalar($val) ? rawurlencode($val) : null;
  }
}
