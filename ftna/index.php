<?php
// File: get_school_results.php
header("Content-Type: text/html; charset=UTF-8");
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get year and school number from query
$year = $_GET['year'] ?? '';
$class =  'ftna';
$centres = [];
if (!empty($year)) {
    // Construct NECTA URL
    $url = "https://matokeo.necta.go.tz/results/{$year}/{$class}/{$class}.htm";
    if ($year>=2022){
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



    foreach ($nodes as $node) {

        $text = trim($node->textContent);
        $href = $node->getAttribute("href");

        $schoolNo = null;

        // 1️⃣ Try extracting from visible text (S2523 Name)
        if (preg_match('/\b([PS]\d{4})\b/i', $text, $m)) {
            $schoolNo = strtoupper($m[1]);
        }

        // 2️⃣ If NOT found in text → extract from href (results/S0302.htm)
        if (!$schoolNo && preg_match('/\/([PS]\d{4})\.(htm|html)$/i', $href, $m)) {
            $schoolNo = strtoupper($m[1]);
        }

        // 3️⃣ Still nothing → skip (invalid or non-school link)
        if (!$schoolNo) {
            continue;
        }

        $centres[] = [
            'school' => $schoolNo,
            'name'   => $text ?: 'Unknown Centre',
            'link'   => '/ftna/result.php?' .
                'school=' . urlencode($schoolNo) .
                '&year=' . urlencode($year) .
                '&class=' . urlencode($class)
        ];
    }
}


$date=date('Y');
$c_year=intval($date);
$ly = $c_year - 1;
$my = $c_year - 2;
$fy = $c_year - 3;
$years=<<<years
<div class="years">
        <a href="/ftna/index.php?year={$fy}"> {$fy}</a>
        <a href="/ftna/index.php?year={$my}"> {$my}</a>
        <a href="/ftna/index.php?year={$ly}"> {$ly}</a>
        <a href="/ftna/index.php?year={$c_year}"> {$c_year}</a>
    </div>
years;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
<title>FTNA <?=$year?> Results – Centre Search</title>
<link rel="stylesheet" href="/css/style.css">
</head>

<body>
<div class="container">

<header>
    <h2>NATIONAL EXAMINATIONS COUNCIL OF TANZANIA</h2>
    <h3>CSEE <?=$year?> Examination Results – Centre Search</h3>
</header>

    <?=$years?>
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
