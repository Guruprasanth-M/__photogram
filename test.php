<pre>
<?php
include 'libs/load.php';

$result = signup("test", "test", "test@test.test", "test");
if ($result === true) {
    echo "Success";
} else {
    echo "Fail";
    if (is_string($result) && strlen($result) > 0) {
        echo ": " . htmlspecialchars($result, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
?>
</pre>