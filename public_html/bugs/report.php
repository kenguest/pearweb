<?php /* vim: set noet ts=4 sw=4: : */
session_start();
/**
 * Procedures for reporting bugs
 *
 * See pearweb/sql/bugs.sql for the table layout.
 *
 * This source file is subject to version 3.0 of the PHP license,
 * that is bundled with this package in the file LICENSE, and is
 * available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.
 * If you did not receive a copy of the PHP license and are unable to
 * obtain it through the world-wide-web, please send a note to
 * license@php.net so we can mail you a copy immediately.
 *
 * @category  pearweb
 * @package   Bugs
 * @copyright Copyright (c) 1997-2005 The PHP Group
 * @license   http://www.php.net/license/3_0.txt  PHP License
 * @version   $Id$
 */

/**
 * Obtain common includes
 */
require_once './include/prepend.inc';

/**
 * Get user's CVS password
 */
require_once './include/cvs-auth.inc';

/**
 * Numeral Captcha Class
 */
require_once './include/NumeralCaptcha.php';

error_reporting(E_ALL ^ E_NOTICE);
$errors              = array();
$ok_to_submit_report = false;

/**
 * Instantiate the numeral captcha object.
 */
$numeralCaptcha = new NumeralCaptcha();

if (isset($_POST['save']) && isset($_POST['pw'])) {
    // non-developers don't have $user set
    setcookie('MAGIC_COOKIE', base64_encode(':' . $_POST['pw']),
              time() + 3600 * 24 * 12, '/', '.php.net');
}

if (isset($_POST['in'])) {
    if (isset($_POST['PEAR_PW'])) {
        $_POST['in']['PEAR_PW'] = $_POST['PEAR_PW'];
    }
    if (isset($_POST['PEAR_PW2'])) {
        $_POST['in']['PEAR_PW2'] = $_POST['PEAR_PW2'];
    }
    if (isset($_POST['PEAR_USER'])) {
        $_POST['in']['PEAR_USER'] = $_POST['PEAR_USER'];
    }
    $errors = incoming_details_are_valid($_POST['in'], 1, ($auth_user && $auth_user->registered));

    // captcha is not necessary if the user is logged in
    if ($auth_user && $auth_user->registered) {
        if (isset($_SESSION['answer'])) {
            unset($_SESSION['answer']);
        }
    }

    /**
     * Check if session answer is set, then compare
     * it with the post captcha value. If it's not
     * the same, then it's an incorrect password.
     */
    if (isset($_SESSION['answer']) && strlen(trim($_SESSION['answer'])) > 0) {
        if ($_POST['captcha'] != $_SESSION['answer']) {
            $errors[] = 'Incorrect Captcha';
        }
    }

    // try to verify the user
    if (!$auth_user) {
        if (!empty($_POST['isMD5'])) {
            $password = @$_POST['PEAR_PW'];
        } else {
            $password = md5(@$_POST['PEAR_PW']);
        }
        if (user::exists($_POST['in']['PEAR_USER'])) {
            if (auth_verify($_POST['in']['PEAR_USER'], $password)) {
                $POST['in']['handle'] = $_POST['in']['PEAR_USER'];
            } else {
                $errors[] = 'User name "' . clean($_POST['in']['PEAR_USER']) .
                    '" already exists, please choose another user name';
            }
        }
    } else {
        $_POST['in']['handle'] = $auth_user->handle;
    }

    if (!$errors) {

        /*
         * When user submits a report, do a search and display
         * the results before allowing them to continue.
         */
        if (!$_POST['in']['did_luser_search']) {

            $_POST['in']['did_luser_search'] = 1;

            // search for a match using keywords from the subject
            $sdesc = rinse($_POST['in']['sdesc']);

            /*
             * If they are filing a feature request,
             * only look for similar features
             */
            $package_name = $_POST['in']['package_name'];
            $where_clause = 'WHERE bugdb.package_name=p.name ';
            if ($package_name == 'Feature/Change Request') {
                $where_clause .= "AND package_name = '$package_name'";
            } else {
                $where_clause .= "AND package_name != 'Feature/Change Request'";
            }

            list($sql_search, $ignored) = format_search_string($sdesc);

            $where_clause .= $sql_search;

            $where_clause .= ' AND p.package_type="pear"';

            $query = "SELECT bugdb.* from bugdb, packages p $where_clause LIMIT 5";

            $res =& $dbh->query($query);

            if ($res->numRows() == 0) {
                $ok_to_submit_report = 1;
            } else {
                response_header("Report - Confirm");

                ?>

<p>
 Are you sure that you searched before you submitted your bug report? We
 found the following bugs that seem to be similar to yours; please check
 them before sumitting the report as they might contain the solution you
 are looking for.
</p>

<p>
 If you're sure that your report is a genuine bug that has not been reported
 before, you can scroll down and click the submit button to really enter the
 details into our database.
</p>

<div class="warnings">

<table class="lusersearch">
 <tr>
  <th>Description</th>
  <th>Possible Solution</th>
 </tr>

                <?php

                while ($row =& $res->fetchRow(DB_FETCHMODE_ASSOC)) {

                    $resolution =& $dbh->getOne('SELECT comment FROM' .
                            ' bugdb_comments where bug = ' .
                            $row['id'] . ' ORDER BY id DESC LIMIT 1');

                    if ($resolution) {
                        $resolution = htmlspecialchars($resolution);
                    }

                    $summary = $row['ldesc'];
                    if (strlen($summary) > 256) {
                        $summary = htmlspecialchars(substr(trim($summary),
                                                    0, 256)) . ' ...';
                    } else {
                        $summary = htmlspecialchars($summary);
                    }

                    $bug_url = "/bugs/bug.php?id=$row[id]&amp;edit=2";

                    echo " <tr>\n";
                    echo '  <td colspan="2"><strong>' . $row['package_name'] . '</strong> : <a href="' . $bug_url . '">Bug #';
                    echo $row['id'] . ': ' . htmlspecialchars($row['sdesc']);
                    echo "</a></td>\n";
                    echo " </tr>\n";
                    echo " <tr>\n";
                    echo '  <td>' . $summary . "</td>\n";
                    echo '  <td>' . nl2br($resolution) . "</td>\n";
                    echo " </tr>\n";

                }

                echo "</table>\n";
                echo "</div>\n";
            }
        } else {
            /*
             * We displayed the luser search and they said it really
             * was not already submitted, so let's allow them to submit.
             */
            $ok_to_submit_report = true;
        }

        do {
            if ($ok_to_submit_report) {
                if (!$auth_user) {
                    // user doesn't exist yet
                    require 'bugs/pear-bug-accountrequest.php';
                    $buggie = new PEAR_Bug_Accountrequest;
                    if (empty($_POST['isMD5'])) {
                        $_POST['PEAR_PW'] = md5($_POST['PEAR_PW']);
                        $_POST['PEAR_PW2'] = md5($_POST['PEAR_PW2']);
                    }
                    $salt = $buggie->addRequest($_POST['PEAR_USER'],
                          $_POST['in']['email'], $_POST['in']['reporter_name'],
                          $_POST['PEAR_PW'], $_POST['PEAR_PW2']);
                    if (is_array($salt)) {
                        $errors = $salt;
                        response_header('Report - Problems');
                        break; // skip bug addition
                    }
                    if (PEAR::isError($salt)) {
                        $errors[] = $salt;
                        response_header('Report - Problems');
                        break;
                    }
                    if ($salt === false) {
                        $errors[] = 'Your account cannot be added to the queue.'
                             . ' Please write a mail message to the '
                             . ' <i>pear-dev</i> mailing list.';
                        response_header('Report - Problems');
                        break;
                    }
        
                    $_POST['in']['handle'] = $_POST['PEAR_USER'];
                    $mailData = array(
                        'username'  => $_POST['in']['handle'],
                        'salt' => $salt,
                    );
        
                    if (!DEVBOX) {
                        require_once 'Damblan/Mailer.php';
                        $mailer = Damblan_Mailer::create('pearweb_account_request_bug', $mailData);
                        $additionalHeaders['To'] = $_POST['in']['email'];
                        $mailer->send($additionalHeaders);
                    }
                }
                // Put all text areas together.
                $fdesc = "Description:\n------------\n" . $_POST['in']['ldesc'] . "\n\n";
                if (!empty($_POST['in']['repcode'])) {
                    $fdesc .= "Test script:\n---------------\n";
                    $fdesc .= $_POST['in']['repcode'] . "\n\n";
                }
                if (!empty($_POST['in']['expres']) ||
                    $_POST['in']['expres'] === '0')
                {
                    $fdesc .= "Expected result:\n----------------\n";
                    $fdesc .= $_POST['in']['expres'] . "\n\n";
                }
                if (!empty($_POST['in']['actres']) ||
                    $_POST['in']['actres'] === '0')
                {
                    $fdesc .= "Actual result:\n--------------\n";
                    $fdesc .= $_POST['in']['actres'] . "\n";
                }
    
                $reporter_name = isset($_POST['in']['reporter_name']) ? htmlspecialchars(strip_tags($_POST['in']['reporter_name'])) : '';
    
                
                $query = 'INSERT INTO bugdb (
                          package_name,
                          bug_type,
                          email,
                          handle,
                          sdesc,
                          ldesc,
                          package_version,
                          php_version,
                          php_os,
                          status, ts1,
                          passwd,
                          reporter_name
                         ) VALUES (' .
                         " '" . escapeSQL($_POST['in']['package_name']) . "'," .
                         " '" . escapeSQL($_POST['in']['bug_type']) . "'," .
                         " '" . escapeSQL($_POST['in']['email']) . "'," .
                         " '" . escapeSQL($_POST['in']['handle']) . "'," .
                         " '" . escapeSQL($_POST['in']['sdesc']) . "'," .
                         " '" . escapeSQL($fdesc) . "'," .
                         " '" . escapeSQL($_POST['in']['package_version']) . "'," .
                         " '" . escapeSQL($_POST['in']['php_version']) . "'," .
                         " '" . escapeSQL($_POST['in']['php_os']) . "'," .
                         " 'Open', NOW(), " .
                         " '" . escapeSQL($_POST['in']['passwd']) . "'," .
                         " '" . escapeSQL($reporter_name) . "')";
    
    
                $dbh->query($query);
    
    /*
     * Need to move the insert ID determination to DB eventually...
     */
                if ($dbh->phptype == 'mysql') {
                    $cid = mysql_insert_id();
                } else {
                    $cid = mysqli_insert_id($dbh->connection);
                }
    
                $report  = '';
                $report .= 'From:             ' . $_POST['in']['handle'] . "\n";
                $report .= 'Operating system: ' . rinse($_POST['in']['php_os']) . "\n";
                $report .= 'Package version:  ' . rinse($_POST['in']['package_version']) . "\n";
                $report .= 'PHP version:      ' . rinse($_POST['in']['php_version']) . "\n";
                $report .= 'Package:          ' . $_POST['in']['package_name'] . "\n";
                $report .= 'Bug Type:         ' . $_POST['in']['bug_type'] . "\n";
                $report .= 'Bug description:  ';
    
                $fdesc = rinse($fdesc);
                $sdesc = rinse($_POST['in']['sdesc']);
    
                $ascii_report  = "$report$sdesc\n\n" . wordwrap($fdesc);
                $ascii_report .= "\n-- \nEdit bug report at ";
                $ascii_report .= "http://$site.php.net/bugs/bug.php?id=$cid&edit=";
    
                list($mailto, $mailfrom) = get_package_mail($_POST['in']['package_name']);
    
                $email = rinse($_POST['in']['email']);
                $protected_email  = '"' . spam_protect($email, 'text') . '"';
                $protected_email .= '<' . $mailfrom . '>';
    
                // provide shortcut URLS for "quick bug fixes"
                // $dev_extra = '';
                // $maxkeysize = 0;
                // foreach ($RESOLVE_REASONS as $v) {
                //     if (!$v['webonly']) {
                //         $actkeysize = strlen($v['desc']) + 1;
                //         $maxkeysize = (($maxkeysize < $actkeysize) ? $actkeysize : $maxkeysize);
                //     }
                // }
                // foreach ($RESOLVE_REASONS as $k => $v) {
                //     if (!$v['webonly'])
                //         $dev_extra .= str_pad($v['desc'] . ":", $maxkeysize) .
                //             " http://bugs.php.net/fix.php?id=$cid&r=$k\n";
                // }
    
                $extra_headers  = 'From: '           . $protected_email . "\n";
                $extra_headers .= 'X-PHP-BugTracker: PEARbug' . "\n";
                $extra_headers .= 'X-PHP-Bug: '      . $cid . "\n";
                $extra_headers .= 'X-PHP-Type: '     . rinse($_POST['in']['bug_type']) . "\n";
                $extra_headers .= 'X-PHP-PackageVersion: '  . rinse($_POST['in']['package_version']) . "\n";
                $extra_headers .= 'X-PHP-Version: '  . rinse($_POST['in']['php_version']) . "\n";
                $extra_headers .= 'X-PHP-Category: ' . rinse($_POST['in']['package_name']) . "\n";
                $extra_headers .= 'X-PHP-OS: '       . rinse($_POST['in']['php_os']) . "\n";
                $extra_headers .= 'X-PHP-Status: Open' . "\n";
                $extra_headers .= 'Message-ID: <bug-' . $cid . '@'.$site.'.php.net>';
    
                $type = @$types[$_POST['in']['bug_type']];
    
                if (DEVBOX == false) {
                    // mail to package developers
                    @mail($mailto, "[$siteBig-BUG] $type #$cid [NEW]: $sdesc",
                          $ascii_report . "1\n-- \n$dev_extra", $extra_headers,
                          '-f bounce-no-user@php.net');
                    // mail to reporter
                    @mail($email, "[$siteBig-BUG] $type #$cid: $sdesc",
                          $ascii_report . "2\n",
                          "From: $siteBig Bug Database <$mailfrom>\n" .
                          "X-PHP-Bug: $cid\n" .
                          "Message-ID: <bug-$cid@$site.php.net>",
                          '-f bounce-no-user@php.net');
                }
                localRedirect('bug.php?id=' . $cid . '&thanks=4');
                exit;
            }
        } while (false);
    } else {
        // had errors...
        response_header('Report - Problems');
    }

}  // end of if input


if (!package_exists($_REQUEST['package'])) {
    $errors[] = 'Package "' . clean($_REQUEST['package']) . '" does not exist.';
    response_header("Report - Invalid bug type");
    display_bug_error($errors);
} else {
    if (!isset($_POST['in'])) {
        response_header('Report - New');
        show_bugs_menu(clean($_REQUEST['package']));

        ?>

<p>
 Before you report a bug, make sure to search for similar bugs using the
 &quot;Bug List&quot; link. Also, read the instructions for
 <a target="top" href="http://bugs.php.net/how-to-report.php">how to report
 a bug that someone will want to help fix</a>.
</p>

<p>
 If you aren't sure that what you're about to report is a bug, you should
 ask for help using one of the means for support
 <a href="/support/">listed here</a>.
</p>

<p>
 <strong>Failure to follow these instructions may result in your bug
 simply being marked as &quot;bogus.&quot;</strong>
</p>

<p>
 <strong>If you feel this bug concerns a security issue, eg a buffer
 overflow, weak encryption, etc, then email
 <?php echo make_mailto_link('pear-group@php.net?subject=%5BSECURITY%5D+possible+new+bug%21', 'pear-group'); ?>
 who will assess the situation.</strong>
</p>

        <?php

    }

    display_bug_error($errors);

    ?>

<?php
$self = htmlspecialchars($_SERVER['PHP_SELF']);
$action = $self . '?package=' . clean($_REQUEST['package']);
if (!$auth_user && DEVBOX == false) {
    $action = "https://" . $_SERVER['SERVER_NAME'] . '/' . $action;
}
?>
<form<?php
if (!$auth_user) {
    echo ' onsubmit="javascript:doMD5(document.forms[\'bugreport\'])" ' ;
} ?> method="post"
 action="<?php echo $action ?>" name="bugreport" id="bugreport">
<?php if (!$auth_user):
    if (isset($_POST['PEAR_USER']) && isset($_POST['PEAR_PW']) && isset($_POST['PEAR_PW2'])):
        ?><input type="hidden" name="PEAR_USER" value="<?php echo htmlspecialchars($_POST['PEAR_USER']) ?>" />
        <input type="hidden" name="PEAR_PW" value="<?php echo htmlspecialchars($_POST['PEAR_PW']) ?>" />
        <input type="hidden" name="PEAR_PW2" value="<?php echo htmlspecialchars($_POST['PEAR_PW2']) ?>" />
        <input type="hidden" name="isMD5" value="<?php echo htmlspecialchars($_POST['isMD5']) ?>" />
        <?php
    else: //if (isset($_POST['PEAR_USER']) && isset($_POST['PEAR_PW']) && isset($_POST['PEAR_PW2'])) ?>
 <div class="explain">
 Please choose a username/password or <a href="<?php echo '/login.php?redirect=' .
        urlencode("{$self}?{$_SERVER['QUERY_STRING']}") ?>">Log in</a>
<script type="text/javascript" src="/javascript/md5.js"></script>
<script type="text/javascript">
function doMD5(frm) {
    frm.PEAR_PW.value = hex_md5(frm.PEAR_PW.value);
    frm.PEAR_PW2.value = hex_md5(frm.PEAR_PW2.value);
    frm.isMD5.value = 1;
}
</script>
<input type="hidden" name="isMD5" value="0" />
<table class="form-holder" cellspacing="1">
 <tr>
  <th class="form-label_left">
Use<span class="accesskey">r</span>name:</th>
  <td class="form-input">
<input size="20" name="PEAR_USER" accesskey="r"/></td>
 </tr>
 <tr>
  <th class="form-label_left">Password:</th>
  <td class="form-input">
<input size="20" name="PEAR_PW" type="password"/></td>
 </tr>
 <tr>
  <th class="form-label_left">Confirm Password:</th>
  <td class="form-input">
<input size="20" name="PEAR_PW2" type="password"/></td>
 </tr>
</table>
</div>
<?php
    endif; //if (isset($_POST['PEAR_USER']) && isset($_POST['PEAR_PW']) && isset($_POST['PEAR_PW2']))
endif; //if (!$auth_user) ?>
<table class="form-holder" cellspacing="1">
 <tr>
  <th class="form-label_left">
   Your <span class="accesskey">n</span>ame:
  </th>
  <td class="form-input">
<?php
    if ($auth_user && $auth_user->registered) {
?>
   <?php echo clean($auth_user->name); ?>
   <input type="hidden" size="20" maxlength="40" name="in[reporter_name]"
    value="<?php echo clean($auth_user->name); ?>" accesskey="n" />
<?php
    } else {
?>
   <input type="text" size="20" maxlength="40" name="in[reporter_name]"
    value="<?php echo clean($_POST['in']['reporter_name']); ?>" accesskey="n" />
<?php
   }
?>
  </td>
 </tr>

 <tr>
  <th class="form-label_left">
   Y<span class="accesskey">o</span>ur email address:
  </th>
  <td class="form-input">
   <input type="hidden" name="in[did_luser_search]"
    value="<?php echo $_POST['in']['did_luser_search'] ? 1 : 0; ?>" />
<?php
if ($auth_user && $auth_user->registered) {
?>
   <input type="text" size="20" maxlength="40" name="in[email]"
    value="<?php echo ($auth_user->showemail) ? $auth_user->email : ($auth_user->handle . '@php.net'); ?>" accesskey="o" />
<?php
} else {
?>
   <input type="text" size="20" maxlength="40" name="in[email]"
    value="<?php echo clean($_POST['in']['email']); ?>" accesskey="o" />
<?php
}
?>
  </td>
 </tr>
 <tr>
  <th class="form-label_left">
   PHP version:
  </th>
  <td class="form-input">
   <select name="in[php_version]">
    <?php show_version_options($_POST['in']['php_version']); ?>
   </select>
  </td>
 </tr>
 <?php if (!in_array(clean($_REQUEST['package']), $pseudo_pkgs, true)): ?>
 <tr>
  <th class="form-label_left">
   Package version:
  </th>
  <td class="form-input">
   <?php echo show_package_version_options(clean($_REQUEST['package']),
        clean($_POST['in']['package_version'])); ?>
  </td>
 </tr>
 <?php endif; ?>
 <tr>
  <th class="form-label_left">
   Package affected:
  </th>
  <td class="form-input">

    <?php

    if (!empty($_REQUEST['package'])) {
        echo '<input type="hidden" name="in[package_name]" value="';
        echo clean($_REQUEST['package']) . '" />' . clean($_REQUEST['package']);
        if ($_REQUEST['package'] == 'Bug System') {
            echo '<p><strong>WARNING: You are saying the <em>package';
            echo ' affected</em> is the &quot;Bug System.&quot; This';
            echo ' category is <em>only</em> for telling us about problems';
            echo ' that the '.$siteBig.' website\'s bug user interface is having. If';
            echo ' your bug is about a '.$siteBig.' package or other aspect of the';
            echo ' website, please hit the back button and actually read that';
            echo ' page so you can properly categorize your bug.</strong></p>';
        }
    } else {
        echo '<select name="in[package_name]">' . "\n";
        show_types(null, 0, clean($_REQUEST['package']));
        echo '</select>';
    }

    ?>

  </td>
 </tr>
 <tr>
  <th class="form-label_left">
   Bug Type:
  </th>
  <td class="form-input">
   <select name="in[bug_type]">
    <?php show_type_options($_POST['in']['bug_type']); ?>
   </select>
  </td>
 </tr>
 <tr>
  <th class="form-label_left">
   Operating system:
  </th>
  <td class="form-input">
   <input type="text" size="20" maxlength="32" name="in[php_os]"
    value="<?php echo clean($_POST['in']['php_os']); ?>" />
  </td>
 </tr>
 <?php if (!$auth_user): ?>
 <tr>
  <th>Solve the problem : <?php print $numeralCaptcha->getOperation(); ?> = ?</th>
  <td class="form-input"><input type="text" name="captcha" /></td>
 </tr>
 <?php $_SESSION['answer'] = $numeralCaptcha->getAnswer(); ?>
 <?php endif; // if (!$auth_user): ?>
 <tr>
  <th class="form-label_left">
   Summary:
  </th>
  <td class="form-input">
   <input type="text" size="40" maxlength="79" name="in[sdesc]"
    value="<?php echo clean($_POST['in']['sdesc']); ?>" />
  </td>
 </tr>
 <tr>
  <th class="form-label_left">
   Note:
  </th>
  <td class="form-input">
   Please supply any information that may be helpful in fixing the bug:
   <ul>
    <li>The version number of the <?php echo $siteBig; ?> package or files you are using.</li>
    <li>A short script that reproduces the problem.</li>
    <li>The list of modules you compiled PHP with (your configure line).</li>
    <li>Any other information unique or specific to your setup.</li>
    <li>
     Any changes made in your php.ini compared to php.ini-dist
     (<strong>not</strong> your whole php.ini!)
    </li>
    <li>
     A <a href="http://bugs.php.net/bugs-generating-backtrace.php">gdb
     backtrace</a>.
    </li>
   </ul>
  </td>
 </tr>
 <tr>
  <th class="form-label_left">
   Description:
   <p class="cell_note">
    Put patches and code samples in the
    &quot;Test script&quot; section, <strong>below</strong>.
   </p>
  </th>
  <td class="form-input">
   <textarea cols="60" rows="15" name="in[ldesc]"
    wrap="physical"><?php echo clean($_POST['in']['ldesc']); ?></textarea>
  </td>
 </tr>
 <tr>
  <th class="form-label_left">
   Test script:
   <p class="cell_note">
    A short test script you wrote that demonstrates the bug.
    Please <strong>do not</strong> post more than 20 lines of code.
    If the code is longer than 20 lines, provide a URL to the source
    code that will reproduce the bug.
   </p>
  </th>
  <td class="form-input">
   <textarea cols="60" rows="15" name="in[repcode]"
    wrap="no"><?php echo clean($_POST['in']['repcode']); ?></textarea>
  </td>
 </tr>
 <tr>
  <th class="form-label_left">
   Expected result:
   <p class="cell_note">
    What do you expect to happen or see when you run the test script above?
   </p>
  </th>
  <td class="form-input">
   <textarea cols="60" rows="15" name="in[expres]"
    wrap="physical"><?php echo clean($_POST['in']['expres']); ?></textarea>
  </td>
 </tr>
 <tr>
  <th class="form-label_left">
   Actual result:
   <p class="cell_note">
    This could be a
    <a href="http://bugs.php.net/bugs-generating-backtrace.php">backtrace</a>
    for example.
    Try to keep it as short as possible without leaving anything relevant out.
   </p>
  </th>
  <td class="form-input">
   <textarea cols="60" rows="15" name="in[actres]"
    wrap="physical"><?php echo clean($_POST['in']['actres']); ?></textarea>
  </td>
 </tr>
 <tr>
  <th class="form-label_left">
   Submit:
  </th>
  <td class="form-input">
   <input type="submit" value="Send bug report" />
  </td>
 </tr>
</table>
</form>

    <?php
}

response_footer();

?>
