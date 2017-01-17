<?php

class SlightApp
{
  private $request;
  private $routes;
  private $config;

  public $twig;

  public function __construct($config = array("debug" => false, "templates" => __DIR__))
  {
    if($config["debug"] == true) {
      error_reporting(-1);
      ini_set('display_errors', 'On');
    }

    $this->routes   = array();
    $this->request  = array(
      "uri"     => $_SERVER["REQUEST_URI"],
      "method"  => $_SERVER["REQUEST_METHOD"]
    );

    //Support for json post data
    if(isset($_SERVER["CONTENT_TYPE"]) && strpos($_SERVER["CONTENT_TYPE"], "application/json") !== false) {
      $_POST = array_merge($_POST, (array) json_decode(trim(file_get_contents('php://input')), true));
    }

    //Parse post data in sepparate variables
  	if($this->request["method"] == "POST") {
  		$this->params = new stdClass();
  		foreach($_POST as $param_name => $param_value) {
        if(!empty($param_value)) {
		      $this->params->$param_name = $param_value;
        }
  		}
  	}

    $this->config = $config;

    $loader     = new Twig_Loader_Filesystem($config["templates"]);
    $this->twig = new Twig_Environment($loader, array("debug" => $config["debug"]));
  }

  public function get($pattern, $callback)
  {
    $this->routes[] = array(
      "method"    => "GET",
      "pattern"   => $pattern,
      "callback"  => $callback,
      "name"      => "",
      "middlewares" => array(
        "after"     => array(),
        "before"    => array()
      )
    );

    return $this;
  }

  public function post($pattern, $callback)
  {
    $this->routes[] = array(
      "method"    => "POST",
      "pattern"   => $pattern,
      "callback"  => $callback,
      "name"      => "",
      "middlewares" => array(
        "after"     => array(),
        "before"    => array()
      )
    );

    return $this;
  }

  public function after($middleware)
  {
    end($this->routes);
    $key = key($this->routes);
    $this->routes[$key]["middlewares"]["after"][] = $middleware;
    return $this;
  }

  public function before($middleware)
  {
    end($this->routes);
    $key = key($this->routes);
    $this->routes[$key]["middlewares"]["before"][] = $middleware;
    return $this;
  }

  public function exec($callback)
  {
    call_user_func($callback);
  }

  public function redirect($url)
  {
    header("Location: ".$url);
  }

  public function render($template, $vars = array())
  {
    return $this->twig->render($template, $vars);
  }

  public function response($code, $message)
  {
    header_remove();

    http_response_code($code);

    header('Status: '.$code);

    header($_SERVER['SERVER_PROTOCOL'] .' '. $code.' '.$message);
  }

  public function jsonResponse($code, $message)
  {
    header_remove();

    http_response_code($code);

    header('Status: '.$code);

    header('Content-Type: application/json');

    header("Cache-Control: no-transform,public,max-age=300,s-maxage=900");

    return json_encode($message);
  }

  public function singleton($obj, $callback)
  {
    $instance = call_user_func($callback);
    $this->$obj = $instance;
  }

  public function run()
  {
    foreach($this->routes as  $route) {
      $args = array();
      $pattern = "@^" . preg_replace('/\\\:[a-zA-Z0-9\_\-]+/', '([a-zA-Z0-9\-\_]+)', preg_quote($route["pattern"])) . "$@D";

      if($this->request["method"] == $route["method"] && preg_match($pattern, $this->request["uri"], $args)) {
        foreach($route["middlewares"]["after"] as $func) {
          echo call_user_func($func);
        }

        array_shift($args);
        echo call_user_func_array($route['callback'], $args);

        foreach($route["middlewares"]["before"] as $func) {
          echo call_user_func($func);
        }
      }
    }
  }
};
