<?php
// Enable error reporting for debugging purposes
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('memory_limit', '512M');
// Database connection
$conn = new mysqli('localhost', 'root', 'Root@123456', 'iCloudEMS');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch the next pending job
$job = $conn->query("SELECT * FROM csv_import_jobs WHERE status = 'pending' LIMIT 1")->fetch_assoc();

if ($job) {
    // Mark the job as processing
    $conn->query("UPDATE csv_import_jobs SET status = 'processing', updated_at = NOW() WHERE id = {$job['id']}");

    // Path to the CSV file
    $file = $job['file_name'];

    if (($handle = fopen($file, 'r')) !== FALSE) {
        fgetcsv($handle); // Skip the header row

        // Prepare batch insert for temp_table
        $stmt = $conn->prepare("
            INSERT INTO temp_table (sr, date, academic_year, session, alloted_category, voucher_type, voucher_no, roll_no, admno_uniqueid, status, fee_category, faculty, program, department, batch, receipt_no, fee_head, due_amount, paid_amount, concession_amount, scholarship_amount, reverse_concession_amount, write_off_amount, adjusted_amount, refund_amount, fund_transfer_amount, remarks)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $batchSize = 1000;
        $rows = [];

        while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
            $rows[] = $data;

            if (count($rows) === $batchSize) {
                // Insert batch
                foreach ($rows as $row) {
                    foreach ($row as $key => $value) {
                        $row[$key] = $value === '' ? NULL : $value;
                    }

                    $stmt->bind_param(
                        'issssssssssssssssddddddddds',
                        $row[0], $row[1], $row[2], $row[3], $row[4],
                        $row[5], $row[6], $row[7], $row[8], $row[9],
                        $row[10], $row[11], $row[12], $row[13], $row[14],
                        $row[15], $row[16], $row[17], $row[18], $row[19],
                        $row[20], $row[21], $row[22], $row[23], $row[24],
                        $row[25], $row[26]
                    );
                    $stmt->execute();
                }
                $rows = [];
            }
        }

        // Handle any remaining rows that didn't fill the batch size
        if (!empty($rows)) {
            foreach ($rows as $row) {
                foreach ($row as $key => $value) {
                    $row[$key] = $value === '' ? NULL : $value;
                }

                $stmt->bind_param(
                    'issssssssssssssssddddddddds',
                    $row[0], $row[1], $row[2], $row[3], $row[4],
                    $row[5], $row[6], $row[7], $row[8], $row[9],
                    $row[10], $row[11], $row[12], $row[13], $row[14],
                    $row[15], $row[16], $row[17], $row[18], $row[19],
                    $row[20], $row[21], $row[22], $row[23], $row[24],
                    $row[25], $row[26]
                );
                $stmt->execute();
            }
        }

        fclose($handle);

        // After importing, calculate the counts and sums
        $result = $conn->query("
            SELECT 
                COUNT(*) AS total_records,
                SUM(due_amount) AS total_due_amount,
                SUM(paid_amount) AS total_paid_amount,
                SUM(concession_amount) AS total_concession_amount,
                SUM(scholarship_amount) AS total_scholarship_amount,
                SUM(refund_amount) AS total_refund_amount
            FROM temp_table
        ");

        if ($result) {
            $totals = $result->fetch_assoc();

            // Insert the stats into the csv_import_stats table
            $insertStats = $conn->prepare("
                INSERT INTO csv_import_stats (job_id, total_records, total_due_amount, total_paid_amount, total_concession_amount, total_scholarship_amount, total_refund_amount)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $insertStats->bind_param(
                'iiddddd',
                $job['id'],                       // job_id
                $totals['total_records'],         // total_records
                $totals['total_due_amount'],      // total_due_amount
                $totals['total_paid_amount'],     // total_paid_amount
                $totals['total_concession_amount'],// total_concession_amount
                $totals['total_scholarship_amount'],// total_scholarship_amount
                $totals['total_refund_amount']    // total_refund_amount
            );
            $insertStats->execute();

            echo "Job {$job['id']} stats inserted: Total Records: " . $totals['total_records'] . "\n";
            echo "Total Due Amount: " . $totals['total_due_amount'] . "\n";
            echo "Total Paid Amount: " . $totals['total_paid_amount'] . "\n";
            echo "Total Concession: " . $totals['total_concession_amount'] . "\n";
            echo "Total Scholarship: " . $totals['total_scholarship_amount'] . "\n";
            echo "Total Refund: " . $totals['total_refund_amount'] . "\n";
        }

        // Mark the job as completed
        $conn->query("UPDATE csv_import_jobs SET status = 'completed', updated_at = NOW() WHERE id = {$job['id']}");

        // After importing, distribute the data into other tables
        // Distribute data to branches
        $conn->query("
            INSERT INTO branches (branch_name)
            SELECT DISTINCT faculty FROM temp_table WHERE faculty IS NOT NULL
        ");

        // Distribute data to feecategory
        $conn->query("
            INSERT INTO feecategory (fee_category)
            SELECT DISTINCT fee_category FROM temp_table WHERE fee_category IS NOT NULL
        ");

        // Distribute data to financialtrans
        $conn->query("
        INSERT INTO financialtrans (module_id, admno, due_amount, concession_amount, scholarship_amount, reverse_concession_amount, write_off_amount, branch_id, entry_mode, voucher_no, academic_year)
        SELECT 
            admno_uniqueid, 
            due_amount, 
            concession_amount, 
            scholarship_amount, 
            reverse_concession_amount, 
            write_off_amount, 
            1,
            (SELECT id FROM entrymode WHERE entry_modename = voucher_type LIMIT 1),
            voucher_no, 
            academic_year 
        FROM temp_table
        ");

        // Distribute data to financialtransdetail
        $conn->query("
            INSERT INTO financialtransdetail (financial_trans_id, head_id, amount, head_name, branch_id)
            SELECT t.id, 1, t.due_amount, t.fee_head, 1
            FROM financialtrans t
            JOIN temp_table tt ON t.voucher_no = tt.voucher_no
        ");

        // Distribute data to commonfeecollection
        $conn->query("
            INSERT INTO commonfeecollection (module_id, admno, paid_amount, adjusted_amount, refund_amount, fund_transfer_amount, branch_id, receipt_no)
            SELECT 1, admno_uniqueid, paid_amount, adjusted_amount, refund_amount, fund_transfer_amount, 1, receipt_no FROM temp_table
        ");

        // Distribute data to commonfeecollectionheadwise
        $conn->query("
            INSERT INTO commonfeecollectionheadwise (common_fee_collection_id, head_id, amount, head_name, branch_id)
            SELECT c.id, 1, c.paid_amount, c.fee_head, 1
            FROM commonfeecollection c
            JOIN temp_table tt ON c.receipt_no = tt.receipt_no
        ");

        echo "Job {$job['id']} completed and data distributed successfully.\n";

    } else {
        // If the file couldn't be opened, mark the job as failed
        $conn->query("UPDATE csv_import_jobs SET status = 'failed', updated_at = NOW() WHERE id = {$job['id']}");
        echo "Job {$job['id']} failed.\n";
    }
} else {
    echo "No pending jobs found.\n";
}

$conn->close();
