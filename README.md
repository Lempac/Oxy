# Oxy
## About
This is a project for learning the development of social team collaboration tool,
that lets you create, manage groups with roles in company or group of people with goal. 
![UI](/assets/ui.png)

### The groups have the ability to chat or voice call.
![Group](/assets/group.png)
![Group Chat](/assets/group_chat.png)
![Group Voice Chat](/assets/group_voice_chat.png)

### And plan on kanban board.
![Kanban Boards](/assets/kanban_boards.png)
![Kanban Cards](/assets/kanban_cards.png)

### Settings for managing groups and kanban boards.
![Settings](/assets/group_settings.png)

## How to build our project
To build the project just run theese commands:

php artisan storage:link
php artisan reverb:start
php artisan queue:work 

This will setup the project and insure that it work.
### If you don't have an email server, go to User.php and remove this line:
```php
use Illuminate\Contracts\Auth\MustVerifyEmail;
```
 
## To run the site
To run the site you will need XAMPP (Apache and MySQL)
and run theese 2 command in the terminal:

npm run dev
php artisan serve

