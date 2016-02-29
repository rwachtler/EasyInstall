EasyInstall
===========

*A Symfony project created on November 12, 2015, 7:48 am.*

#### How to run this application locally

##### Install Symfony

- **OS X / Linux** (see [Symfony Documentation](http://symfony.com/doc/current/book/installation.html#linux-and-mac-os-x-systems))
 - `sudo curl -LsS https://symfony.com/installer -o /usr/local/bin/symfony`
 - `sudo chmod a+x /usr/local/bin/symfony`


- **Windows** (see [Symfony Documentation](http://symfony.com/doc/current/book/installation.html#windows-systems))
 - `c:\> php -r "readfile('https://symfony.com/installer');" > symfony`
 - `c:\> move symfony c:\projects`
 - `c:\projects\> php symfony`

##### Create a config file

- Create a file named `EIconfig.php` in `EasyInstall/src/AppBundle/
- Insert following information into it
- 

    <?php
        namespace AppBundle;
        
        class EIconfig
        {
            public static $dbHost = 'YOUR_DB_HOST';
            public static $dbUser = 'YOUR_DB_USER';
            public static $dbPass = 'YOUR_DB_PASS';
            public static $coreDirectoryPath = 'PATH_WHERE_TMP_USERFOLDERS_WILL_BE_PLACED';
        }

##### Run the app

- `cd my_project_name/`
- `php app/console server:run` (try `php bin/console server:run` if something went wrong)
- Go to [http://localhost:8000](http://localhost:8000)

#### `ei-config.json` sample

    {
        "user_themes": [{
            "url": "https://downloads.wordpress.org/theme/theme-name.zip",
            "enable": true
        }],
        "user_plugins": [{
            "url": "https://downloads.wordpress.org/plugin/plugin-name-1.zip",
            "enable": true
        }, {
            "url": "https://downloads.wordpress.org/plugin/plugin-name-2.zip",
            "enable": true
        }, {
            "url": "https://downloads.wordpress.org/plugin/plugin-name-3.zip",
            "enable": true
        }],
        "user_posts": [{
            "title": "Demo Posting #1",
            "status": "publish",
            "type": "post",
            "content": "This is a demo post #1",
            "slug": "demo-posting",
            "parent": ""
        }, {
            "title": "Demo Posting #2",
            "status": "publish",
            "type": "post",
            "content": "This is a demo post #2",
            "slug": "demo-posting-2",
            "parent": ""
        }, {
            "title": "Demo Site #1",
            "status": "publish",
            "type": "page",
            "content": "This is a demo site!",
            "slug": "demo-site-1",
            "parent": ""
        }]
    }
