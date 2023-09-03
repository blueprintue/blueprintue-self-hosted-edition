<?php

/* @noinspection PhpUnhandledExceptionInspection */
/* phpcs:disable Generic.Files.LineLength */

declare(strict_types=1);

use Rancoud\Security\Security;

/* @var $data array */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo Security::escHTML($this->title); ?></title>

    <meta name="robots" content="noindex">
    <meta content="<?php echo Security::escAttr($this->description); ?>" name="description">
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=5.0" name="viewport">

    <!--[if IE]>
    <meta HTTP-EQUIV="REFRESH" content="0; url=/ie.html">
    <![endif]-->

    <!-- favicons -->
    <link href="/apple-touch-icon.png" rel="apple-touch-icon" sizes="180x180">
    <link href="/favicon-32x32.png" rel="icon" sizes="32x32" type="image/png">
    <link href="/favicon-16x16.png" rel="icon" sizes="16x16" type="image/png">
    <link crossorigin="use-credentials" href="/site.webmanifest" rel="manifest">
    <meta content="#1a1c1f" name="msapplication-TileColor">
    <meta content="#ffffff" name="theme-color">

    <link href="<?php echo Security::escAttr($data['host']); ?>/bue-render/render.css" rel="stylesheet">
    <style>.hidden{display: none;}</style>
</head>
<body>
    <div class="playground"></div>
    <textarea class="hidden" id="pastebin_data"><?php echo Security::escHTML($data['content']); ?></textarea>
    <script src="<?php echo Security::escAttr($data['host']); ?>/bue-render/render.js"></script>
    <script>
        new window.blueprintUE.render.Main(
            document.getElementById('pastebin_data').value,
            document.getElementsByClassName('playground')[0],
            {height:"643px"}
        ).start();
    </script>
</body>
</html>