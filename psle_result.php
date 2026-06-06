<?php
// File: get_school_results.php
header("Content-Type: text/html; charset=UTF-8");
error_reporting(E_ALL);

ini_set('display_errors', 1);

// Get year and school number from query
$year = $_GET['year'] ?? '';
$reg = strtolower($_GET['reg'] ?? '');
$class = strtolower($_GET['class'] ?? '');

if (empty($year) || empty($reg) || empty($class)) {
    echo "<p style='color:red;'>❌ Missing parameters. Use ?year=2024&reg=reg_ps06&class=sfna</p>";
    exit;
}


$url = "https://matokeo.necta.go.tz/results/{$year}/{$class}/results/{$reg}";
if ($year>=2022 && $year<=2025){
    $url="https://onlinesys.necta.go.tz/results/{$year}/{$class}/results/{$reg}";
}
header("Location: ".$url);
exit();
// Fetch HTML from NECTA
$html = @file_get_contents($url);

if ($html === false) {
    echo "<p style='color:red;'>⚠️ Unable to fetch results for year <b>$year</b>.</p>";
    exit;
}
echo $html;