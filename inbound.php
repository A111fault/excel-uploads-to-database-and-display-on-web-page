<?php
session_start();

$con = mysqli_connect('localhost', 'root', '', 'tech360inventory');

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if (isset($_POST['save_excel_data'])) {
    $fileName = $_FILES['import_file']['name'];
    $file_ext = pathinfo($fileName, PATHINFO_EXTENSION);

    $allowed_ext = ['csv', 'xls', 'xlsx'];

    if (in_array($file_ext, $allowed_ext)) {
        $inputFileNamePath = $_FILES['import_file']['tmp_name'];

        // Load the file into a Spreadsheet object
        $spreadsheet = IOFactory::load($inputFileNamePath);
        $data = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

        // Skip the header row
        $headerSkipped = false;

        foreach ($data as $row) {
            // Skip the header row
            if (!$headerSkipped) {
                $headerSkipped = true;
                continue;
            }

            // Retrieve data from the row
            $SN = isset($row['A']) ? $row['A'] : '';
            $item_id = isset($row['B']) ? $row['B'] : '';
            $item_description = isset($row['C']) ? $row['C'] : '';
            $item_quantity = isset($row['D']) ? $row['D'] : '';
            $unit_price = isset($row['E']) ? $row['E'] : '';
            $date_received = isset($row['F']) ? $row['F'] : '';
            $supplier = isset($row['G']) ? $row['G'] : '';
            $total_price = isset($row['H']) ? $row['H'] : '';
            $remarks = isset($row['I']) ? $row['I'] : '';

            // SQL query to insert data
            $inboundQuery = "INSERT INTO inbound (SN,item_id, item_description, item_quantity, unit_price, date_received, supplier, total_price, remarks) VALUES 
            ('$SN',
            '$item_id',
            '$item_description',
            '$item_quantity',
            '$unit_price',
            '$date_received',
            '$supplier',
            '$total_price',
            '$remarks')";

            // Execute the query
            $result = mysqli_query($con, $inboundQuery);
            if ($result) {
                $msg = "Successfully imported";
            } else {
                $msg = "Error occurred while importing data: " . mysqli_error($con);
                break; // Exit the loop if an error occurs
            }
        }

        $_SESSION['message'] = $msg;
        header('location: inbound.php');
        exit(0);
    } else {
        $_SESSION['message'] = "Invalid file format. Please upload a CSV, XLS, or XLSX file.";
        header('location: inbound.php');
        exit(0);
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <title>Search Inbound Data</title>
    <style>
        body {
            background-color: #f8f9fa;
        }

        .container-box {
            border: 2px solid #007bff;
            padding: 20px;
            margin-top: 20px;
            border-radius: 10px;
            background-color: #fff;
        }

        .form-control-sm {
            border-radius: 5px;
        }

        .btn-dark {
            border-radius: 5px;
        }

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 10px;
            text-align: center;
        }

        th {
            background-color: #007bff;
            color: #fff;
        }

        tbody tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .btn-primary {
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="container-box">
                    <form class="my-2 mx-2" method="post">
                        <h2 class="mb-4">Search Inbound Details</h2>
                        <div class="row">
                            <div class="col-md-3">
                                <input type="text" class="form-control form-control-sm mb-3" name="item_id" placeholder="Item ID">
                            </div>
                            <div class="col-md-3">
                                <input type="text" class="form-control form-control-sm mb-3" name="item_description" placeholder="Item description">
                            </div>
                            <div class="col-md-3">
                                <input type="text" class="form-control form-control-sm mb-3" name="date_received" placeholder="Received Date">
                            </div>
                            <div class="col-md-3">
                                <input type="text" class="form-control form-control-sm mb-3" name="supplier" placeholder="Supplier name">
                            </div>
                        </div>
                        <button type="submit" name="submit" class="btn btn-dark">Search</button>
                    </form>
                    <div class="container my-2 mx-2 table-container">
                        <table class="table table-bordered border-primary">
                            <thead>
                                <tr>
                                    <th>SN</th>
                                    <th>Item Id</th>
                                    <th>Item Descrition</th>
                                    <th>Item Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Date Received</th>
                                    <th>Supplier</th>
                                    <th>Total Price</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (!isset($_POST['submit'])) {
                                    $sql = "SELECT * FROM `inbound` WHERE SN <= 50"; // Limit data to SN 50
                                    $result = mysqli_query($con, $sql);
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        echo '<tr>
                                                <td>' . $row['SN'] . '</td>
                                                <td>' . $row['item_id'] . '</td>
                                                <td>' . $row['item_description'] . '</td>
                                                <td>' . $row['item_quantity'] . '</td>
                                                <td>' . $row['unit_price'] . '</td>
                                                <td>' . $row['date_received'] . '</td>
                                                <td>' . $row['supplier'] . '</td>
                                                <td>' . $row['total_price'] . '</td>
                                                <td>' . $row['remarks'] . '</td>
                                              </tr>';
                                    }
                                } else {
                                    $item_id = isset($_POST['item_id']) ? $_POST['item_id'] : '';
                                    $item_description = isset($_POST['item_description']) ? $_POST['item_description'] : '';
                                    $date_received = isset($_POST['date_received']) ? $_POST['date_received'] : '';
                                    $supplier = isset($_POST['supplier']) ? $_POST['supplier'] : '';

                                    $sql = "SELECT * FROM `inbound` WHERE 1=1";

                                    if (!empty($item_id)) {
                                        $sql .= " AND item_id LIKE '%$item_id%'";
                                    }
                                    if (!empty($item_description)) {
                                        $sql .= " AND item_description LIKE '%$item_description%'";
                                    }
                                    if (!empty($date_received)) {
                                        $sql .= " AND date_received LIKE '%$date_received%'";
                                    }
                                    if (!empty($supplier)) {
                                        $sql .= " AND supplier LIKE '%$supplier%'";
                                    }

                                    $result = mysqli_query($con, $sql);
                                    if ($result) {
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            if ($row['SN'] <= 50) { // Only display rows with SN up to 80
                                                echo '<tr>
                                                    <td>' . $row['SN'] . '</td>
                                                    <td>' . $row['item_id'] . '</td>
                                                    <td>' . $row['item_description'] . '</td>
                                                    <td>' . $row['item_quantity'] . '</td>
                                                    <td>' . $row['unit_price'] . '</td>
                                                    <td>' . $row['date_received'] . '</td>
                                                    <td>' . $row['supplier'] . '</td>
                                                    <td>' . $row['total_price'] . '</td>
                                                    <td>' . $row['remarks'] . '</td>
                                                  </tr>';
                                            }
                                        }
                                    } else {
                                        echo '<tr><td colspan="8">Data not found</td></tr>';
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <form action="" method="POST" enctype="multipart/form-data">
                        <input type="file" name="import_file" class="form-control" />
                        <button type="submit" name="save_excel_data" class="btn btn-primary mt-3">Import</button>
                    </form>
                    <form method="post" action="">
                        <button type="submit" name="reset" class="btn btn-danger mt-3">Reset</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>