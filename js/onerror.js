window.onerror = function handler(
    msg,
    file,
    line,
    col,
    err) {
    if (!window.JSON || handler.count > 5) { return; }
    let logData = {
        msg: msg,
        file: (file ? file + ':' + line + ':' + col : 'nofile'),
        loc: location.href,
        ua: window.navigator.userAgent
    };
    let url = '/jserror.php?e=' + encodeURIComponent(JSON.stringify(logData));
    if (typeof navigator.sendBeacon === 'function') {
        navigator.sendBeacon(url, ' ');
    } else {
        new Image().src = url;
    }
    if (handler.count) {
        handler.count++;
    } else {
        handler.count = 1;
    }
};