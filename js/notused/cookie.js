
// https://github.com/angular/angular.js/pull/2459
geapp.provider('$cookieStore', [function(){
    var self = this;
    self.defaultOptions = {};

    self.setDefaultOptions = function(options){
        self.defaultOptions = options;
    };

    self.$get = function(){
        return {
            get: function(name){
                var jsonCookie = $.cookie(name);
                if(jsonCookie){
                    return angular.fromJson(jsonCookie);
                }
            },
            put: function(name, value, options){
                options = $.extend({}, self.defaultOptions, options);
                $.cookie(name, angular.toJson(value), options);
            },
            remove: function(name, options){
                options = $.extend({}, self.defaultOptions, options);
                $.removeCookie(name, options);
            }
        };
    };
}]);

geapp.config(['$cookieStoreProvider', function($cookieStoreProvider){
    $cookieStoreProvider.setDefaultOptions({
//        path: '/', // Cookies should be available on all pages
        expires: 5 // Store cookies for a week
    });
}]);
