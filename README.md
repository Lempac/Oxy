# Oxy
## How to build our project
To build the project just run theese commands:

php artisan storage:link
php artisan reverb:start
php artisan queue:work 

This will setup the project and insure that it work.
### If you don't have an email server, go to User.php and remove this line:
"use Illuminate\Contracts\Auth\MustVerifyEmail;"
 
## To run the site
To run the site you will need XAMPP (Apache and MySQL)
and run theese 2 command in the terminal:

npm run dev
php artisan serve

