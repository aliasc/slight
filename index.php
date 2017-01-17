<?php

//Enable error reporting, comment on production
error_reporting(-1);
ini_set('display_errors', 'On');

//Include slight
require "src/slight.php";

//Instantiate slight
$app = new SlightApp();

//Home route
$app->get('/', function() use($app) {
  echo "Hi Home";
});

//User route with parameters
$app->get('/user:name', function($username) use ($app) {
  echo "Hi ".$username;
})->name("user_profile");

//Run slight
$app->run();
