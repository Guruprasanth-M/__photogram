<?php
include 'libs/load.php';
$conn = Database::getConnection();
$u = 'u_test2';
$q = "SELECT username,password FROM auth WHERE username='" . $conn->real_escape_string($u) . "'";
$res = $conn->query($q);
if ($res && $res->num_rows) {
    var_dump($res->fetch_assoc());
} else {
    var_dump('no row', $conn->error);
}

// show a few recent rows for inspection
$res2 = $conn->query("SELECT username,password, id FROM auth ORDER BY id DESC LIMIT 5");
if ($res2) {
    while ($r = $res2->fetch_assoc()) {
        var_dump($r);
    }
} else {
    var_dump('list error', $conn->error);
}
