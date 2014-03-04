/*
    Watcher4site 
    (c) 2014 Novostrim, OOO. http://www.novostrim.com
    License: MIT
*/

var geapp = angular.module('genteeApp',  [ 'ngCookies', /*'ngSanitize',/*'ui.router',*/ 'ui.bootstrap',
               'ngRoute', 'route-segment', 'view-segment' ] );

geapp
.config( [ '$httpProvider', function( $httpProvider ) {    
        // Use x-www-form-urlencoded Content-Type
        $httpProvider.defaults.headers.post[ 'Content-Type' ] = 'application/x-www-form-urlencoded;charset=utf-8';
        // Override $http service's default transformRequest
        $httpProvider.defaults.transformRequest = function( data ) {
            return angular.isObject( data ) && String( data ) !== '[object File]' ? angular.toParam( data ) : data;
        };
    }])


geapp.config( function($routeSegmentProvider, $routeProvider) {
    $routeSegmentProvider.options.autoLoadTemplates = true;
    $routeSegmentProvider
          .when('/',            'index' )
          .when('/install',     'install' )
          .when('/login',       'login' )
          .when('/report',      'report' )
          .when('/auto',        'auto' )
          .when('/settings',    'settings' )
          .segment('index', {
            templateUrl: 'tpl/index.html',
             })
          .segment('auto', {
            templateUrl: 'tpl/auto.html',
             })
          .segment('settings', {
            templateUrl: 'tpl/settings.html'
            /*controller: MainCtrl*/ })
          .segment('install', {
            templateUrl: 'tpl/install.html' })
          .segment('login', {
            templateUrl: 'tpl/login.html' })
          .segment('report', {
            templateUrl: 'tpl/report.html',
            dependencies: ['p'] 
             })
});    

geapp.controller( 'GenteeCtrl', function GenteeCtrl($scope, $location, $cookies, 
    $rootScope, $modal, $timeout, DbApi, $sce, $routeSegment  ) {

//    angular.extend( cfg, conf );
    if ( typeof( cfg.module ) != 'undefined' )
    {
        cfg.prevurl = $location.path();
        $location.path( cfg.module );
    } 
    if ( !cfg.title.length )
        cfg.title = cfg.appname;
    
    cfg.apphead = $sce.trustAsHtml( cfg.apphead );

    $rootScope.cfg = cfg; 
    $rootScope.lng = lng;
    $rootScope.loading = false;
    $scope.module = cfg.module;
    $rootScope.$routeSegment = $routeSegment;
    $rootScope.menu = [
        { title: lng.dashboard, icon: 'dashboard', href: '#/', name: 'index'},
        { title: lng.report, icon: 'table', href: '#/report', name: 'report'},
        { title: lng.automation, icon: 'clock-o', href: '#/auto', name: 'auto'},
//        { title: lng.settings, icon: 'cogs', href: '#/appsettings', name: 'appsettings'},
    ];
/*    $scope.bigger = function(){
        var  mh = parseInt( document.getElementById("main").clientHeight ) + 100;
        $("#main").css( 'height', mh + 'px' );

    }*/
    $scope.changelng = function( langname )
    {
        js_loadjs( cfg.appname + "/l10n/locale_" + langname + '.js', function(){
                $rootScope.lng = lng; 
                $rootScope.$apply()
        });
    }
    $scope.logout = function()
    {
        $cookies.__pass = '';
        cfg.user = '';
        $timeout( function() { document.location = ''; },  200 );
//        $state.go( 'login' );

    }
    $rootScope.msg = function( dlg_opt )
    {
        if ( angular.isUndefined( dlg_opt.template ))
        {
            if ( angular.isDefined( this.lng[ dlg_opt.body ] ))
                dlg_opt.body = this.lng[ dlg_opt.body ];
            dlg_opt.template = false;
        }
        $scope.dlg_opt = dlg_opt;
        var modalInstance = $modal.open({
            templateUrl: 'dialog.html',
            controller: ModalInstanceCtrl,
            backdrop: true, //'static'
            resolve: {
                dlg_opt: function () {
                    return $scope.dlg_opt;
            }
        }

        });
        modalInstance.result.then( function() {
        }, function () {});
    }
    $rootScope.msg_error = function( text )
    {
        if ( parseInt( text ) )
            text = this.lng[ 'err_system' ] + ' [' + text + ']';
        this.msg( { title: lng.error, body: text, icon: 'fa-times-circle', icon_class: 'red'  } );
    }
    $rootScope.msg_warning = function( text )
    {
        this.msg( { title: lng.warning, body: text, icon: 'fa-exclamation-triangle', icon_class: 'yellow'  } );
    }
    $rootScope.msg_info = function( text )
    {
        this.msg( { title: lng.inform, body: text, icon: 'fa-info-circle', icon_class: 'blue'  } );
    }
    $rootScope.msg_quest = function( text, funcyes )
    {
        this.msg( { title: lng.confirm, body: text, icon: 'fa-question-circle', icon_class: 'blue',
                    btns: [ {text: lng.yes, func: funcyes, class: 'btn-primary btn-near' },
                    {text: lng.no, class: 'btn-default btn-near' }
        ]  } );
    }

    $rootScope.updatepass = function( data, obj )
    {
        this.msg_info( 'updpass' );
    }
    $rootScope.updateuser = function( data, obj )
    {
        for ( var key in obj ) 
           cfg.user[ key ] = obj[ key ];
    }
    $rootScope.updbname = function( data, obj )
    {
       cfg.title = obj[ 'name' ];
    }
});

var ModalInstanceCtrl = function( $scope, $modalInstance, dlg_opt ) {
    $scope.ok = function () {
        $modalInstance.close();
    };

    $scope.cancel = function() {
        $modalInstance.close();//dismiss();
    };
    $scope.button = function ( obj ) {
        if ( angular.isFunction( obj.func ))
            if ( obj.func() === false )
                return;
        $modalInstance.close();//dismiss();
    };
    $scope.dlg = {
        title: '',
        body: '',
        icon: '',
        icon_class: '',
        btns: [ {text: lng.close, class: 'btn-warning' }
        ]
    }
    angular.extend( $scope.dlg, dlg_opt );
    if ( angular.isDefined( $scope.dlg.body ))
        $scope.dlg.body = $scope.dlg.body.replace( '#temp#', cfg.temp );
};


geapp.factory( 'DbApi', function( $rootScope, $http ) {
    function ajaxerror( data, status )
    {
        $rootScope.loading = false;
        $rootScope.msg_error(  this.lng[ 'err_server' ] + ' [' + status + ']' );
    }
    function ajaxpost( params, ajaxname, callback )
    {
        $rootScope.loading = true;
        $http.post('ajax/' + ajaxname + '.php', { params: params }).
                    success(function ( data, status ) {
        $rootScope.loading = false;
        $rootScope.cfg.temp = data.temp;
        if ( data.success )
        {
            if ( angular.isDefined( callback ))
               callback( data );
        } 
        else
            $rootScope.msg_error( data.err );
        }).error( ajaxerror );
    }
    function ajaxget( params, ajaxname, callback )
    {
        $rootScope.loading = true;
        $http.get('ajax/' + ajaxname + '.php', { params: params }).
                    success(function ( data, status ) {
        $rootScope.loading = false;
        $rootScope.cfg.temp = data.temp;
        if ( data.success )
        {
            if ( angular.isDefined( callback ))
                callback( data );
        }
        else
            $rootScope.msg_error( data.err );
        }).error( ajaxerror );
    }
    return {
        changefld: function( params, callback  ){ ajaxpost( params, 'changefld', callback ) },
        delmenu: function( params, callback  ){ ajaxpost( params, 'delmenu', callback ) },
        droptable: function( params, callback  ){ ajaxpost( params, 'droptable', callback ) },
        edititem: function( params, callback  ){ ajaxget( params, 'edititem', callback ) },
        getmenu: function( params, callback  ){ ajaxget( params, 'getmenu', callback ) },
        getstruct: function( params, callback  ){ ajaxget( params, 'getstruct', callback ) },
        gettables: function( params, callback  ){ ajaxget( params, 'gettables', callback ) },
        gettree: function( params, callback  ){ ajaxget( params, 'gettree', callback ) },
        movemenu: function( params, callback  ){ ajaxpost( params, 'movemenu', callback ) },
        savefolder: function( params, callback  ){ ajaxpost( params, 'savefolder', callback ) },
        saveitem: function( params, callback  ){ ajaxpost( params, 'saveitem', callback ) },
        savemenu: function( params, callback  ){ ajaxpost( params, 'savemenu', callback ) },
        savestruct: function( params, callback  ){ ajaxpost( params, 'savestruct', callback ) },
        savedb: function( params, callback  ){ ajaxpost( params, 'savedb', callback ) },
        saveusr: function( params, callback ){ ajaxpost( params, 'saveusr', callback ) },
        table: function( params, callback  ){ ajaxget( params, 'table', callback ) }
    }
});


function js_menuover( objdiv )
{
    $(objdiv).children().eq( 1 ).css('display','block');
}

function js_menuout( objdiv )
{
    $(objdiv).children().eq( 1 ).css('display','none');
}

