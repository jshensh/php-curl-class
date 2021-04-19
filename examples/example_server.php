<?php
    setcookie('server', 'test');
    if (isset($_COOKIE['b'])) {
        setcookie('c', 'b', time() + 5);
    }
    setcookie('b', 'b', time() + 5);
    $serv=$_SERVER;
    unset($serv["SCRIPT_FILENAME"]);
    unset($serv["CONTEXT_DOCUMENT_ROOT"]);
    unset($serv["SERVER_ADMIN"]);
    unset($serv["DOCUMENT_ROOT"]);
?><p>$_SERVER</p>
<pre><?php print_r($serv); ?></pre>
<p>$_POST</p>
<pre><?php print_r($_POST); ?></pre>
<p>$_GET</p>
<pre><?php print_r($_GET); ?></pre>
<p>$_FILES</p>
<pre><?php print_r($_FILES); ?></pre>
<p>$_COOKIE</p>
<pre><?php print_r($_COOKIE); ?></pre>
<p>php://input</p>
<pre><?php print_r(file_get_contents("php://input")); ?></pre>
