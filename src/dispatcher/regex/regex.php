<?php

namespace ActionDispatch\Dispatcher\Regex;

/**
 * Regex Parser
 *
 * Parses Regular Expressions
 *
 * @author Benjamin J. Anderson <andeb2804@gmail.com>
 * @package ActionDispatch\Dispatcher\Regex
 * @since Nov 4th, 2015
 * @version v0.1.0
 */
class Regex
{
  
  protected $route;
  protected $regex;
  protected $matches;
  
  public function match( $route, $path )
  {
    $this->route = $route;
    $this->regex = $this->route->path;
    $this->setRegexOptionalParams();
    $this->setRegexParams();
    $this->setRegexWildcard();
    $this->regex = '#^' . $this->regex . '$#';
    return preg_match($this->regex, $path, $this->matches);
  }
  
  public function getMatches()
  {
    return $this->matches;
  }
  
  protected function setRegexOptionalParams()
  {
    preg_match('#{/([a-z][a-zA-Z0-9_,]*)}#', $this->regex, $matches);
    
    if ($matches) 
    {
      $repl = $this->getRegexOptionalParamsReplacement($matches[1]);
      $this->regex = str_replace($matches[0], $repl, $this->regex);
    }
  }
  
  protected function getRegexOptionalParamsReplacement( $list )
  {
    $list = explode(',', $list);
    $head = $this->getRegexOptionalParamsReplacementHead($list);
    $tail = '';
    
    foreach ($list as $name) 
    {
      $head .= "(/{{$name}}";
      $tail .= ')?';
    }
    
    return $head . $tail;
  }
  
  protected function getRegexOptionalParamsReplacementHead( &$list )
  {
    $head = '';
    if (substr($this->regex, 0, 2) == '{/') 
    {
     $name = array_shift($list);
     $head = "/({{$name}})?";
    }
  
    return $head;
  }
  
  protected function setRegexParams()
  {
    $find = '#{([a-z][a-zA-Z0-9_]*)}#';
    preg_match_all($find, $this->regex, $matches, PREG_SET_ORDER);
  
    foreach ($matches as $match) 
    {
      $name = $match[1];
      $subpattern = $this->getSubpattern($name);
      $this->regex = str_replace("{{$name}}", $subpattern, $this->regex);
    
      if (! isset($this->route->values[$name])) 
      {
        $this->route->addValues(array($name => null));
      }
    }
  
  }
  
  protected function getSubpattern( $name )
  {
    if (isset($this->route->tokens[$name])) {
      return "(?P<{$name}>{$this->route->tokens[$name]})";
    }
    return "(?P<{$name}>[^/]+)";
  }
  
  protected function setRegexWildcard()
  {
    if (! $this->route->wildcard) 
    {
      return;
    }
    $this->regex = rtrim($this->regex, '/')
    . "(/(?P<{$this->route->wildcard}>.*))?";
  }
  
}