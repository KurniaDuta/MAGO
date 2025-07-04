<div align="center">
  <img src="https://img.shields.io/badge/MAGO-Magang%20On%20The%20GO-blue?style=for-the-badge" alt="MAGO - Magang On The GO" />
  <h1>🏢 MAGO 👷</h1>
  <p><strong>Magang On The GO</strong> - Information Technology Student Internship Information System</p>
  <p>
    <img src="https://img.shields.io/badge/Laravel-v10.3-FF2D20?style=flat-square&logo=laravel&logoColor=white" alt="Laravel v10.3" />
    <img src="https://img.shields.io/badge/Filament-v3.3-34D399?style=flat-square&logo=laravel&logoColor=white" alt="Filament v3.3" />
    <img src="https://img.shields.io/badge/Spatie-v6-F16061?style=flat-square" alt="Spatie v6" />
    <img src="https://img.shields.io/badge/Cloudinary-Storage-3448C5?style=flat-square&logo=cloudinary&logoColor=white" alt="Cloudinary" />
    <img src="https://img.shields.io/badge/OpenStreetMap-Geocode-7EBC6F?style=flat-square&logo=openstreetmap&logoColor=white" alt="OpenStreetMap" />
  </p>
</div>

> [!WARNING]
> This main branch is protected. To contribute, please make a [**Pull Request**](https://github.com/Khip01/MAGO/pulls) instead of committing directly to the main branch.

## 📋 Table of Contents
- [Project Overview](#-project-overview)
- [Tech Stack](#-tech-stack)
- [Getting Started](#-getting-started)
- [Authentication](#-authentication)
- [Contributing](#-contributing)
- [Troubleshooting](#-troubleshooting)
- [Attribution](#️-attribution)

## 🔍 Project Overview
MAGO (Magang On The GO) is an Information Technology Student Internship Information System developed at Malang State Polytechnic. The platform simplifies internship management by connecting students, faculty advisors, and administrators in one integrated system.

## 🛠 Tech Stack

<div align="center">
  <table>
    <tr>
      <td align="center">
        <kbd><img src="https://avatars.githubusercontent.com/u/958072?s=200&v=4" width="60" height="60" alt="Laravel" style="object-fit: cover;"/></kbd>
      </td>
      <td align="center">
        <kbd><img src="https://logowik.com/content/uploads/images/filament-laravel4896.logowik.com.webp" width="60" height="60" alt="Filament" style="object-fit: fill;"/></kbd>
      </td>
      <td align="center">
        <kbd><img src="https://avatars.githubusercontent.com/u/7535935?s=48&v=4" width="60" height="60" alt="Spatie" style="object-fit: cover;"/></kbd>
      </td>
      <td align="center">
        <kbd><img src="https://cdn.prod.website-files.com/64d41aab8183c7c3324ddb29/674f5ebd0de31390e6f53218_3-logo-brand-square.svg" width="60" height="60" alt="Cloudinary" style="object-fit: cover;"/></kbd>
      </td>
      <td align="center">
        <kbd><img src="https://raw.githubusercontent.com/juancarlospaco/nim-overpass/master/osm.jpg" width="60" height="60" alt="OpenStreetMap" style="object-fit: cover;"/></kbd>
      </td>
    </tr>
    <tr>
      <td align="center"><b>Laravel<br>v10.3</b></td>
      <td align="center"><b>Filament<br>v3.3</b></td>
      <td align="center"><b>Spatie v6</b></td>
      <td align="center"><b>Cloudinary</b></td>
      <td align="center"><b>Nominatim<br>Geocode</b></td>
    </tr>
    <tr>
      <td align="center"><small>PHP<br>Framework</small></td>
      <td align="center"><small>Admin Panel<br>Builder</small></td>
      <td align="center"><small>Permission<br>Management</small></td>
      <td align="center"><small>Image Storage<br>Solution</small></td>
      <td align="center"><small>OpenStreetMap<br>Integration</small></td>
    </tr>
  </table>
</div>

## 🚀 Getting Started

### Prerequisites
- PHP 8.1 or higher
- Composer
- MySQL
- Cloudinary account

### Step-by-Step Installation

<details>
<summary>📥 1. Clone & Setup</summary>

```bash
# Clone the repository
git clone https://github.com/Khip01/MAGO.git
cd MAGO

# Install dependencies
composer install

# Generate application key
php artisan key:generate
```
</details>

<details>
<summary>⚙️ 2. Configure Environment</summary>

Create a `.env` file by copying `.env.example`:

```bash
cp .env.example .env
```

Configure your database:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mago_db
DB_USERNAME=root
DB_PASSWORD=
```

Configure Cloudinary:
```env
### CLOUDINARY CONFIGURATION ###
# Option 1
CLOUDINARY_URL=cloudinary://api_key:api_secret@cloud_name

# Option 2
# CLOUDINARY_CLOUD_NAME=your_cloud_name
# CLOUDINARY_API_KEY=your_api_key
# CLOUDINARY_API_SECRET=your_api_secret
```
</details>

<details>
<summary>🗄️ 3. Database Setup</summary>

```bash
# Create your MySQL database (e.g., mago_db)

# Run migrations and seeders
php artisan migrate:fresh --seed
```
</details>

<details>
<summary>🖥️ 4. Launch the Application</summary>

```bash
php artisan serve
```

Visit `http://localhost:8000` in your browser
</details>

## 🔐 Authentication

### User Roles
This system has three main roles:
- **Admin**
- **Mahasiswa**
- **Dosen Pembimbing**

### Login Instructions
1. Navigate to `/login` (e.g., `http://localhost:8000/login`)
2. Enter credentials based on your role
3. You'll be redirected to your role-specific dashboard

> [!NOTE]
> Default login credentials are available in the following seeder files:
> - `seeders/UserSeeder.php`
> - `seeders/MahasiswaSeeder.php`
> - `seeders/AdminSeeder.php`
> - `seeders/DosenPembimbingSeeder.php`

> [!NOTE]
> When you provide credentials on the login page, the web will immediately redirect you to the filament panel according to the role of the credentials of the user who is currently logged in.

## 🤝 Contributing

> [!WARNING]
> If you want to contribute, this main branch is a protected branch, so you can't commit directly to the main branch. Instead you must make a [**Pull request**](https://github.com/Khip01/MAGO/pulls) to this main branch.

## ⚠️ Troubleshooting

### Missing Vendor Directory
If you encounter this error:
```
Warning: require(...\MAGO/vendor/autoload.php): Failed to open stream: No such file or directory in ...\MAGO\artisan on line 18
Fatal error: Uncaught Error: Failed to open required '...\MAGO/vendor/autoload.php' (include_path='.;C:/laragon/etc/php/pear') in ...\MAGO\artisan:18
Stack trace:
#0 {main}
  thrown in ...\MAGO\artisan on line 18
```

**Solution:** Run `composer install` to install all dependencies.

### Database Connection Issues
Verify your `.env` database settings match your actual MySQL configuration.

## ©️ Attribution
This application uses data from © [OpenStreetMap](https://www.openstreetmap.org/) contributors, available under the [Open Database License (ODbL)](https://opendatacommons.org/licenses/odbl/).

---

<div align="center">
  <p>
    © 2025 MAGO. All Rights Reserved.<br>
    <small>Final Project for Project-Based Learning at Malang State Polytechnic.</small>
  </p>
</div>