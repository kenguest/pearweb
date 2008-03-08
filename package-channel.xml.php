<?php
require_once 'PEAR/PackageFileManager2.php';
PEAR::setErrorHandling(PEAR_ERROR_DIE);
$a = PEAR_PackageFileManager2::importOptions(dirname(__FILE__) . '/package-channel.xml',
    array(
        'baseinstalldir' => '/',
        'packagefile' => 'package-channel.xml',
        'filelistgenerator' => 'cvs',
        'roles' => array('*' => 'www'),
        'exceptions' => array('pearweb.php' => 'php'),
        'simpleoutput' => true,
        'include' => array(
            dirname(__FILE__) . '/public_html/channel.xml',
            'public_html/dtd/'
        ),
    ));
$a->setReleaseVersion('1.13.0');
$a->setReleaseStability('stable');
$a->setAPIStability('stable');
$a->setNotes('
- Remove the xmlrpc tags
- fix de mirror, make it use SSL to work
');
$a->resetUsesrole();
$a->clearDeps();
$a->setPhpDep('4.3.0');
$a->setPearInstallerDep('1.4.11');
$a->generateContents();
$a->writePackageFile();