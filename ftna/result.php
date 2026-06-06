<?php
// File: get_school_results.php
header("Content-Type: text/html; charset=UTF-8");
error_reporting(E_ALL);
ini_set('display_errors', 1);

// -------------------- INPUT --------------------
$year     = $_GET['year']   ?? '';
$schoolNo = strtolower($_GET['school'] ?? '');
$class    = strtolower($_GET['class'] ?? '');

if (!$year || !$schoolNo || !$class) {
    echo "<p style='color:red;'>❌ Missing parameters. Use ?year=2024&school=PS0401058&class=csee</p>";
    exit;
}

// -------------------- BUILD URL --------------------
if ($class === 'ftna') {
    $url = "https://matokeo.necta.go.tz/results/$year/$class/results/$schoolNo.htm";

    if ($year >= 2022 && $year <= 2025) {
        $schoolNo=strtoupper($schoolNo);
        $url = "https://onlinesys.necta.go.tz/results/$year/$class/results/$schoolNo.htm";
    }
} else {
    $url = "https://matokeo.necta.go.tz/results/$year/$class/results/$schoolNo.htm";

    if ($year >= 2022 && $year <= 2024) {
        $url = "https://onlinesys.necta.go.tz/results/$year/$class/results/$schoolNo.htm";
    }
}

// -------------------- FETCH VIA CURL --------------------
$ch = curl_init($url);

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT        => 20,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_USERAGENT      => "Mozilla/5.0 (NECTA Results Fetcher)",
    CURLOPT_SSL_VERIFYPEER => true,   // server environments OK
    CURLOPT_SSL_VERIFYHOST => 2,
]);

$html = curl_exec($ch);

// -------------------- CURL ERRORS --------------------
if ($html === false) {
    $err = curl_error($ch);
    curl_close($ch);

    echo "<p style='color:red;'>⚠️ Connection error while fetching results.</p>";
    echo "<small>" . htmlspecialchars($err) . "</small>";
    exit;
}

// -------------------- HTTP STATUS CHECK --------------------
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    echo "<p style='color:red;'>⚠️ NECTA server returned HTTP $httpCode.</p>";
    exit;
}

// -------------------- OUTPUT HTML --------------------
echo $html;
