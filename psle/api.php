<?php
// File: get_student_result.php
header("Content-Type: application/json; charset=UTF-8");
error_reporting(E_ALL);
ini_set('display_errors', 1);

// --------------------
// INPUT
// --------------------
$year   = $_GET['year']   ?? '';
$school = $_GET['school'] ?? '';
$index  = $_GET['index']  ?? '';

if (!$year || !$school || !$index) {
    echo json_encode([
        "error" => "Missing parameters. Please provide year, school number, and index."
    ]);
    exit;
}

// --------------------
// BUILD URL
// --------------------
$schoolLower = strtolower($school);

/*  Has beed deleted from necta
 * if ($year == 2025) {
    $url = "https://matokeo.necta.go.tz/results/{$year}/psle/results/shl_{$schoolLower}.htm";
}*/
if ($year <= 2021 && $year >= 2016) {
    $url = "https://maktaba.tetea.org/exam-results/PSLE{$year}/shl_{$schoolLower}.htm";
} else {
    $url = "https://onlinesys.necta.go.tz/results/{$year}/psle/results/shl_{$schoolLower}.htm";
}

// --------------------
// FETCH USING CURL
// --------------------
$ch = curl_init($url);

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT        => 20,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_SSL_VERIFYPEER => true,   // keep TRUE for production
    CURLOPT_SSL_VERIFYHOST => 2,
    CURLOPT_USERAGENT      => "Mozilla/5.0 (NECTA Result Fetcher)"
]);

$html = curl_exec($ch);

// ---- CURL ERROR HANDLING ----
if ($html === false) {
    echo json_encode([
        "error" => "Connection error while fetching results",
        "details" => curl_error($ch),
        "code" => curl_errno($ch)
    ]);
    curl_close($ch);
    exit;
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    echo json_encode([
        "error" => "Failed to fetch results page",
        "http_code" => $httpCode,
        "url" => $url
    ]);
    exit;
}

// --------------------
// PARSE HTML
// --------------------
libxml_use_internal_errors(true);
$dom = new DOMDocument();
$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
$xpath = new DOMXPath($dom);

// Target table
$rows = $xpath->query("//table[@bgcolor='LIGHTYELLOW']//tr");

$found = false;
$resultData = null;

foreach ($rows as $row) {
    $tds = $xpath->query("td", $row);

    if ($tds->length >= 4) {
        $candNo = trim($tds->item(0)->textContent);

        if (strcasecmp($candNo, "{$school}-{$index}") === 0) {

            // Column structure changes by year
            if ($year < 2022) {
                $premNo   = trim($tds->item(2)->textContent);
                $sex      = trim($tds->item(1)->textContent);
                $subjects = trim($tds->item(3)->textContent);
            } else {
                $premNo   = trim($tds->item(1)->textContent);
                $sex      = trim($tds->item(2)->textContent);
                $subjects = trim($tds->item(3)->textContent);
            }

            $resultData = [
                "year"         => $year,
                "school"       => $school,
                "index"        => $index,
                "candidate_no" => $candNo,
                "prem_no"      => $premNo,
                "sex"          => $sex,
                "subjects"     => $subjects
            ];

            $found = true;
            break;
        }
    }
}

// --------------------
// RESULT
// --------------------
if (!$found) {
    echo json_encode([
        "error" => "No result found",
        "details" => "Index {$index} not found at school {$school} for year {$year}"
    ]);
    exit;
}

echo json_encode($resultData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
