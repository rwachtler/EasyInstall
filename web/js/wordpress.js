/**
 * Created by rwachtler on 16.01.16.
 */
$(document).ready(function(){

});

$("#downloadPackage").click(function(e){
    e.preventDefault();
    // Check database connection
    checkDbConnection(dbData, showDatabaseConnectionSuccessPopup, showDatabaseConnectionErrorModal);
    // Serialize input data
    var serializedBaseSettings = serializeWordPressFormData('form#base-settings');
    /**
     *  Starts the whole installation process
     *  1. Downloads a language specific WordPress package
     *  2. Unzips the package
     *  3. Configures WordPress (wp-config.php)
     *  4. Installs WordPress
     */
    downloadWordPressPackage(serializedBaseSettings, configureWordPress);
    // TODO: Install Theme
    // TODO: Install Plugins
    // TODO: Content?
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
                console.log(response);
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


