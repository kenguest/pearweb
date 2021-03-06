--TEST--
user::info()
--FILE--
<?php
// setup
require dirname(dirname(__FILE__)) . '/setup.php.inc';
$mock->addDataQuery("SELECT * FROM users WHERE handle = 'dufuz' AND registered = '0'", array (
  0 => 
  array (
    'handle' => 'dufuz',
    'password' => 'as if!',
    'name' => 'Helgi Thormar',
    'email' => 'dufuz@php.net',
    'homepage' => 'http://www.helgi.ws',
    'created' => '2002-11-22 16:16:00',
    'createdby' => 'richard',
    'lastlogin' => NULL,
    'showemail' => '0',
    'registered' => '0',
    'admin' => '0',
    'userinfo' => '',
    'pgpkeyid' => '1F81E560',
    'pgpkey' => NULL,
    'wishlist' => NULL,
    'longitude' => '-96.6831931472',
    'latitude' => '40.7818087725',
    'active' => '1',
  ),
), array('handle', 'password', 'name', 'email', 'homepage', 'created',
    'createdby', 'lastlogin', 'showemail', 'registered', 'admin', 'userinfo',
    'pgpkeyid', 'pgpkey', 'wishlist', 'longitude', 'latitude', 'active'));

$mock->addDataQuery("SELECT email FROM users WHERE handle = 'dufuz' AND registered = '0'",
    array(array('email' => 'dufuz@php.net')),
    array('email')
);

$mock->addDataQuery("SELECT * FROM users WHERE handle = 'dufuz' AND registered = '1'",
    array(),
    array('handle', 'password', 'name', 'email', 'homepage', 'created',
    'createdby', 'lastlogin', 'showemail', 'registered', 'admin', 'userinfo',
    'pgpkeyid', 'pgpkey', 'wishlist', 'longitude', 'latitude', 'active')
);

// test
$user = user::info('dufuz', null, false);
$phpt->assertEquals(array (
    'handle' => 'dufuz',
    'name' => 'Helgi Thormar',
    'email' => 'dufuz@php.net',
    'homepage' => 'http://www.helgi.ws',
    'created' => '2002-11-22 16:16:00',
    'createdby' => 'richard',
    'lastlogin' => '',
    'showemail' => '0',
    'registered' => '0',
    'admin' => '0',
    'userinfo' => '',
    'pgpkeyid' => '1F81E560',
    'pgpkey' => '',
    'wishlist' => '',
    'longitude' => '-96.6831931472',
    'latitude' => '40.7818087725',
    'active' => '1',
  ), $user, 'test 1');

$user = user::info('dufuz', null, false, false);
$phpt->assertEquals(array (
    'handle' => 'dufuz',
    'password' => 'as if!',
    'name' => 'Helgi Thormar',
    'email' => 'dufuz@php.net',
    'homepage' => 'http://www.helgi.ws',
    'created' => '2002-11-22 16:16:00',
    'createdby' => 'richard',
    'lastlogin' => '',
    'showemail' => '0',
    'registered' => '0',
    'admin' => '0',
    'userinfo' => '',
    'pgpkeyid' => '1F81E560',
    'pgpkey' => '',
    'wishlist' => '',
    'longitude' => '-96.6831931472',
    'latitude' => '40.7818087725',
    'active' => '1',
  ), $user, 'test 2');

$user = user::info('dufuz', 'password', false);
$phpt->assertNull($user, 'password fetching');

$user = user::info('dufuz', 'email', false);
$phpt->assertEquals(array('email' => 'dufuz@php.net'), $user, 'field fetching');

$info = user::info('dufuz', null, true);
$phpt->assertNull($info, 'test 3');

?>
===DONE===
--CLEAN--
<?php
require dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
===DONE===