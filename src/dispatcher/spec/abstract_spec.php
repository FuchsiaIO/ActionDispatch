<?php

namespace ActionDispatch\Dispatcher\Spec;

class AbstractSpec
{
  protected $tokens      = array();
  protected $server      = array();
  protected $method      = array();
  protected $accept      = array();
  protected $values      = array();
  protected $secure      = null;
  protected $wildcard    = null;
  protected $routable    = true;
  protected $is_match    = null;
  protected $generate    = null;  
    
  public function setTokens( array $tokens )
  {
    $this->tokens = $tokens;
    return $this;
  }
  
  public function addTokens( array $tokens )
  {
    $this->tokens = array_merge($this->tokens, $tokens);
    return $this;
  }
  
  public function setServer( array $server )
  {
    $this->server = $server;
    return $this;
  }
  
  public function addServer( array $server )
  {
    $this->server = array_merge($this->server, $server);
    return $this;
  }
  
  public function setMethod( $method )
  {
    $this->method = (array) $method;
    return $this;
  }
  
  public function addMethod( $method )
  {
    $this->method = array_merge($this->method, (array) $method);
    return $this;
  }
  
  public function setAccept( $accept )
  {
    $this->accept = (array) $accept;
    return $this;
  }
  
  public function addAccept( $accept )
  {
    $this->accept = array_merge($this->accept, (array) $accept);
    return $this;
  }
  
  public function setValues( array $values )
  {
    $this->values = $values;
    return $this;
  }
  
  public function addValues( array $values )
  {
    $this->values = array_merge($this->values, $values);
    return $this;
  }
  
  public function setSecure( $secure = true )
  {
    $this->secure = ($secure === null) ? null : (bool) $secure;
    return $this;
  }
  
  public function setWildcard( $wildcard )
  {
    $this->wildcard = $wildcard;
    return $this;
  }
  
  public function setRoutable( $routable = true )
  {
    $this->routable = (bool) $routable;
    return $this;
  }
  
  public function setIsMatchCallable($is_match)
  {
    $this->is_match = $is_match;
    return $this;
  }
  
  public function setGenerateCallable($generate)
  {
    $this->generate = $generate;
    return $this;
  }
    
}