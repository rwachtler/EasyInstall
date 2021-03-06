/**
 * Created by rwachtler on 16.01.16.
 */
$(document).ready(function(){
    var obj = {
        "user_themes": [{
            "url": "THEME_URL",
            "enable": true
        }],
        "user_plugins": [{
            "url": "PLUGIN_URL",
            "enable": true
        }, {
            "url": "PLUGIN_URL",
            "enable": true
        }, {
            "url": "PLUGIN_URL",
            "enable": true
        }],
        "user_posts": [{
            "title": "Demo Posting #1",
            "status": "publish",
            "type": "post",
            "content": "Easy Install Rocks!!!",
            "slug": "demo-posting",
            "parent": ""
        }, {
            "title": "Demo Posting #2",
            "status": "publish",
            "type": "post",
            "content": "Second Blogpost using ei-config!",
            "slug": "demo-posting-2",
            "parent": ""
        }, {
            "title": "Demo Site #1",
            "status": "publish",
            "type": "page",
            "content": "This is a demo-site!!!",
            "slug": "demo-site-1",
            "parent": ""
        }]
    };
    var data = "text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(obj));
    $('#sample-config-file-download').attr("href", "data:" + data);
    $('#sample-config-file-download').attr('download', 'ei-config.json');
});
var userConfiguration;
/**
 *  Starts the whole installation process
 *  0. Checks the connection to database
 *  0.5 - Serializes input data
 *  1. Downloads a language specific WordPress package
 *  2. Unzips the package
 *  3. Configures WordPress (wp-config.php)
 *  4. Installs WordPress
 *  5. Installs WordPress themes defined inside ei-config.json (and activates if enable = true)
 *  6. Installs WordPress plugins defined inside ei-config.json (and activates if enable = true)
 *  7. Inserts posts defined inside ei-config.json
 *  8. Wraps the ZIP package for user
 *  9. Delivers the package
 *  10. Cleans up
 */
$("#downloadPackage").click(function(e){
    e.preventDefault();
    $statusLayer.show('slow');
    // Check database connection
    checkDbConnection(dbData, showDatabaseConnectionSuccessPopup, showDatabaseConnectionErrorModal);
    // Serialize input data
    var serializedBaseSettings = serializeWordPressFormData('form#settings');
    downloadWordPressPackage(serializedBaseSettings, configureWordPress);
});

/**
 * Serializes the values of a form and splits them by '&'
 * @param formIdentifier - form to serialize
 * @returns {Array|jQuery} - array of serialized form values
 */
var serializeWordPressFormData = function(formIdentifier){
    console.log("Serializing input data...");
    $statusLayer.text("Serializing input data");
    var serializedDataArr = $(formIdentifier).serialize().split("&");
    var serializedDataObj = {};
    serializedDataArr.forEach(function(value, index){
        var tmp = value.split("=");
        serializedDataObj[tmp[0]] = tmp[1];
    });
    console.log("Serializing completed!")
    return serializedDataObj;
}

/**
 * Performs an ajax request to the wp-download route
 * which triggers the downloading of language specific
 * WordPress package
 * @param serializedSettings - Serialized WordPress settings object
 * @param configWpCallback - WordPress configuration callback function
 */
var downloadWordPressPackage = function(serializedSettings, configWpCallback){
    console.log("Downloading...");
    $statusLayer.text("Downloading WordPress package");
    $loadingOverlay.show('slow', function(){
        $loadingOverlay.animate(
            {
                opacity : 1
            },'slow'
        );
    });
    $.ajax({
        url: "wp-download",
        type: "POST",
        data: serializedSettings,
        cache: false,
        error: function(err) {
            $loadingOverlay.animate(
                {
                    opacity : 0
                },'slow',
                function(){
                    $loadingOverlay.hide('slow');
                }
            );
            console.log(err)
        },
        success: function(response) {
            if(response.status === "success"){
                console.log("Downloaded & Unzipped!");
                configWpCallback(serializedSettings);
            }
        }
    });
}

/**
 * Performs an ajax request to the wp-config route
 * which triggers the configuring of WordPress (wp-config.php)
 * @param settings - Serialized WordPress settings object
 */
var configureWordPress = function(settings){
    console.log("Configuring and installing...");
    $statusLayer.text("Configuring WordPress");
    $.ajax({
        url: "wp-config",
        type: "POST",
        data: settings,
        cache: false,
        error: function(err) {
            $loadingOverlay.animate(
                {
                    opacity : 0
                },'slow',
                function(){
                    $loadingOverlay.hide('slow');
                }
            );
            console.log(err)
        },
        success: function(response) {
            if(response.status === "success"){
                console.log("Configured and installed!");
                if(userConfiguration === undefined){
                    exportPackage();
                } else{
                    if(!(userConfiguration.user_themes === undefined)){
                        installThemesFromConfig();
                    } else{
                        if(!(userConfiguration.user_plugins === undefined)){
                            installPluginsFromConfig();
                        } else{
                            if(!(userConfiguration.user_posts === undefined)){
                                insertPosts();
                            }
                        }
                    }
                }
            }
        }
    });
}

/**
 * Performs an ajax request to the wp-install-theme route
 * which triggers the download and installation (optional activation)
 * of WordPress Themes defined inside the ei-config.json
 */
var installThemesFromConfig = function(){
    console.log("Installing themes...");
    $statusLayer.text("Installing themes");
    $.ajax({
        url: "wp-install-theme",
        type: "POST",
        data: getUserThemes(),
        cache: false,
        error: function(err) {
            $loadingOverlay.animate(
                {
                    opacity : 0
                },'slow',
                function(){
                    $loadingOverlay.hide('slow');
                }
            );
            console.log(err)
        },
        success: function(response) {
            if(response.status === "success"){
                console.log("Themes installed!");
                if(!(userConfiguration.user_plugins === undefined)){
                    installPluginsFromConfig();
                } else{
                    exportPackage();
                }
            }
        }
    });
}

/**
 * Performs an ajax request to the wp-install-plugin route
 * which triggers the download and installation of
 * WordPress Plugins defined inside the ei-config.json
 */
var installPluginsFromConfig = function(){
    console.log("Installing plugins...");
    $statusLayer.text("Installing plugins");
    $.ajax({
        url: "wp-install-plugin",
        type: "POST",
        data: getUserPlugins(),
        cache: false,
        error: function(err) {
            $loadingOverlay.animate(
                {
                    opacity : 0
                },'slow',
                function(){
                    $loadingOverlay.hide('slow');
                }
            );
            console.log(err)
        },
        success: function(response) {
            if(response.status === "success"){
                console.log("Plugins installed!");
                // Check if there are WordPress Plugins to activate
                if(getUserActivePlugins().user_active_plugins.length > 0){
                    activatePluginsFromConfig();
                } else if(!(userConfiguration.user_posts === undefined)){
                    insertPosts();
                } else{
                    exportPackage();
                }
            }
        }
    });
}

/**
 * Performs an ajax request to the wp-activate-plugin route
 * which triggers the activation of
 * WordPress Plugins defined inside the ei-config.json
 */
var activatePluginsFromConfig = function(){
    console.log("Activating plugins...");
    $statusLayer.text("Activating plugins");
    $.ajax({
        url: "wp-activate-plugin",
        type: "POST",
        data: getUserActivePlugins(),
        cache: false,
        error: function(err) {
            $loadingOverlay.animate(
                {
                    opacity : 0
                },'slow',
                function(){
                    $loadingOverlay.hide('slow');
                }
            );
            console.log(err)
        },
        success: function(response) {
            if(response.status === "success"){
                console.log("Plugins activated!");
                if(!(userConfiguration.user_posts === undefined)){
                    insertPosts();
                } else{
                    exportPackage();
                }
            }
        }
    });
}

/**
 * Performs an ajax request to the wp-insert-post route
 * which triggers the inserting of posts/sites defined
 * inside the ei-config.json
 */
var insertPosts = function(){
    console.log("Inserting posts...");
    $statusLayer.text("Inserting posts");
    $.ajax({
        url: "wp-insert-post",
        type: "POST",
        data: getUserPosts(),
        cache: false,
        error: function(err) {
            $loadingOverlay.animate(
                {
                    opacity : 0
                },'slow',
                function(){
                    $loadingOverlay.hide('slow');
                }
            );
            console.log(err)
        },
        success: function(response) {
            if(response.status === "success"){
                console.log("Posts inserted!");
                exportPackage();
            }
        }
    });
}

var exportPackage = function(){
    console.log("Wrapping your package...");
    $statusLayer.text("Wrapping your ZIP package");
    var serializedBaseSettings = serializeWordPressFormData('form#settings');
    $.ajax({
        url: "wp-prepare-package",
        type: "POST",
        data: serializedBaseSettings,
        cache: false,
        error: function(err) {
            $loadingOverlay.animate(
                {
                    opacity : 0
                },'slow',
                function(){
                    $loadingOverlay.hide('slow');
                }
            );
            console.log(err)
        },
        success: function(response) {
            if(response.status === "success"){
                window.location.href = "wp-export-package";
                console.log("Here you go!");
                $loadingOverlay.animate(
                    {
                        opacity : 0
                    },'slow',
                    function(){
                        $statusLayer.hide('slow');
                        $loadingOverlay.hide('slow');
                        cleanUp();
                    }
                );
            }
        }
    });
}

var cleanUp = function(){
    console.log("Cleaning up...");
    $.ajax({
        url: "wp-cleanup",
        type: "POST",
        cache: false,
        error: function(err) {
            console.log(err)
        },
        success: function(response) {
            if(response.status === "success"){
                console.log("Cleaned up!");
            }
        }
    });
}

$('#configuration-file').change(function(){
    var file = $(this)[0].files[0];
    if(file !== 'undefined'){
        var reader = new FileReader();
        reader.onload = setUserConfiguration;
        reader.readAsText(file);
    }
});

/**
 * Sets the userConfiguration variable
 * which contains the values from ei-config.json
 * @param event - onload event
 */
var setUserConfiguration = function(event){
    userConfiguration = JSON.parse(event.target.result);
}

/**
 * Returns user_themes values out of userConfiguration
 * @returns {{user_themes: *}}
 */
var getUserThemes = function(){
    return {user_themes : userConfiguration.user_themes};
}
/**
 * Returns user_plugins values out of userConfiguration
 * @returns {{user_plugins: *}}
 */
var getUserPlugins = function(){
    return {user_plugins : userConfiguration.user_plugins};
}

/**
 * Checks some of the user_plugins should be activated
 * @returns {{user_active_plugins: Array}} - array of user_plugins to activate
 */
getUserActivePlugins = function(){
    var active_plugins = [];
    $(userConfiguration.user_plugins).each(function(){
        if($(this)[0].enable === true){
            active_plugins.push($(this)[0]);
        }
    });
    return {user_active_plugins : active_plugins};
}

/**
 * Returns user_posts values out of userConfiguration
 * @returns {{user_posts: *}}
 */
var getUserPosts = function(){
    return {user_posts : userConfiguration.user_posts};
}


