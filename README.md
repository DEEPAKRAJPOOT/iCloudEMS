# iCloudEMS - CSV Import and Data Distribution

This project handles CSV import and data distribution for the iCloudEMS system. The system imports large amounts of data, processes it in the background, and distributes it into the appropriate database tables.

## Features

- **CSV Import**: Upload large CSV files for processing.
- **Job Queue**: Uses a job queue to handle CSV imports in the background.
- **Data Distribution**: Distributes the imported data to various database tables like `branches`, `financialtrans`, `feecategory`, etc.
- **Summing and Counting**: After import, the system calculates the total number of records and sums of different financial columns (due amount, paid amount, etc.).

## Installation

1. Clone this repository:
   ```bash
   git clone https://github.com/DEEPAKRAJPOOT/iCloudEMS.git
