# Real Estate Agency Portal

This is a simple PHP + MySQL web app for a Real Estate Agency Portal.

## Files
- `index.php` - home page
- `login.php` - login page
- `register.php` - register page
- `logout.php` - logout session
- `dashboard.php` - user dashboard (shows role + actions)
- `properties.php` - browse property listings
- `property_details.php` - view one property and details
- `add_property.php` - agents add new listings
- `submit_inquiry.php` - buyers/renters send inquiries
- `config/` - database settings
- `classes/` - PHP classes (database + helper methods)
- `includes/` - shared PHP includes (header/footer, session checks, etc.)
- `sql/` - database scripts (tables, sample data, procedures/views/triggers)
- `assests/` - styles.cc file
## Setup
1. Start **XAMPP** (Apache + MySQL).
2. Open **phpMyAdmin** and create the database:
   - `real_estate_portal_db`
3. Import the SQL script(s) inside the `sql/` folder into `real_estate_portal_db`.
4. Move the project folder into:
   - `htdocs/`
5. Open in the browser:
   - `http://localhost/<project-folder>/index.php`

## What it does
- Supports 3 user roles: **agent**, **buyer**, and **renter**
- Allows users to **register** and **log in** securely (password hashing + sessions)
- Agents can **add and manage property listings**
- Buyers/renters can **browse properties** and **submit inquiries**
- The app stores users, listings, inquiries, transactions, and favorites in MySQL
- Uses joins/views to display property listings with agent info
