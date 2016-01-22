/**
 * Created by rwachtler on 16.01.16.
 */
$(document).ready(function(){

});

$("#downloadPackage").click(function(e){
    e.preventDefault();
    // Check database connection
    checkDbConnection(dbData, showDatabaseConnectionSuccessPopup, showDatabaseConnectionErrorModal);
    // TODO: Serialize input data and download WordPress
    var serializedBaseSettings = serializeWordPressFormData('form#base-settings');
    downloadWordPressPackage(serializedBaseSettings, unzipWordPressPackage);
    // TODO: Unzip WordPress
    // TODO: Configure WordPress
    // TODO: Install WordPress
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

var downloadWordPressPackage = function(language, unzipCallback){
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
        data: language,
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
                console.log("Downloaded!");
                $loadingOverlay.animate(
                    {
                        opacity : 0
                    },'slow',
                    function(){
                        $loadingOverlay.hide('slow');
                        unzipCallback();
                    }
                );
            }
        }
    });
}

var unzipWordPressPackage = function(){
    console.log("Unzipping...")
}


