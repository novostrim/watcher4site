'use strict';

//(function() {
angular.extend(angular, {
    toParam: toParam
});

/**
 * Source: [url]http://habrahabr.ru/post/181009/[url]
 * Преобразует объект, массив или массив объектов в строку,
 * которая соответствует формату передачи данных через url
 * Почти эквивалент [url]http://api.jquery.com/jQuery.param/[/url]
 * Источник [url]http://stackoverflow.com/questions/1714786/querystring-encoding-of-a-javascript-object/1714899#1714899[/url]
 *
 * @param object
 * @param [prefix]
 * @returns {string}
 */
function toParam(object, prefix) {
    var stack = [];
    var value;
    var key;

    for (key in object) {
        value = object[key];
        key = prefix ? prefix + '[' + key + ']' : key;

        if (value === null) {
            value = encodeURIComponent(key) + '=';
        } else if (typeof( value ) !== 'object') {
            value = encodeURIComponent(key) + '=' + encodeURIComponent(value);
        } else {
            value = toParam(value, key);
        }

        stack.push(value);
    }

    return stack.join('&');
}

//}());

function js_footer(hoff) {
    var w, h, ch;
    w = (window.innerWidth ? window.innerWidth : (document.documentElement.clientWidth ? document.documentElement.clientWidth : document.body.offsetWidth));
    h = (window.innerHeight ? window.innerHeight : (document.documentElement.clientHeight ? document.documentElement.clientHeight : document.body.offsetHeight));
    //document.getElementById("main").style.width = w;
    var hfoot = parseInt(document.getElementById("xfooter").clientHeight);
    alert(document.getElementById("xfooter").clientHeight);
    h = h - hfoot - hoff;
    ch = document.getElementById("main").clientHeight;
    alert(ch + ' ' + hfoot + ' ' + h);
    if (parseInt(ch) < h) {
        $("#main").css('height', h + 'px');
        ch = h;
    }
}

function js_loadjs(jsfile, f) {
    var head = document.getElementsByTagName("head")[0];
    var script = document.createElement('script');
    script.type = 'text/javascript';
    script.src = jsfile;
    var done = false;
    script.onload = script.onreadystatechange = function () {
        if (!done && (!this.readyState ||
            this.readyState == "loaded" || this.readyState == "complete")) {
            done = true;
            if (typeof f == 'function') f();
            script.onload = script.onreadystatechange = null;
            head.removeChild(script);
        }
    };
    head.appendChild(script);
}
