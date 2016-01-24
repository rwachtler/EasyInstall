/**
 * Created by rwachtler on 16.01.16.
 */
$(document).ready(function(){

});
var userConfiguration;
$("#downloadPackage").click(function(e){
    e.preventDefault();
    // Check database connection
    checkDbConnection(dbData, showDatabaseConnectionSuccessPopup, showDatabaseConnectionErrorModal);
    // Serialize input data
    var serializedBaseSettings = serializeWordPressFormData('form#settings');
    /**
     *  Starts the whole installation process
     *  1. Downloads a language specific WordPress package
     *  2. Unzips the package
     *  3. Configures WordPress (wp-config.php)
     *  4. Installs WordPress
     *  5. Installs WordPress themes defined inside ei-config.json (and activates if enable = true)
     *  6. Installs WordPress plugins defined inside ei-config.json (and activates if enable = true)
     */
    downloadWordPressPackage(serializedBaseSettings, configureWordPress);
    // TODO: Content
    // TODO: Sample config file download
});

/**
 * Serializes the values of a form and splits them by '&'
 * @param formIdentifier - form to serialize
 * @returns {Array|jQuery} - array of serialized form values
 */
var serializeWordPressFormData = function(formIdentifier){
    var serializedDataArr = $(formIdentifier).serialize().split("&");
    var serializedDataObj = {};
    serializedDataArr.forEach(function(value, index){
        var tmp = value.split("=");
        serializedDataObj[tmp[0]] = tmp[1];
    });
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

var configureWordPress = function(settings){
    console.log("Configuring and installing...");
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
                if(userConfiguration !== 'undefined'){
                    installWordPressThemesFromConfig();
                } else {
                    $loadingOverlay.animate(
                        {
                            opacity : 0
                        },'slow',
                        function(){
                            $loadingOverlay.hide('slow');
                        }
                    );
                }
            }
        }
    });
}

var installWordPressThemesFromConfig = function(){
    console.log("Installing themes...");
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
                installWordPressPluginsFromConfig();
            }
        }
    });
}

var installWordPressPluginsFromConfig = function(){
    console.log("Installing plugins...");
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
                if(getUserActivePlugins().user_active_plugins.length > 0){
                    activateWordPressPluginsFromConfig();
                } else{
                    $loadingOverlay.animate(
                        {
                            opacity : 0
                        },'slow',
                        function(){
                            $loadingOverlay.hide('slow');
                        }
                    );
                }
            }
        }
    });
}

var activateWordPressPluginsFromConfig = function(){
    console.log("Activating plugins...");
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
                $loadingOverlay.animate(
                    {
                        opacity : 0
                    },'slow',
                    function(){
                        $loadingOverlay.hide('slow');
                    }
                );
            }
        }
    });
}

// TODO: Fix console error if user selects nothing
$('#configuration-file').change(function(){
    var file = $(this)[0].files[0];
    var reader = new FileReader();
    reader.onload = readerLoad;
    reader.readAsText(file);


});

var readerLoad = function(event){
    userConfiguration = JSON.parse(event.target.result);
}

var getUserThemes = function(){
    return {user_themes : userConfiguration.user_themes};
}

var getUserPlugins = function(){
    return {user_plugins : userConfiguration.user_plugins};
}

getUserActivePlugins = function(){
    var active_plugins = [];
    $(userConfiguration.user_plugins).each(function(){
        if($(this)[0].enable === true){
            active_plugins.push($(this)[0]);
        }
    });
    return {user_active_plugins : active_plugins};
}

var getUserPosts = function(){
    return {user_posts : userConfiguration.user_posts};
}


