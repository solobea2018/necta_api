<?php
// File: get_school_results.php
header("Content-Type: text/html; charset=UTF-8");
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get year and school number from query
$year = $_GET['year'] ?? '';
$class = $_GET['class'] ?? '';

if (empty($year) || empty($class)) {
    echo "<p style='color:red;'>❌ Missing parameters. Use ?year=2024&class=csee</p>";
    exit;
}

// Construct NECTA URL
$url = "https://matokeo.necta.go.tz/results/{$year}/{$class}/{$class}.htm";
if ($year>=2022 && $year<=2025){
    $url="https://onlinesys.necta.go.tz/results/{$year}/{$class}/{$class}.htm";
}

// Fetch HTML from NECTA
$html = @file_get_contents($url);

if ($html === false) {
    echo "<p style='color:red;'>⚠️ Unable to fetch results for year <b>$year</b>.</p>";
    exit;
}

// Load HTML safely
libxml_use_internal_errors(true);
$dom = new DOMDocument();
$dom->loadHTML($html);
$xpath = new DOMXPath($dom);

// Get all centre links
$nodes = $xpath->query("//a[contains(@href,'results/')]");

$centres = [];


foreach ($nodes as $node) {

    $text = trim($node->nodeValue);
    $href = $node->getAttribute('href');
    // Extract reg from href: reg_ps01.htm → PS01
    $reg=str_replace("results/","",$href);
    $centres[] = [
        'reg'  => $reg,
        'name' => $text,
        'link' => 'psle_result.php?reg=' . urlencode($reg)
            . '&year=' . urlencode($year)
            . '&class=' . urlencode($class)
    ];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PSLE 2025 Results – Centre Search</title>

    <style>
        body {
            font-family: "Segoe UI", Arial, sans-serif;
            background: #f4f6f8;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1100px;
            margin: auto;
            padding: 20px;
        }

        header {
            text-align: center;
            margin-bottom: 25px;
        }

        header h2 {
            color: #4b0082;
            margin-bottom: 5px;
        }

        header h3 {
            color: #333;
            font-weight: normal;
        }

        .search-box {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }

        .search-box input {
            width: 100%;
            max-width: 400px;
            padding: 12px 15px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 6px;
            outline: none;
        }

        .search-box input:focus {
            border-color: #4b0082;
        }

        .results {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 12px;
        }

        .result-item {
            background: #ffffff;
            padding: 12px 14px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,.08);
        }

        .result-item a {
            text-decoration: none;
            color: #003366;
            font-size: 14px;
            font-weight: 500;
        }

        .result-item a:hover {
            text-decoration: underline;
        }

        .no-results {
            display: none;
            text-align: center;
            color: #999;
            margin-top: 20px;
        }
    </style>
</head>

<body>
<div class="container">

    <header>
        <h2>NATIONAL EXAMINATIONS COUNCIL OF TANZANIA</h2>
        <h3>PSLE <?=$year?> Examination Results – Centre Search</h3>
    </header>

    <!-- SEARCH FORM -->
    <div class="search-box">
        <input type="text" id="searchInput"
               placeholder="Search by school name or centre number (e.g. P0101)">
    </div>

    <!-- RESULTS -->
    <div class="results" id="results">
        <?php foreach ($centres as $c): ?>
            <div class="result-item" data-name="<?= strtolower($c['name']) ?>">
                <a href="<?= htmlspecialchars($c['link']) ?>">
                    <?= htmlspecialchars($c['name']) ?>
                </a>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="no-results" id="noResults">
        No matching centre found
    </div>

</div>

<script>
    // Live search filter
    const input = document.getElementById("searchInput");
    const items = document.querySelectorAll(".result-item");
    const noResults = document.getElementById("noResults");

    input.addEventListener("keyup", function () {
        let value = this.value.toLowerCase();
        let visible = 0;

        items.forEach(item => {
            if (item.dataset.name.includes(value)) {
                item.style.display = "block";
                visible++;
            } else {
                item.style.display = "none";
            }
        });

        noResults.style.display = visible === 0 ? "block" : "none";
    });
</script>

</body>
</html>
