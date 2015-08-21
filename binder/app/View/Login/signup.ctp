<!DOCTYPE html>
<?php $this->Html->loadConfig('html5_tags'); ?>
<html lang="en" ng-app="loginApp" ng-controller="loginCtrl">
<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title translate="login.error.title"></title>

    <!-- JQuery UI Stylesheet -->
    <?php echo $this->Html->css('../lib/jquery.ui/jquery-ui.min.css'); ?>

    <!-- Bootstrap Stylesheet -->
    <?php echo $this->Html->css('../lib/bootstrap/css/bootstrap.min.css'); ?>
    <?php echo $this->Html->css('../lib/bootstrap/css/bootstrap-theme.min.css'); ?>
    <?php echo $this->Html->css('../lib/font-awesome/css/font-awesome.min.css'); ?>

    <!-- Application Stylesheet -->
    <?php echo $this->Html->css('style.css'); ?>

    <style>
        [ng\:cloak], [ng-cloak], [data-ng-cloak], [x-ng-cloak], .ng-cloak, .x-ng-cloak {
            display: none !important;
        }
    </style>

</head>
<body>

    <div id="wrapper">

        <div id="page-wrapper" class="signup">

            <div class="container">

                <div class="row" style="padding-top:10px;">
                    <div class="col-md-12">
                        <div class="alert alert-warning" role="alert" translate="login.error.message"></div>
                        <a href="<?php echo $this->Html->url('/login/', true); ?>" translate="login.error.link"></a>
                    </div>
                </div>

            </div>

        </div>

    </div><!-- /#wrapper -->

    <!-- JQuery -->
    <?php echo $this->Html->script('../lib/jquery/jquery-2.1.3.min.js'); ?>
    <!-- AngularJS -->
    <?php echo $this->Html->script('../lib/angular/angular.min.js'); ?>
    <!-- Bootstrap -->
    <?php echo $this->Html->script('../lib/bootstrap/js/bootstrap.min.js'); ?>
    <!-- Angular UI Bootstrap -->
    <?php echo $this->Html->script('../lib/angular/ui-bootstrap-tpls.min.js'); ?>
    <!-- Angular Translate -->
    <?php echo $this->Html->script('../lib/angular/angular-translate.min.js'); ?>
    <?php echo $this->Html->script('../lib/angular/angular-translate-storage-cookie.min.js'); ?>
    <?php echo $this->Html->script('../lib/angular/angular-translate-storage-local.min.js'); ?>
    <?php echo $this->Html->script('../lib/angular/angular-translate-loader-static-files.min.js'); ?>
    <?php echo $this->Html->script('../lib/angular/angular-translate-handler-log.min.js'); ?>

    <!-- Login script -->
    <?php echo $this->Html->script('configs/config.js'); ?>

    <!-- Login script -->
    <?php echo $this->Html->script('loginApp.js'); ?>

    <span id="langId" style="display:none;"><?php echo Configure::read("server.language") ?></span>

</body>
</html>


