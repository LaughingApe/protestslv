function cookieAlert(){
    $(".cookie-alert").css("display", "block");
}
function stopCookieAlert(){
    $(".cookie-alert").css("display", "none");
    localStorage.setItem("accepts-cookies", "true");
}


$(document).ready(function(){
    if( localStorage.getItem("accepts-cookies") != "true" ){
        cookieAlert();
    }
});