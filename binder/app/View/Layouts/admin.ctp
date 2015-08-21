<!DOCTYPE html>
<?php $this->Html->loadConfig('html5_tags'); ?>
<html>
<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Administration of Annotation Image Viewer</title>

    <!-- JQuery UI Stylesheet -->
    <?php echo $this->Html->css('../lib/jquery.ui/jquery-ui.min.css'); ?>

    <!-- Bootstrap Stylesheet -->
    <?php echo $this->Html->css('../lib/bootstrap/css/bootstrap.min.css'); ?>
    <?php echo $this->Html->css('../lib/bootstrap/css/bootstrap-theme.min.css'); ?>
    <?php echo $this->Html->css('../lib/font-awesome/css/font-awesome.min.css'); ?>

    <!-- Application Stylesheet -->
    <?php echo $this->Html->css('style.css'); ?>

</head>
<body>
<div id="wrapper">

    <!-- Navigation -->
    <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
        <div class="navbar-header">
            <p class="navbar-brand homeBtn"><i title="TOP" data-placement="top" data-toggle="tooltip" class="fa fa-home  fa-fw"></i><a href="<?php echo Configure::read('server.integration_server_url'); ?>">RegMed</a></p>
            <?php echo $this->Html->link(
            'Annotation Image Viewer',
            array('controller' => 'binder', 'action' => 'index', 'full_base' => true),
            array('class' => 'navbar-brand')
            );
            ?>
        </div>
        <!-- /.navbar-header -->

        <ul class="nav navbar-top-links navbar-right">
            <li class="dropdown">
                <a href="#">
                    <i class="fa fa-user fa-fw"></i>  <?php echo $user['User']['displayName']; ?>
                </a>
            </li><!-- /.dropdown -->
            <li class="dropdown">
                <a class="dropdown-toggle" data-toggle="dropdown" href="<?php echo Configure::read('login.logout_uri'); ?>">
                    <i class="fa fa-sign-out fa-fw"></i>  Logout
                </a>
            </li>
        </ul><!-- / .nav navbar-top-links navbar-right -->
    </nav><!-- /nav -->

    <div id="page-wrapper">

        <!-- Sidebar -->
        <div id="sidebar" class="uv_sidenav">
            <div class="uv_sidenav__box uv_sidenav__srcBox">
                <div id="acdBox101">

					<!--users-->
                    <ul class="nav">
						<li class="sidebar-formbox">
	<?php echo $this->Form->create(false, array('type'=>'post','url' => array('controller' => 'users', 'action' => 'index')));?>
	<?php $user = "<i data-placement='top' data-toggle='tooltip' class='fa fa-user fa-fw'></i>" . __('ユーザー管理') ;?>
    <?php echo $this->Form->button($user, array('type' => 'submit','class'=> 'btn btn-primary'));?>
    <?php echo $this->Form->end();?>
						</li>
                    </ul>

					<!--logs-->
                    <ul class="nav">
						<li class="sidebar-formbox">
	<?php echo $this->Form->create(false, array('type'=>'post','url' => array('controller' => 'logs', 'action' => 'index')));?>
	<?php $user = "<i data-placement='top' data-toggle='tooltip' class='fa fa-list fa-fw'></i>" . __('ログ管理') ;?>
    <?php echo $this->Form->button($user, array('type' => 'submit','class'=> 'btn btn-primary'));?>
    <?php echo $this->Form->end();?>
						</li>
                    </ul>

					<!--backups-->
                    <ul class="nav">
						<li class="sidebar-formbox">
	<?php echo $this->Form->create(false, array('type'=>'post','url' => array('controller' => 'backups', 'action' => 'index')));?>
	<?php $user = "<i data-placement='top' data-toggle='tooltip' class='fa fa-database fa-fw'></i>" . __('DBバックアップ') ;?>
    <?php echo $this->Form->button($user, array('type' => 'submit','class'=> 'btn btn-primary'));?>
    <?php echo $this->Form->end();?>
						</li>
                    </ul>

                </div>
            </div>
        </div>

        <!-- ここにコンテンツを記載します -->
        <div id="page-contents">

<?php echo $content_for_layout;?>








        </div>

    </div><!-- /#page-wrapper -->

</div><!-- /#wrapper -->


<!-- JQuery -->
<?php echo $this->Html->script('../lib/jquery/jquery-2.1.3.min.js'); ?>
<!-- JQuery UI -->
<?php echo $this->Html->script('../lib/jquery.ui/jquery-ui.min.js'); ?>

</body>
</html>
