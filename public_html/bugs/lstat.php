<?php

    require_once 'pear-database.php';
    $res = $dbh->getOne('SELECT count(bugdb.id) AS count FROM bugdb
                        LEFT JOIN packages ON packages.name = bugdb.package_name
                        WHERE bugdb.status IN ("Open", "Assigned", 
                                                "Analyzed", "Critical", "Verified")
                        AND bugdb.bug_type = "Bug" AND packages.package_type = "pear"');
    if (DB::isError($res)) {
        die;
    }
    echo $res;

?>
