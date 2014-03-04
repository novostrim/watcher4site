 $scope.modal = function( name )
    {
//        $("#" + name ).parent().html('');
        $scope.xtemplate = 'tpl/test1.html';
//        $scope.$apply();
//        $("#" + name ).parent().html() + '{{boxtitle}}eseses';
        //alert( name );//jQuery("#" + name ).parent().html());
//        alert( '1');
       $timeout( function(){ $.colorbox( {//inline:true,
        html: $("#" + name ).parent().html(),//"<h1>Welcome</h1>",
//        href: 'tpl/test.html',
        opacity: 0.1,
        width:"600px",
        closeButton: false,
        open: true 
      } )
       }, 500);
    }

      <link rel="stylesheet" type="text/css" href="js/colorbox/colorbox.css" media="screen" />
  <!--link rel="stylesheet" type="text/css" href="css/bootstrap.css" media="screen" /-->

  <script src="/js/jquery.1.9.0.min.js" type="text/javascript"></script-->
  <script src="js/jquery.min.js" type="text/javascript"></script>
  <!--script type="text/javascript" src="js/colorbox/jquery.colorbox-min.js"></script>  
  <script type="text/javascript" src="js/colorbox/jquery.colorbox-ru.js"></script>
