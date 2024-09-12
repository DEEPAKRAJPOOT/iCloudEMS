CREATE TABLE IF NOT EXISTS csv_import_jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    file_name VARCHAR(255) NOT NULL,        -- Name and path of the CSV file
    status ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',  -- Job status
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,    -- When the job was created
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP      -- When the job was last updated
);

-- 1. Temporary Table for CSV Import
CREATE TABLE IF NOT EXISTS temp_table (
    sr INT,
    date DATE,
    academic_year VARCHAR(255),
    session VARCHAR(255),
    alloted_category VARCHAR(255),
    voucher_type VARCHAR(255),
    voucher_no VARCHAR(255),
    roll_no VARCHAR(255),
    admno_uniqueid VARCHAR(255),
    status VARCHAR(255),
    fee_category VARCHAR(255),
    faculty VARCHAR(255),
    program VARCHAR(255),
    department VARCHAR(255),
    batch VARCHAR(255),
    receipt_no VARCHAR(255),
    fee_head VARCHAR(255),
    due_amount DECIMAL(15,2),
    paid_amount DECIMAL(15,2),
    concession_amount DECIMAL(15,2),
    scholarship_amount DECIMAL(15,2),
    reverse_concession_amount DECIMAL(15,2),
    write_off_amount DECIMAL(15,2),
    adjusted_amount DECIMAL(15,2),
    refund_amount DECIMAL(15,2),
    fund_transfer_amount DECIMAL(15,2),
    remarks VARCHAR(255)
);

-- 2. Branches Table
CREATE TABLE IF NOT EXISTS branches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    branch_name VARCHAR(255) NOT NULL
);

-- 3. FeeCategory Table
CREATE TABLE IF NOT EXISTS feecategory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fee_category VARCHAR(255) NOT NULL
);

-- 4. FeeCollectionTypes (Static) Table
CREATE TABLE IF NOT EXISTS feecollectiontypes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    collection_head VARCHAR(255) NOT NULL,
    collection_desc VARCHAR(255),
    br_id INT NOT NULL
);

-- 5. FeeTypes Table
CREATE TABLE IF NOT EXISTS feetypes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fee_category_id INT NOT NULL,
    f_name VARCHAR(255) NOT NULL,
    collection_id INT NOT NULL,
    br_id INT NOT NULL,
    seq_id INT NOT NULL,
    fee_type_ledger VARCHAR(255),
    fee_head_type INT NOT NULL
);

-- 6. EntryMode (Static) Table
CREATE TABLE IF NOT EXISTS entrymode (
    id INT AUTO_INCREMENT PRIMARY KEY,
    entry_modename VARCHAR(255) NOT NULL,
    crdr CHAR(1) NOT NULL,
    entrymodeno INT NOT NULL
);

-- 7. Module (Static) Table
CREATE TABLE IF NOT EXISTS module (
    id INT AUTO_INCREMENT PRIMARY KEY,
    module_name VARCHAR(255) NOT NULL,
    module_id INT NOT NULL
);

-- 8. FinancialTrans (Parent Table)
CREATE TABLE IF NOT EXISTS financialtrans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    module_id INT NOT NULL,
    admno VARCHAR(255) NOT NULL,
    due_amount DECIMAL(15,2),
    concession_amount DECIMAL(15,2),
    scholarship_amount DECIMAL(15,2),
    reverse_concession_amount DECIMAL(15,2),
    write_off_amount DECIMAL(15,2),
    branch_id INT NOT NULL,
    entry_mode VARCHAR(255) NOT NULL,
    voucher_no VARCHAR(255) NOT NULL,
    academic_year VARCHAR(255) NOT NULL
);

-- 9. FinancialTransDetail (Child Table)
CREATE TABLE IF NOT EXISTS financialtransdetail (
    id INT AUTO_INCREMENT PRIMARY KEY,
    financial_trans_id INT NOT NULL,
    head_id INT NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    head_name VARCHAR(255) NOT NULL,
    crdr CHAR(1) NOT NULL,
    branch_id INT NOT NULL
);

-- 10. CommonFeeCollection (Parent Table)
CREATE TABLE IF NOT EXISTS commonfeecollection (
    id INT AUTO_INCREMENT PRIMARY KEY,
    module_id INT NOT NULL,
    admno VARCHAR(255) NOT NULL,
    paid_amount DECIMAL(15,2),
    adjusted_amount DECIMAL(15,2),
    refund_amount DECIMAL(15,2),
    fund_transfer_amount DECIMAL(15,2),
    branch_id INT NOT NULL,
    receipt_no VARCHAR(255) NOT NULL
);

-- 11. CommonFeeCollectionHeadwise (Child Table)
CREATE TABLE IF NOT EXISTS commonfeecollectionheadwise (
    id INT AUTO_INCREMENT PRIMARY KEY,
    common_fee_collection_id INT NOT NULL,
    head_id INT NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    head_name VARCHAR(255) NOT NULL,
    branch_id INT NOT NULL
);

CREATE TABLE IF NOT EXISTS csv_import_stats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    job_id INT NOT NULL,                       -- References the job ID from csv_import_jobs
    total_records INT NOT NULL,                -- Total number of records
    total_due_amount DECIMAL(15,2),            -- Sum of due_amount
    total_paid_amount DECIMAL(15,2),           -- Sum of paid_amount
    total_concession_amount DECIMAL(15,2),     -- Sum of concession_amount
    total_scholarship_amount DECIMAL(15,2),    -- Sum of scholarship_amount
    total_refund_amount DECIMAL(15,2),         -- Sum of refund_amount
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP  -- Timestamp of the insertion
);

INSERT INTO entrymode (id, entry_modename, crdr, entrymodeno)
VALUES
    (1, 'Cash', 'C', 1),
    (2, 'Bank', 'C', 2),
    (3, 'Online', 'C', 3),
    (4, 'Cheque', 'D', 4),
    (5, 'NEFT', 'D', 5),
    (6, 'RTGS', 'C', 6);
    
INSERT INTO module (id, module_name, module_id)
VALUES
    (1, 'Ledger', 1),
    (2, 'Fees Collection', 2),
    (3, 'Payment Gateway', 3),
    (4, 'Reporting', 4);

