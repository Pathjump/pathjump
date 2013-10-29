/**
 * this function is used to get cookie value
 * @author w3schools added by Mahmoud
 */
function getCookie(c_name){
    var i,x,y,ARRcookies=document.cookie.split(";");
    for (i=0;i<ARRcookies.length;i++){
        x=ARRcookies[i].substr(0,ARRcookies[i].indexOf("="));
        y=ARRcookies[i].substr(ARRcookies[i].indexOf("=")+1);
        x=x.replace(/^\s+|\s+$/g,"");
        if (x==c_name){
            return unescape(y);
        }
    }
    return false;
}

/**
 * this function is used to set cookie value
 * @author w3schools added by Mahmoud
 */
function setCookie(c_name,value,exdays){
    var exdate=new Date();
    exdate.setDate(exdate.getDate() + exdays);
    var c_value=escape(value) + ((exdays==null) ? "" : "; expires="+exdate.toUTCString());
    document.cookie=c_name + "=" + c_value +  "; path=/";
}

/**
 * this function is used to delete a cookie
 * @author Mahmoud
 */
function deleteCookie(c_name){
    var exdate=new Date();
    exdate.setDate(exdate.getDate() + -1);
    var c_value = escape(0) +  "; expires=" + exdate.toUTCString();
    document.cookie = c_name + "=" + c_value +  "; path=/";
}

/**
 * this function is used to delete the cookies containing c_name
 * @param c_name part of the cookies names to delete
 * @author Mahmoud
 */
function deleteCookies(c_name){
    var exdate=new Date();
    exdate.setDate(exdate.getDate() + -1);
    var c_value = escape(0) +  "; expires=" + exdate.toUTCString();

    var i,x,ARRcookies = document.cookie.split(";");
    for (i=0;i<ARRcookies.length;i++){
        x=ARRcookies[i].substr(0,ARRcookies[i].indexOf("="));
        x=x.replace(/^\s+|\s+$/g,"");
        if (x.indexOf(c_name) != -1){
            document.cookie = x + "=" + c_value +  "; path=/";
        }
    }
}