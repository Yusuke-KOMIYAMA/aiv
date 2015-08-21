<!DOCTYPE html>
<?php $this->Html->loadConfig('html5_tags'); ?>
<html lang="en" ng-app="loginApp" ng-controller="loginCtrl">
<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title translate="login.title"></title>

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
                <?php if ($allowSSOLogin){ ?><a href="<?php echo $uri ?>" class="btn btn-lg btn-success btn-block" id="login_btn" translate="login.sso_login"></a><?php }?>

                <?php if ($message) { ?>
                <div class="alert alert-danger" role="alert" style="margin-top:20px;">
                    <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                    <span class="sr-only">Error:</span>
                    <span translate="login.local_login.message.<?php echo $message ?>"></span>
                </div>
                <?php } ?>

                <div class="login-panel panel panel-default col-md-offset-6">
                    <div class="panel-heading" ng-click="isCollapsed = !isCollapsed" ng-cloak>
                        <h3 class="panel-title uvLoginTitle" translate="login.local_login.title">　</h3>
                        <p>▼</p>
                    </div>
                    <div class="panel-body uvLogin" collapse="isCollapsed" ng-cloak>
                        <!--form role="form"-->
                        <?php echo $this->Form->create(false,array('type'=>'post','action'=>'','role'=>'form')); ?>
                            <fieldset>
                                <div class="form-group">
                                    <span>{{'login.local_login.id.label' | translate}}：</span><input name="data[localLoginForm][loginID]" class="form-control" placeholder="{{'login.local_login.id.placeholder' | translate}}" name="ID" type="ID" autofocus>
                                </div>
                                <div class="form-group">
                                    <span>{{'login.local_login.password.label' | translate}}：</span><input name="data[localLoginForm][password]" class="form-control" placeholder="{{'login.local_login.password.placeholder' | translate}}" name="password" type="password" value="">
                                </div>
                                <div class="checkbox">
                                    <button type="submit" class="btn btn-outline btn-primary" translate="login.local_login.submit_form"></button>
                                </div>
                            </fieldset>
                        <?php echo $this->Form->end(); ?>
                        <!--/form-->
                    </div>
                </div>

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
