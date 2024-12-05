$(document).ready(function() {
    var userAgent = navigator.userAgent;
    var platform = navigator.platform;

    var isIpad = !!navigator.maxTouchPoints && navigator.maxTouchPoints > 1 && navigator.platform === 'MacIntel';

    if (isIpad) {
        $("#monitor").hide();
    }

    // if (!isIpad) {
    //     $("#tablet").hide();
    // }

});