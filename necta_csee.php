<?php
if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {

    header('Content-Type: application/json');

    if (empty($_GET['index_no'])) {
        echo json_encode(['error' => 'Index number required']);
        exit;
    }

    $indexNo = trim($_GET['index_no']);

    // Validate index number (S5720/0066/2023 or Pxxxx/xxxx/yyyy)
    if (!preg_match('/^[SP]\d{4}\/\d{4}\/\d{4}$/i', $indexNo)) {
        echo json_encode(['error' => 'Invalid index number format']);
        exit;
    }

    $url = 'https://olas.heslb.go.tz/pgz/necta-pre-reg/get-f4-candidate-details/?index_no='. urlencode($indexNo);

    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_USERAGENT      => 'Mozilla/5.0'
    ]);

    $response = curl_exec($ch);

    if ($response === false) {
        echo json_encode([
            'error' => 'Connection error: ' . curl_error($ch)
        ]);
        curl_close($ch);
        exit;
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        echo json_encode(['error' => 'Server returned HTTP ' . $httpCode]);
        exit;
    }

    $data = json_decode($response, true);

    if (!$data || !isset($data['index_no'])) {
        echo json_encode(['error' => 'No record found']);
        exit;
    }

    echo json_encode([
        'index_no'    => $data['index_no'],
        'first_name'  => $data['first_name'],
        'middle_name' => $data['middle_name'],
        'last_name'   => $data['last_name'],
        'gender'      => $data['sex'],
        'division'    => $data['results_division'],
        'points'      => $data['results_points'],
        'centre_no'   => $data['centre']['centre_no'],
        'centre_name' => $data['centre']['centre_name']
    ]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CSEE Results Portal</title>

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f2f4f8;
        }

        .container {
            max-width: 1000px;
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

        header p {
            color: #555;
        }

        /* Year links */
        .years {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 25px;
        }

        .years a {
            background: #4b0082;
            color: #fff;
            padding: 10px 16px;
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
        }

        .years a:hover {
            background: #360061;
        }

        /* Search card */
        .card {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,.08);
        }

        .form-group {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .form-group input {
            flex: 1;
            padding: 12px;
            font-size: 15px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        .form-group button {
            padding: 12px 20px;
            background: #4b0082;
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        .form-group button:hover {
            background: #360061;
        }

        .loading {
            margin-top: 15px;
            color: #555;
        }

        .error {
            color: red;
            margin-top: 15px;
        }

        /* Result table */
        .result {
            margin-top: 20px;
        }

        .result table {
            width: 100%;
            border-collapse: collapse;
        }

        .result td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }

        .result td:first-child {
            font-weight: bold;
            width: 40%;
        }

        /* Responsive */
        @media (max-width: 600px) {
            .form-group {
                flex-direction: column;
            }

            .result td:first-child {
                width: auto;
            }
        }
    </style>
</head>

<body>
<div class="container">

    <header>
        <h2>CSEE Results Portal</h2>
        <p>Search Form Four candidate results</p>
    </header>

    <!-- YEAR LINKS -->
    <div class="years">
        <a href="csee.php?year=2022">CSEE 2022</a>
        <a href="csee.php?year=2023">CSEE 2023</a>
        <a href="csee.php?year=2024">CSEE 2024</a>
        <a href="csee.php?year=2025">CSEE 2025</a>
    </div>

    <!-- SEARCH CARD -->
    <div class="card">
        <div class="form-group">
            <input type="text" id="indexNo" placeholder="Enter Index Number (e.g. S5720/0066/2023)">
            <button onclick="searchResult()">Search</button>
        </div>

        <div class="loading" id="loading" style="display:none;">Fetching result…</div>
        <div class="error" id="error"></div>

        <div class="result" id="result"></div>
    </div>

</div>

<script>
    function searchResult() {
        var indexNo = document.getElementById("indexNo").value;
        var resultDiv = document.getElementById("result");
        var errorDiv  = document.getElementById("error");
        var loading   = document.getElementById("loading");

        resultDiv.innerHTML = "";
        errorDiv.innerHTML  = "";

        if (indexNo === "") {
            errorDiv.innerHTML = "Please enter index number";
            return;
        }

        loading.style.display = "block";

        var xhr = new XMLHttpRequest();
        xhr.open("GET", "?ajax=1&index_no=" + encodeURIComponent(indexNo), true);

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                loading.style.display = "none";

                if (xhr.status === 200) {
                    var res = JSON.parse(xhr.responseText);

                    if (res.error) {
                        errorDiv.innerHTML = res.error;
                        return;
                    }

                    resultDiv.innerHTML =
                        "<table>" +
                        "<tr><td>Index Number</td><td>" + res.index_no + "</td></tr>" +
                        "<tr><td>Name</td><td>" + res.first_name + " " + res.middle_name + " " + res.last_name + "</td></tr>" +
                        "<tr><td>Gender</td><td>" + res.gender + "</td></tr>" +
                        "<tr><td>Division</td><td>" + res.division + "</td></tr>" +
                        "<tr><td>Points</td><td>" + res.points + "</td></tr>" +
                        "<tr><td>Centre</td><td>" + res.centre_no + " - " + res.centre_name + "</td></tr>" +
                        "</table>";
                } else {
                    errorDiv.innerHTML = "Server error occurred";
                }
            }
        };

        xhr.send();
    }
</script>

</body>
</html>
