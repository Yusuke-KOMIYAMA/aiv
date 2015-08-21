<!DOCTYPE html>
<html lang="en">

<head>

    <?php echo $this->Html->charset(); ?>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title><?php echo $title_for_layout; ?>:</title>

	<?php
		//echo $this->Html->css('cake.user');

		echo $this->Html->css('bootstrap.min.css');
		echo $this->Html->css('plugins/metisMenu/metisMenu.min.css');
		echo $this->Html->css('plugins/timeline.css');
		echo $this->Html->css('sb-admin-2.css');
		echo $this->Html->css('plugins/morris.css');
		echo $this->Html->css('plugins/morris.css');


		echo $scripts_for_layout;
	?>


    <!-- Custom Fonts -->
	<link href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css" rel="stylesheet">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

</head>

<body class="editPage">

    <div id="wrapper">

        <!-- Navigation -->
        <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="index.html">ユニバーサルビューワー Universal Viewer</a>
            </div>
            <!-- /.navbar-header -->

			<ul class="nav navbar-top-links navbar-right">
				<li class="dropdown">
					<a href="#">
						<i class="fa fa-user fa-fw"></i>  ユニバーサル 太郎
					</a>
				</li><!-- /.dropdown -->
				<li class="dropdown">
					<a class="dropdown-toggle" data-toggle="dropdown" href="#">
						<i class="fa fa-sign-out fa-fw"></i>  ログアウト</i>
					</a>
				</li>
			</ul><!-- / .nav navbar-top-links navbar-right -->
			
        </nav>

<?php echo $content_for_layout;?>



    </div>
    <!-- /#wrapper -->
	<?php
		echo $this->Html->script( 'bootstrap/jquery.js', array( 'inline' => true));//<!-- jQuery -->
		echo $this->Html->script( 'bootstrap/bootstrap.min.js', array( 'inline' => true));//<!-- Bootstrap Core JavaScript -->
		echo $this->Html->script( 'bootstrap/sb-admin-2.js', array( 'inline' => true));//<!-- Custom Theme JavaScript -->
		echo $this->Html->script( 'bootstrap/plugins/metisMenu/metisMenu.min.js', array( 'inline' => true));//<!-- Metis Menu Plugin JavaScript -->
	?>
    

    <script>
	$('#myTab a').click(function (e) {
		e.preventDefault()
		$(this).tab('show')
	})
	</script>

	<?php
		echo $this->Html->script( 'bootstrap/plugins/morris/raphael.min.js', array( 'inline' => true));//<!-- Morris Charts JavaScript -->
		echo $this->Html->script( 'bootstrap/plugins/morris/morris.min.js', array( 'inline' => true));//<!-- Morris Charts JavaScript -->
		echo $this->Html->script( 'bootstrap/plugins/morris/morris-data.js', array( 'inline' => true));//<!-- Morris Charts JavaScript -->
	?>


</body>

</html>



