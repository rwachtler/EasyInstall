$(document).ready(function(){
});
var $dbCheckButton = $('#check-database-connection');
var $dbRedoCheckButton = $dbCheckButton.find("span");
var dbData = {
    "name" : $("#database-name").val()
};
var $loadingOverlay = $('#loading-overlay');

$dbCheckButton.click(function(e){
    e.preventDefault();
    $dbRedoCheckButton.removeClass("hidden");
    checkDbConnection(dbData, showDatabaseButtonStatusSuccess, showDatabaseButtonStatusError);

});

var checkDbConnection = function(dbData, successCallback, errorCallback){
    $.ajax({
        url: "check-connection",
        type: 'POST',
        data: dbData,
        cache: false,
        error: function() {
            errorCallback();
        },
        success: function(response) {
            successCallback(response);
        }
    });
}

var showDatabaseButtonStatusError = function(){
    $dbCheckButton.removeClass("btn-default").addClass("btn-danger");
    $dbCheckButton.text("Database connection failed ").append($dbRedoCheckButton);
}

var showDatabaseButtonStatusSuccess = function(response){
    if(response.status === "failure"){
        showDatabaseButtonStatusError();
    } else if(response.status === "success"){
        $dbCheckButton.removeClass("btn-danger").removeClass("btn-default").addClass("btn-success");
        $dbCheckButton.text("Database connection succeeded").append($dbRedoCheckButton);
    }
}

var showDatabaseConnectionErrorModal = function(){
    $('#database-connection-error-modal').modal('show');
}

var showDatabaseConnectionSuccessPopup = function(response){
    if(response.status === "failure"){
        showDatabaseConnectionErrorModal();
    } else if(response.status === "success"){}
}



