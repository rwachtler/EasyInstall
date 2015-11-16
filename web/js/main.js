$(document).ready(function(){
});

$("#check-database-connection").click(function(e){
    e.preventDefault();
    var button = $(this);
    var buttonRefresh = $(this).find("span").removeClass("hidden");
    var dbData = {
        "name" : $("#database-name").val(),
        "username" : $("database-username").val(),
        "pass" : $("database-password").val(),
        "prefix" : $("database-prefix").val()
    };

    var asyncRequest = $.post("/check-connection", dbData)
       .done(function(response){
            if(response.status === "failure"){
                button.removeClass("btn-default").addClass("btn-danger");
                button.text("Database connection failed ").append(buttonRefresh);
            } else if(response.status === "success"){
                button.removeClass("btn-danger").removeClass("btn-default").addClass("btn-success");
                buttonRefresh.addClass("hidden");
                button.text("Database connection succeeded");
            }
       })
       .fail(function(response){
            button.removeClass("btn-default").addClass("btn-danger");
            button.text("Database connection failed ").append(buttonRefresh);
       })
});

/**
 * Next handler
 */
$(".next").click(function(e){
    e.preventDefault();
    var targetOffset = $($(this).attr('href')).offset().top;
    $("html, body").animate({scrollTop:targetOffset-50},600);
});
