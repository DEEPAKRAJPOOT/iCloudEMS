<?php

/*

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_POST['upload'])) {
    // Database connection
    $conn = new mysqli('localhost', 'root', 'Root@123456', 'iCloudEMS');

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check if a file was uploaded
    if ($_FILES['file']['error'] == 0) {
        $file = $_FILES['file']['tmp_name'];

        // Open the file
        if (($handle = fopen($file, 'r')) !== FALSE) {
            // Skip the header row
            fgetcsv($handle);

            // Prepare the SQL statement
            $stmt = $conn->prepare("
                INSERT INTO temp_table (sr, date, academic_year, session, alloted_category, voucher_type, voucher_no, roll_no, admno_uniqueid, status, fee_category, faculty, program, department, batch, receipt_no, fee_head, due_amount, paid_amount, concession_amount, scholarship_amount, reverse_concession_amount, write_off_amount, adjusted_amount, refund_amount, fund_transfer_amount, remarks)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                // Replace empty strings with NULL for numeric fields
                foreach ($data as $key => $value) {
                    if ($value === '') {
                        $data[$key] = NULL;
                    }
                }

                // Bind parameters and handle NULLs correctly
                $stmt->bind_param(
                    'issssssssssssssssddddddddds',
                    $data[0], // sr (INT)
                    $data[1], // date (VARCHAR/DATE)
                    $data[2], // academic_year (VARCHAR)
                    $data[3], // session (VARCHAR)
                    $data[4], // alloted_category (VARCHAR)
                    $data[5], // voucher_type (VARCHAR)
                    $data[6], // voucher_no (VARCHAR)
                    $data[7], // roll_no (VARCHAR)
                    $data[8], // admno_uniqueid (VARCHAR)
                    $data[9], // status (VARCHAR)
                    $data[10], // fee_category (VARCHAR)
                    $data[11], // faculty (VARCHAR)
                    $data[12], // program (VARCHAR)
                    $data[13], // department (VARCHAR)
                    $data[14], // batch (VARCHAR)
                    $data[15], // receipt_no (VARCHAR)
                    $data[16], // fee_head (VARCHAR)
                    $data[17], // due_amount (DECIMAL)
                    $data[18], // paid_amount (DECIMAL)
                    $data[19], // concession_amount (DECIMAL)
                    $data[20], // scholarship_amount (DECIMAL)
                    $data[21], // reverse_concession_amount (DECIMAL)
                    $data[22], // write_off_amount (DECIMAL)
                    $data[23], // adjusted_amount (DECIMAL)
                    $data[24], // refund_amount (DECIMAL)
                    $data[25], // fund_transfer_amount (DECIMAL)
                    $data[26]  // remarks (VARCHAR)
                );

                if (!$stmt->execute()) {
                    echo "Error: " . $stmt->error . "\n";
                }
            }
            fclose($handle);
            echo "File uploaded and data imported successfully.";
        }
    } else {
        echo "Error uploading file.";
    }

    $stmt->close();
    $conn->close();
}*/



ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection
$conn = new mysqli('localhost', 'root', 'Root@123456', 'iCloudEMS');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Path to the CSV file in the MySQL secure file directory
$csvFilePath = '/var/lib/mysql-files/Bulk_Ledger_04_03_2021_17_13_28.csv';

// Load data from the CSV file into the table using LOAD DATA INFILE
$loadQuery = "
    LOAD DATA INFILE '" . $conn->real_escape_string($csvFilePath) . "' 
    INTO TABLE temp_table 
    FIELDS TERMINATED BY ',' 
    ENCLOSED BY '\"' 
    LINES TERMINATED BY '\n' 
    IGNORE 1 LINES
    (sr, date, academic_year, session, alloted_category, voucher_type, voucher_no, roll_no, admno_uniqueid, status, fee_category, faculty, program, department, batch, receipt_no, fee_head, due_amount, paid_amount, concession_amount, scholarship_amount, reverse_concession_amount, write_off_amount, adjusted_amount, refund_amount, fund_transfer_amount, remarks, @dummy)
";

if ($conn->query($loadQuery)) {
    echo "File imported successfully using LOAD DATA INFILE.";
} else {
    echo "Error: " . $conn->error;
}

// Close the connection
$conn->close();



?>
