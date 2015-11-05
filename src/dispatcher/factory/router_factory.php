<?php

namespace ActionDispatch\Dispatcher\Factory;

/**
 * Router Factory
 *
 * The default router constructor
 *
 * @author Benjamin J. Anderson <andeb2804@gmail.com>
 * @package ActionDispatch\Dispatcher\Factory
 * @since Nov 4th, 2015
 * @version v0.1.0
 */
class RouterFactory
{
  public function newInstance()
  {
    $router = new \ActionDispatch\Dispatcher\Route\Router(
      new \ActionDispatch\Dispatcher\Route\RouteCollection(
          new \ActionDispatch\Dispatcher\Factory\RouteFactory()
        ),
        new \ActionDispatch\Dispatcher\Route\RouteGenerator());
    return $router;
  }

}