# Inflektion Email Parser

This Laravel project parses raw email content stored in a MySQL database and extracts the plain text body of each email. The extracted body is saved back into the same record under the `raw_text` column.

## 📦 Requirements

- PHP >= 8.1
- Laravel >= 10
- MySQL
- Composer

## 📁 Table: `successful_emails`

| Column        | Type     | Description                              |
|---------------|----------|------------------------------------------|
| id            | int      | Primary key                              |
| email         | longtext | Raw full email content                   |
| raw_text      | text     | Parsed plain text (nullable, to be filled) |
| created_at    | datetime | Timestamp                                |
| updated_at    | datetime | Timestamp                                |

## ⚙️ Setup

```bash
git clone https://github.com/bogdanrusu98/inflektion-email-parser.git
cd inflektion-email-parser
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
Set your .env file with correct database credentials.

🔧 Command: emails:parse
This command will:

Fetch all records from successful_emails where raw_text is NULL or empty.

Parse the email field and extract only the plain text content.

Save it into the raw_text column.

Run Manually:

php artisan emails:parse
Sample Output:
yaml
Copy
Edit
Parsed email ID: 1
Parsed email ID: 2
All emails parsed successfully.
Example Crontab (Run every 5 minutes):
cron
Copy
Edit
*/5 * * * * cd /var/www/html && php artisan schedule:run >> /dev/null 2>&1
Ensure App\Console\Kernel has:

php

protected function schedule(Schedule $schedule): void
{
    $schedule->command('emails:parse')->everyFiveMinutes();
}
✅ Testing
To test with dummy data:

sql

UPDATE successful_emails SET raw_text = '' WHERE raw_text IS NOT NULL;
php artisan emails:parse
🔒 Notes
.env and /vendor are excluded via .gitignore.

Avoid committing sensitive credentials or auto-generated files.

Contributions welcome. For any questions, open an issue or contact bogdanrusu98.



---

