/*
    Watcher4site 
    (c) 2014 Novostrim, OOO. http://www.novostrim.com
    License: MIT
*/

function IndexCtrl($scope, $http, $routeSegment ) {
    $scope.$routeSegment = $routeSegment;
    $scope.form = {};   
    $scope.info = { ok: 0, numfiles: '-' };

    $http.post('api/gettask.php', { }).success(function(data) {
            if ( data.success )
            {
                $scope.form = data.result;
            }
            else
                $scope.msg_error( $scope.lng[ data.err ] + ( data.err == 'err_system' ? 
                                 ' [' + data.code + ']' : '' ) );
    });
    $scope.hashchange = function( value, callback ) {
        $scope.form.hash = value;
        callback();
    }
    $scope.getinfo = function() {
        $http.post('api/getinfo.php', { }).success(function(data) {
                if ( data.success )
                    $scope.info = data.result;
                else
                    $scope.msg_error( $scope.lng[ data.err ] + ( data.err == 'err_system' ? 
                                     ' [' + data.code + ']' : '' ) );
        });
    }
    $scope.continue = function() {
        $http.post('api/conttask.php', {}).success(function(data) {
            $scope.getinfo();
        });
    }
    $scope.cont = function()
    {
        $scope.info.ok = 2;
        if ( $scope.break )
        {
//            $scope.getinfo();
            $scope.info.ok = 3;
            return;
        }
        $http.post('api/starttask.php', {}).success( function(data) {
            $scope.getinfo();
            if ( data.success && data.result == 0 )
                $scope.cont();
        });

    }
    $scope.breakcheck = function() {
        $scope.break = true;
    }
    $scope.start = function() {
        $scope.info.ok = 2;
        $scope.info.numfiles = '-';
        $scope.info.changes = '0';
        $scope.break = false;
        $scope.loop = setInterval( function(){ 
                if ( $scope.info.ok == 2 ) 
                    $scope.getinfo();
                else
                    clearInterval( $scope.loop );
            }, 5000 );
//        alert( angular.toJson( $scope.form ));
        $http.post('api/starttask.php', { form: $scope.form }).success( function(data) {
            $scope.getinfo();
            if ( data.success && data.result == 0 )
                $scope.cont();
        });
    }
    $scope.getinfo();
}

function ReportCtrl($scope, $http, $routeSegment ) {
    $scope.$routeSegment = $routeSegment;
    $scope.info = { ok: 0, numfiles: '-' };
    $scope.pages = { pages: 1 };
    $http.post('api/getinfo.php', { }).success(function(data) {
        if ( data.success )
            $scope.info = data.result;
        else
            $scope.msg_error( $scope.lng[ data.err ] + ( data.err == 'err_system' ? 
                             ' [' + data.code + ']' : '' ) );
    });
    $scope.form = {};   
    $http.post('api/gettask.php', { }).success(function(data) {
        if ( data.success )
        {
            $scope.form = data.result;
            $scope.form.ignpath = $scope.form.ignpath.replace( '\n',', '); 
        }
        else
            $scope.msg_error( $scope.lng[ data.err ] + ( data.err == 'err_system' ? 
                             ' [' + data.code + ']' : '' ) );
    });
    $http.get('api/report.php', {params: $scope.$routeSegment.$routeParams} ).success(function(data) {
        if ( data.success )
        {
            $scope.pages = data.pages;
            $scope.items = data.items;
            $scope.uptime = data.uptime;
        }
        else
            $scope.msg_error( $scope.lng[ data.err ] + ( data.err == 'err_system' ? 
                             ' [' + data.code + ']' : '' ) );
    });
    $scope.clearreport = function() {
       $http.post('api/clearreport.php', { }).success(function(data) {
        if ( data.success )
        {
            document.location = '#/';
        }
        else
            $scope.msg_error( $scope.lng[ data.err ] + ( data.err == 'err_system' ? 
                             ' [' + data.code + ']' : '' ) );
    }); 
    }
}

geapp.controller( 'InstallCtrl', function InstallCtrl($scope, $http ) {
    $scope.langlist = langlist;
    $scope.form = {};
    $scope.submit = function() {
        $http.post('ajax/waccess.php', {}).success(function(data) {
            if ( data.success )
            {
                $http.post('ajax/install.php', { form: $scope.form, lang: $scope.lng.code }).success(function(data) {
                    if ( data.success )
                    {
                        cfg.user = data.user;
                        document.location = '#/';
                    }
                    else
                    {
                        cfg.temp = data.temp;
                        $scope.msg_error( $scope.lng[ data.err ] + ( data.err == 'err_system' ? 
                                         ' [' + data.code + ']' : '' ) );
                    }
                })
            }
            else
            {
                cfg.temp = data.temp;
                $scope.msg_error( $scope.lng[ data.err ] + ( data.err == 'err_system' ? 
                             ' [' + data.code + ']' : '' ) );
            }
        })
    }
});

geapp.controller( 'LoginCtrl', function LoginCtrl($scope, $http ) {
    $scope.form = {};
    $scope.submit = function() {
        $http.post('ajax/login.php', { form: $scope.form }).success(function(data) {
            if ( data.success )
            {
                document.location = '';
/*                if ( typeof( conf.prevurl ) == 'undefined' || conf.prevurl == '/login' )
                    document.location = '';
                else
                    document.location = '#' + conf.prevurl;
                cfg.user = data.user;
                if ( typeof( conf.prevurl ) == 'undefined' || conf.prevurl == '/login' )
                      $location.path( '' );
//                    $state.go( 'index' );
                else
                    $location.path( conf.prevurl );*/
            }
            else
                $scope.msg_error( $scope.lng[ data.err ] );
        })
    }
});

geapp.controller( 'SettingsCtrl', function SettingsCtrl( $scope, DbApi, $rootScope ) {
    $scope.langlist = langlist;
    $scope.form = { title: cfg.title, login: cfg.user.login, email: cfg.user.email, apitoken: cfg.apitoken };    
    $scope.language = function()
    {
        DbApi[ 'saveusr' ]( { lang: $scope.form.lang }, function( data ) {
                        $scope.$parent.changelng( $scope.form.lang ) });
    }
});


function AutoCtrl($scope, $http, $routeSegment, $rootScope ) {
    $scope.$routeSegment = $routeSegment;
    $http.get('api/getauto.php', {} ).success(function(data) {
        if ( data.success )
            $scope.form = data.result;
        else
            $scope.msg_error( $scope.lng[ data.err ] + ( data.err == 'err_system' ? 
                             ' [' + data.code + ']' : '' ) );
    });  
    $scope.save = function() {
        $rootScope.loading = true;
        $http.post('api/saveauto.php', $scope.form ).success(function(data) {
        $rootScope.loading = false;
        if ( data.success )
            angular.extend( $scope.form, data.result );
        else
            $scope.msg_error( $scope.lng[ data.err ] + ( data.err == 'err_system' ? 
                             ' [' + data.code + ']' : '' ) );
        });  
    }
} 

