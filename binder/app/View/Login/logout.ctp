<!DOCTYPE html>
<?php $this->Html->loadConfig('html5_tags'); ?>
<html lang="en" ng-app="loginApp" ng-controller="loginCtrl">
<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title translate="login.logout.title"></title>

    <!-- Bootstrap Core CSS -->
    <?php echo $this->Html->css('../lib/bootstrap/css/bootstrap.min.css'); ?>
    <!-- Custom Fonts -->
    <?php echo $this->Html->css('../lib/font-awesome/css/font-awesome.min.css'); ?>
    <!-- Custom CSS -->
    <?php echo $this->Html->css('style.css'); ?>

    <style>
        [ng\:cloak], [ng-cloak], [data-ng-cloak], [x-ng-cloak], .ng-cloak, .x-ng-cloak {
            display: none !important;
        }
    </style>

</head>

<body>

    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="alert alert-success" role="alert" style="margin-top: 12%;">
                    <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                    <span translate="login.logout.message"></span>
                </div>
                <?php if ($loginType === 'SSO') { ?>
                <a href="<?php echo Configure::read('login.logout_endpoint'); ?>" translate="login.logout.link.sso"></a>
                <?php } else { ?>
                <a href="<?php echo $this->Html->url('/login/', true); ?>" translate="login.logout.link.local"></a>
                <?php } ?>
            </div>
        </div>
    </div>

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
