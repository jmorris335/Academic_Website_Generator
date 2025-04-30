<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your-Name-Here: Publications</title>
    <link rel="stylesheet" href="./web/css/styles.css">
    <script src="./web/js/app.js"></script>
</head>
<body>   
    <h1>Publications</h1>

    <?php include "./web/php/bib_scraper.php"; printPublications("./web/assets/library.bib"); ?>

</body>
</html>