<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2001-2003 The PHP Group                                |
   +----------------------------------------------------------------------+
   | This source file is subject to version 2.02 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available at through the world-wide-web at                           |
   | http://www.php.net/license/2_02.txt.                                 |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Authors:                                                             |
   +----------------------------------------------------------------------+
   $Id$
*/

/**
 * Details about PEAR accounts
 */
require_once 'Damblan/URL.php';
$site = new Damblan_URL();

$params = array('handle' => '');
$site->getElements($params);

$handle = strtolower($params['handle']);

/*
 * Redirect to the accounts list if no handle was specified
 */
if (empty($handle)) {
    localRedirect('/accounts.php');
}

$dbh->setFetchmode(DB_FETCHMODE_ASSOC);

$row = user::info($handle);

if ($row === null) {
    handle_error($handle . ' is not a valid account name.', 'Invalid Account');
}

response_header('User Information: ' . $handle);

echo '<h1>User Information: ' . $handle . "</h1>\n";

?>

<table border="0" cellspacing="0" cellpadding="2" width="100%">
 <tr>
  <th class="headrow" colspan="2">&raquo; <?php echo $row['name']; ?></th>
 </tr>

<?php

if ($row['userinfo']) {
    echo ' <tr>' . "\n";
    echo '  <td class="textcell" colspan="2">' . $row['userinfo'] . "</td>\n";
    echo ' </tr>' . "\n";
}

?>

 <tr>
  <td colspan="2">
   <ul>

<?php

if ($row['showemail']) {
    $row['email'] = str_replace(array('@', '.'),
                                array(' at ', ' dot '),
                                $row['email']);
    echo '<li>Email: &nbsp;';
    print_link('/account-mail.php?handle=' . $handle, $row['email']);
    echo "</li>\n";
}

if ($row['homepage']) {
    echo '<li>Homepage: &nbsp;';
    print_link($row['homepage'], $row['homepage']);
    echo "</li>\n";
}

if ($row['wishlist']) {
    echo '<li>Wishlist: &nbsp;';
    if (strlen($row['wishlist']) > 60) {
        print_link($row['wishlist'], substr($row['wishlist'], 0, 60) . '...');
    } else {
        print_link($row['wishlist'], $row['wishlist']);
    }
    echo "</li>\n";
}

if ($row['pgpkeyid']) {
    echo '<li>PGP Key: &nbsp;';
    print_link('http://pgp.mit.edu:11371/pks/lookup?search=0x'
               . $row['pgpkeyid'] . '&amp;op=get',
               $row['pgpkeyid']);
    echo "</li>\n";
}

echo '<li>RSS Feed: &nbsp;';
print_link('http://' . $_SERVER['HTTP_HOST'] . '/feeds/user_' . $handle . '.rss');
echo '</li>';

?>

   </ul>
  </td>
 </tr>

 <tr>
  <th class="headrow" style="width: 50%">&raquo; Maintains These Packages</th>
  <th class="headrow" style="width: 50%">&raquo; Notes Regarding User</th>
 </tr>
 <tr>
  <td valign="top">
   <ul>

<?php

$query = 'SELECT p.id, p.name, m.role'
       . ' FROM packages p, maintains m'
       . ' WHERE m.handle = ? AND p.id = m.package'
       . ' ORDER BY p.name';

$maintained_pkg = $dbh->getAll($query, array($handle));

foreach ($maintained_pkg as $row) {
    echo '<li>';
    print_link('/package/' . $row['name'], $row['name']);
    echo ' &nbsp;(' . $row['role'] . ")</li>\n";
}

?>

   </ul>
  </td>
  <td valign="top">
   <ul>

<?php

$notes = $dbh->getAll('SELECT id, nby, ntime, note FROM notes'
                      . ' WHERE uid = ? ORDER BY ntime',
                      array($handle));

foreach ($notes as $nid => $data) {
    echo ' <li>' . "\n";
    echo '' . $data['nby'] . ' ';
    echo substr($data['ntime'], 0, 10) . ":<br />\n";
    echo htmlspecialchars($data['note']);
    echo "\n </li>\n";
}

?>

   </ul>
  </td>
 </tr>
</table>

<?php

response_footer();

?>
