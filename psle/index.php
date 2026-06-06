<?php
// index.php — PSLE Results API Documentation
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PSLE Results API Documentation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f7f9fc;
            color: #333;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }
        header {
            background: #004aad;
            color: white;
            padding: 20px;
            text-align: center;
        }
        h1 {
            margin: 0;
            font-size: 26px;
        }
        main {
            max-width: 900px;
            margin: 30px auto;
            background: #fff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        code {
            background: #f1f1f1;
            padding: 4px 8px;
            border-radius: 5px;
            color: #c7254e;
        }
        pre {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 10px;
            overflow-x: auto;
        }
        .example {
            background: #eef9ff;
            border-left: 4px solid #004aad;
            padding: 15px;
            margin: 20px 0;
            border-radius: 6px;
        }
        footer {
            text-align: center;
            font-size: 14px;
            color: #666;
            padding: 20px;
            margin-top: 40px;
        }
        a {
            color: #004aad;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<header>
    <h1>🧾 PSLE Results API Documentation</h1>
    <p>Hosted at <strong>api.shule.store</strong></p>
</header>

<main>
    <form method="get" action="/psle/api.php" target="_blank">
        <label>Year: <input type="text" name="year" value="2024" required></label>
        <label>School: <input type="text" name="school" value="PS0401058" required></label>
        <label>Index: <input type="text" name="index" value="0001" required></label>
        <button type="submit">Get Result (JSON)</button>
    </form>

    <form method="get" action="/psle/school.php" target="_blank" style="margin-top:10px;">
        <label>Year: <input type="text" name="year" value="2024" required></label>
        <label>School: <input type="text" name="school" value="PS0401058" required></label>
        <button type="submit">View Results (HTML)</button>
    </form>
</main>

<footer>
    © <?= date('Y'); ?> PSLE Results API | Developed by <a href="https://solobea.com" target="_blank">Mb-Techs</a>
</footer>

</body>
</html>
