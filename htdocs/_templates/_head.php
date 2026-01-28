<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Selfmade Ninja Academy">
    <meta name="generator" content="Hugo 0.88.1">
    <title>Photogram | Share your story</title>

    <!-- Bootstrap core CSS -->
    <link href="<?=get_config('base_path')?>assets/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Google Fonts: Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link href="<?=get_config('base_path')?>css/app.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fingerprintjs/fingerprintjs@3/dist/fp.min.js"></script>
    <script>
    $(document).ready(function() {
        if (typeof FingerprintJS !== 'undefined') {
            FingerprintJS.load().then(fp => {
                fp.get().then(result => {
                    const visitorId = result.visitorId;
                    console.log("Fingerprint visitorId:", visitorId);
                    $('#fingerprint').val(visitorId);
                });
            });
        } else {
            // Fallback if CDN fails or blocked
            const fallbackId = "fallback-" + Math.random().toString(36).substring(7);
            $('#fingerprint').val(fallbackId);
        }
    });
    </script>
    <? 
    $css_file = dirname(__DIR__, 1) . '/css/' . basename($_SERVER['PHP_SELF'], ".php") . ".css";
    if (file_exists($css_file)) { ?>
        <link href="<?=get_config('base_path')?>css/<?= basename($_SERVER['PHP_SELF'], ".php") ?>.css" rel="stylesheet">
    <? } ?>

</head>