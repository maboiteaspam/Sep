<?
/* @var $exception \Exception */
$code = $exception->getCode();
$message = $exception->getMessage();
$file = $exception->getFile();
$line = $exception->getLine();
$previous = $exception->getPrevious();
$trace = str_replace(array('#', '\n'), array('<div>#', '</div>'), $exception->getTraceAsString());
$html = "";
$html .= '<p>The application could not run because of the following error:</p>';
$html .= '<h2>Details</h2>';
$html .= sprintf('<div><strong>Type:</strong> %s</div>', get_class($exception));

if( $exception instanceof \PDOException ){
    $html .= sprintf('<div><strong>SQL query:</strong> %s</div>', \ORM::get_last_query());
}
if ($code) {
    $html .= sprintf('<div><strong>Code:</strong> %s</div>', $code);
}
if ($message) {
    $html .= sprintf('<div><strong>Message:</strong> %s</div>', $message);
}
if ($file) {
    $html .= sprintf('<div><strong>File:</strong> %s</div>', $file);
}
if ($line) {
    $html .= sprintf('<div><strong>Line:</strong> %s</div>', $line);
}
if ($trace) {
    $html .= '<h2>Trace</h2>';
    $html .= sprintf('<pre>%s</pre>', $trace);
}
if( $previous ){
    $code = $previous->getCode();
    $message = $previous->getMessage();
    $file = $previous->getFile();
    $line = $previous->getLine();

    $html .= '<h2>Previous exception</h2>';
    $html .= sprintf('<div><strong>Type:</strong> %s</div>', get_class($previous));

    if( $previous instanceof \PDOException ){
        $html .= sprintf('<div><strong>SQL query:</strong> %s</div>', \ORM::get_last_query());
    }
    if ($code) {
        $html .= sprintf('<div><strong>Code:</strong> %s</div>', $code);
    }
    if ($message) {
        $html .= sprintf('<div><strong>Message:</strong> %s</div>', $message);
    }
    if ($file) {
        $html .= sprintf('<div><strong>File:</strong> %s</div>', $file);
    }
    if ($line) {
        $html .= sprintf('<div><strong>Line:</strong> %s</div>', $line);
    }
    if ($trace) {
        $html .= '<h2>Trace</h2>';
        $html .= sprintf('<pre>%s</pre>', $trace);
    }
}
?><html>
<head>
    <title><?= $title ?></title>
    <style>
        body {
            margin: 0;
            padding: 30px;
            font: 12px/1.5 Helvetica, Arial, Verdana, sans-serif;
        }

        h1 {
            margin: 0;
            font-size: 48px;
            font-weight: normal;
            line-height: 48px;
        }

        strong {
            display: inline-block;
            width: 65px;
        }
    </style>
</head>
<body>
<h1><?= $title ?></h1>
<?= $html ?>
</body>
</html>

