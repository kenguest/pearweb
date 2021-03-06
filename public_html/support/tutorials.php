<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2004-2005 The PEAR Group                               |
   +----------------------------------------------------------------------+
   | This source file is subject to version 2.02 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available at through the world-wide-web at                           |
   | http://www.php.net/license/2_02.txt.                                 |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Authors: Martin Jansen <mj@php.net>                                  |
   +----------------------------------------------------------------------+
   $Id$
*/

response_header('Support - Tutorials');
?>
<p>The following is a list of pointers to external tutorials about
PEAR packages. In addition to this list one can find additional links
on <a href="http://www.phpkitchen.com/staticpages/index.php?page=2003041204203962">
PHPkitchen</a>.</p>

<ul class="spaced">
 <li>
  <a href="http://www.ulf-wendel.de/projekte/cache/">Tutorial
  about the PEAR Cache package</a>.
 </li>
 <li>
  <cite><a href="http://www.devshed.com/c/a/PHP/Serializing-XML-With-PHP/">Serializing
  XML With PHP</a></cite> covers the task of serializing XML documents with
  PHP with the help of the XML_Serializer package.
 </li>
 <li>
  <cite><a href="http://www.sitepoint.com/article/xml-php-pear-xml_serializer">Instant
XML with PHP and PEAR::XML_Serializer</a></cite> is another article about
  XML_Serializer.
 </li>

 <li>
  <cite><a href="http://www.zend.com/pear/tutorials/Mail.php">PEAR::Mail</a></cite>
  on zend.com.
 </li>
 <li>
  <cite>
   <a href="http://www.onlamp.com/pub/a/php/2001/10/11/pearcache.html">
   Caching PHP Programs with PEAR
   </a>
  </cite>
  on O&#39;Reilly Network
 </li>
    <li><a href="http://www.onlamp.com/pub/a/php/2001/07/19/pear.html">A Detailed Look at PEAR</a> on O&#39;Reilly Network</li>

    <li><a href="http://www.onlamp.com/pub/a/php/2001/05/24/pear.html">An Introduction to PEAR</a> on O&#39;Reilly Network</li>

    <li><a href="http://www.php.net/svn.php">SVN usage instructions</a></li>

    <li><a href="http://www.macdevcenter.com/pub/a/mac/2003/01/21/pear_macosx.html">O&#39;Reilly Network: PHP&#39;s PEAR on Mac OS X</a></li>
 <li>
  <cite>
   <a href="http://www.devshed.com/c/a/PHP/Configuration-Manipulation-With-PHP-Config/">
    Configuration Manipulation With PHP Config
   </a>
  </cite>
 </li>
 <li>
  <cite>
   <a href="http://www.samalyse.com/code/pear/dgdo/index.php">
    Database frontend with PEAR DataGrid and DataObject
   </a>
  </cite>
  discusses using <a href="/package/Structures_DataGrid">Structures_Datagrid</a>
  and <a href="/package/DB_DataObject">DB_DataObject</a>.  This tutorial is
  also <a href="http://www.samalyse.com/code/pear/dgdo/index.fr.php">available in
  French</a>.
 </li>
</ul>

<h3>DB Tutorials</h3>

<ul class="spaced">
 <li>
  <cite><a href="http://www.pearfr.org/index.php/en/article/db_pager">PEAR
  DB_Pager Package Tutorial</a></cite> by Tomas V.V.Cox and Arnaud Limbourg.
  This package handles all the stuff needed for displaying paginated
  results from an array or a database result.
 </li>
 <li>
  <a href="http://www.phpbuilder.com/columns/allan20010115.php3">PEAR DB
  Tutorial</a> on phpbuilder.com.
 </li>

 <li>
  <cite><a href="http://www.onlamp.com/pub/a/php/2001/11/29/peardb.html">PEAR::DB
  Primer</a></cite> on O&#39;Reilly Network.
 </li>
 <li>
  <cite><a href="http://www.nusphere.com/products/library/script_peardb.pdf">Writing
  Scripts with PHP&#39;s PEAR DB Class</a></cite> - by Paul DuBois (PDF) in nusphere.com.
 </li>
 <li>
  <cite><a href="http://evolt.org/article/Abstract_PHP_s_database_code_with_PEAR_DB/17/21927/index.html">Abstract
  PHP&#39;s database code with PEAR::DB</a></cite> on evolt.org.
 </li>

 <li>
  <cite><a href="http://www.devshed.com/c/a/PHP/Database-Abstraction-With-PHP/">Database
  Abstraction With PHP</a></cite> on devshed.com.
 </li>
 <li>
  <cite><a href="http://nyphp.org/content/presentations/db160/">PEAR DB:
  What&#39;s New in 1.6.0</a></cite> by Daniel Convissor.
 </li>
</ul>

<h3>German Tutorials</h3>
<ul class="spaced">
 <li>
  <cite><a href="http://www.heise.de/ix/artikel/2003/12/124/">XML Transformer
  kann XSLT ersetzen</a></cite>.
 </li>
 <li>
IT[X]: <a href="http://www.ulf-wendel.de/projekte/itx/index.php">http://www.ulf-wendel.de/projekte/itx/index.php</a>.
 </li>
 <li>
  <a href="http://www.ulf-wendel.de/projekte/menu/tutorial.php">Menu.php
  Tutorial</a>. This package generates a HTML menu from a multidimensional
  array.
 </li>
 <li>
  <cite>
   <a href="http://www.phpmag.de/itr/online_artikel/psecom,id,563,nodeid,62.html">
    Ein Colt f&uuml;r alle F&auml;lle
   </a>
  </cite>
  is an article from PHP Magazin about the
  <a href="/package/Config/">Config</a> package.
 </li>
</ul>

<hr />

<p>Do you know of other tutorials that should be added here?
Please let the <a href="mailto:<?php echo PEAR_WEBMASTER_EMAIL ?>">webmaster
team</a> know about them.</p>

<?php
response_footer();
