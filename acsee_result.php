<?php
// File: get_school_results.php
header("Content-Type: text/html; charset=UTF-8");
error_reporting(E_ALL);

ini_set('display_errors', 1);

// Get year and school number from query
$year = isset($_GET['year']) ? $_GET['year'] : '';
$schoolNo = strtolower($_GET['school'] ?? '');

if (empty($year) || empty($schoolNo)) {
    echo "<p style='color:red;'>❌ Missing parameters. Use ?year=2024&school=PS0401058&class=acsee</p>";
    exit;
}


// Construct NECTA URL
$url = "https://matokeo.necta.go.tz/results/{$year}/acsee/results/{$schoolNo}.htm";
if ($year>=2023 && $year<=2025){
    $url="https://onlinesys.necta.go.tz/results/{$year}/acsee/results/{$schoolNo}.htm";
}

// Fetch HTML from NECTA
$html = @file_get_contents($url);

if ($html === false) {
    echo "<p style='color:red;'>⚠️ Unable to fetch results for year <b>$year</b>.</p>";
    exit;
}
echo $html;