var isTabActive;
var settime, tabkeypress, tabmousemove, tabvisible, timecheck;
console.log(duration);
if(duration>0){
    settime=(duration*60*60*1000);
    timecheck = new Date().getTime()+settime;
    window.onfocus = function () { 
        isTabActive = true; 
    }; 
    window.onblur = function () { 
        isTabActive = false; 
    }; 
    // test
    var hidden, visibilityChange;
    if (typeof document.hidden !== "undefined") {
        hidden = "hidden";
        visibilityChange = "visibilitychange";
        state = "visibilityState";
    } else if (typeof document.mozHidden !== "undefined") {
        hidden = "mozHidden";
        visibilityChange = "mozvisibilitychange";
        state = "mozVisibilityState";
    } else if (typeof document.msHidden !== "undefined") {
        hidden = "msHidden";
        visibilityChange = "msvisibilitychange";
        state = "msVisibilityState";
    } else if (typeof document.webkitHidden !== "undefined") {
        hidden = "webkitHidden";
        visibilityChange = "webkitvisibilitychange";
        state = "webkitVisibilityState";
    }
    document.addEventListener(visibilityChange, function() {
        tabvisible=document.hidden;
    });
    document.addEventListener('mousemove', function() {
        tabmousemove=true;
        timecheck = new Date().getTime()+settime;
    });
    document.addEventListener('keypress', function() {
        tabkeypress=true;
        timecheck = new Date().getTime()+settime;
    });
    var timer=setInterval(function () {
        var timenow = new Date().getTime();
        if(timenow>=timecheck){
            jQuery.ajax({
                method: "post",
                data: {
                    "idle_session_timeout": 'timeout',
                    'update_time': 'now',
                    'nonce'      : nonce
                },
                success: function(){
                    window.location=window.location;
                    clearInterval(timer);
                }
            });
            tabmousemove=false;
            tabkeypress=false;
        }
    }, 1000);
}