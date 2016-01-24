EasyInstall
===========

*A Symfony project created on November 12, 2015, 7:48 am.*

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
