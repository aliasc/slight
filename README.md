# slight
Simple php routing class for quick bootstrapping.
Slight is just a small wrapper class which helps you easily separate logic from markup,
its minimal just a single class, and very fast. Slight uses Twig for templating which is awesome.

#Installation
Via composer
```php
composer require slight/slight
```

#Usage

First create an .htaccess file where your index.php is and add this
```php
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^ index.php [QSA,L]
```
###Instantiate slight
```php
<?php

//Require slight
require 'vendor/autoload.php';

//Instantiate slight
$app = new SlightApp();

//Setup routes
$app->get('/', function() {
  return 'Hi Home';
});

//Run slight
$app->run();
```
###Basic routing
```php
<?php

//Require slight
require 'vendor/autoload.php';

//Instantiate slight
$app = new SlightApp();

$app->get('/', function() {
  return 'Hi Home';
});

$app->get('/admin/', function() {
  return 'Hi Admin';
});

//Run slight
$app->run();
```
###Parameters
```php
<?php

//Require slight
require 'vendor/autoload.php';

//Instantiate slight
$app = new SlightApp();

$app->get('/admin/:username/', function($username) {
  return 'Hello '.$username;
});

$app->get('/admin/:firstname/:lastname/', function($firstname, $lastname) {
  return 'Welcome '.$firstname.' '.$lastname;
});

//Run slight
$app->run();
```

###Redirects
```php
<?php

//Require slight
require 'vendor/autoload.php';

//Instantiate slight
$app = new SlightApp();

$app->get('/admin', function() use ($app) {
  $app->redirect('/admin/login/');
});

$app->get('/admin/login', function() use ($app) {
  return 'Login page';
});

//Run slight
$app->run();
```

###Rendering output to file
All variables passed to the render function will be mapped
to a $display variable which can be used to output content.
```php
<?php

//Require slight
require 'vendor/autoload.php';

//Instantiate slight
$app = new SlightApp();

$app->get('/foo', function() {
  return $app->render('foo.php', array(
    'foo' => 'Bar'
  ));
});

$app->run();
```
####In foo.php
```php
<?php echo $display['foo']; ?>//Will output Bar
```

###Using Twig to render output
When you install slight you will notice that Twig is also installed as a dependency.
That is because Slight can also work closely with Twig.
```php
<?php

//Require slight
require "vendor/autoload.php";

//Configure the templates folder
$app = new SlightApp(array(
  'templates' => 'views'
));

$app->get('/', function() use ($app) {
  return $app->render('foo.html', array(
    'foo' => array(
      'name' => 'Bar',
      'username' => 'baz'
    )
  ));
});

//Run slight
$app->run();
```
####In foo.html now using Twig
```php
<p>{{ foo.name }} {{ foo.username }}</p>
```

###Using singleton to connect to a database
```php
<?php

//Require slight
require "vendor/autoload.php";

//Instantiate slight
$app = new SlightApp();

//Connect to mongodb using singleton
$app->singleton("database", function() use ($app) {
  //this will create $database property in slight
  //which will map to a testDatabase in MongoDB
  //this property can then be accessible using $app->database
  //Similar connections can be made to a MySQL database too.
  $connection = new MongoClient();
  return $connection->testDatabase;
});

//Home route
$app->get('/', function() use ($app) {
  //Find users from database, using property created from singleton.
  $users = $app->database->Users->find();
  //Render them
  return $app->render('users.html', array(
    "users" => $users
  ));
});

//Run slight
$app->run();
```
