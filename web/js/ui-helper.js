/**
 * Created by rwachtler on 16.01.16.
 */
/**
 * Next handler
 */
$(".next").click(function(e){
    e.preventDefault();
    var targetOffset = $($(this).attr('href')).offset().top;
    $("html, body").animate({scrollTop:targetOffset-50},600);
});
