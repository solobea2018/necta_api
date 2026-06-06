<?php
// File: get_school_results.php
header("Content-Type: text/html; charset=UTF-8");
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get year and school number from query
$year = $_GET['year'] ?? '';
$school = $_GET['school'] ?? '';

if (empty($year) || empty($school)) {
    echo "<p style='color:red;'>❌ Missing parameters. Use ?year=2024&school=PS0401058</p>";
    exit;
}

// Construct NECTA URL
$url = "https://onlinesys.necta.go.tz/results/{$year}/psle/results/shl_" . strtolower($school) . ".htm";
if ($year==2025){
    $url= "https://matokeo.necta.go.tz/results/{$year}/psle/results/shl_".strtolower($school).".htm";
}
if ($year<=2022 && $year>=2016){
    $url="https://maktaba.tetea.org/exam-results/PSLE".$year."/shl_".strtolower($school).".htm";
}

// Fetch HTML from NECTA
$html = @file_get_contents($url);

if ($html === false) {
    echo "<p style='color:red;'>⚠️ Unable to fetch results for school <b>$school</b> for year <b>$year</b>.</p>";
    exit;
}

// Clean and prepare HTML output
$html = str_replace('bgcolor="LIGHTYELLOW"', 'style="background:#fff5d7;border-radius:10px;padding:10px;"', $html);
$html = str_replace('<body', '<body style="font-family:Arial, sans-serif;background:#f9f9f9;"', $html);
$html = str_replace('<table', '<table style="border-collapse:collapse;width:100%;margin-bottom:20px;" border="1"', $html);
$html = str_replace('<td', '<td style="padding:5px;text-align:center;"', $html);
$html = str_replace('<th', '<th style="background:#ffc107;padding:6px;"', $html);

// Return the result HTML directly
echo $html;
?>
