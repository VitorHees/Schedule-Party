# Laravel 12 - starter project

- Clone this repository to the folder **C:\Sites_laravel**
- Rename the folder in **project**
- Start and open Laravel Herd
  - Secure the site with `https` (the site will be available on `https://project.test`)
- Open the folder **C:\Sites_laravel\project** in PHPStorm
    - Rename the file **.env.example** to **.env**
    - Run the command `composer install` in the terminal
    - Run the command `npm install` in the terminal
    - Run the command `php artisan storage:link`
    - Run the command `php artisan key:generate`
    - Run the command `php artisan migrate` (just enter or type `yes` when asked for creating the SQLite database)
    - Run the command `npm run dev` to open the project in the browser
- Configure your project (https://itf-laravel-12.netlify.app/config/laravel#initial-project-configuration)
