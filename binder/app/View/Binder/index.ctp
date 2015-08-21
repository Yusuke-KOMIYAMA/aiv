<!DOCTYPE html>
<?php $this->Html->loadConfig('html5_tags'); ?>
<html lang="ja" ng-app="universalViewer" ng-controller="universalViewerController">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title ng-bind="title"></title>

    <!-- JQuery UI Stylesheet -->
    <?php echo $this->Html->css('../lib/jquery.ui/jquery-ui.min.css'); ?>

    <!-- Bootstrap Stylesheet -->
    <?php echo $this->Html->css('../lib/bootstrap/css/bootstrap.min.css'); ?>
    <?php echo $this->Html->css('../lib/bootstrap/css/bootstrap-theme.min.css'); ?>
    <?php echo $this->Html->css('../lib/font-awesome/css/font-awesome.min.css'); ?>

    <!-- owl.carousel -->
    <?php echo $this->Html->css('../lib/owl-carousel/owl.carousel.css'); ?>
    <?php echo $this->Html->css('../lib/owl-carousel/owl.theme.css'); ?>
    <?php echo $this->Html->css('../lib/owl-carousel/owl.transitions.css'); ?>

    <!-- Angular loading-bar -->
    <?php echo $this->Html->css('../lib/angular/loading-bar.min.css'); ?>

    <!-- ngTagsInput -->
    <?php echo $this->Html->css('../lib/angular/ng-tags-input.min.css'); ?>
    <?php echo $this->Html->css('../lib/angular/ng-tags-input.bootstrap.min.css'); ?>

    <!-- evol color picker -->
    <?php echo $this->Html->css('../lib/evol.colorpicker/css/evol.colorpicker.min.css'); ?>

    <!-- Application Stylesheet -->
    <?php echo $this->Html->css('style.css'); ?>

    <!-- JQuery -->
    <?php echo $this->Html->script('../lib/jquery/jquery-2.1.3.min.js'); ?>
    <!-- JQuery UI -->
    <?php echo $this->Html->script('../lib/jquery.ui/jquery-ui.min.js'); ?>

    <!--  owl.carousel -->
    <?php echo $this->Html->script('../lib/owl-carousel/owl.carousel.min.js'); ?>

    <!-- AngularJS -->
    <?php echo $this->Html->script('../lib/angular/angular.min.js'); ?>
    <?php echo $this->Html->script('../lib/angular/angular-resource.min.js'); ?>
    <?php echo $this->Html->script('../lib/angular/angular-touch.min.js'); ?>
    <?php echo $this->Html->script('../lib/angular/angular-ui-router.min.js'); ?>
    <?php echo $this->Html->script('../lib/angular/ui-sortable.js'); ?>
    <?php echo $this->Html->script('../lib/angular/ui.sortable.multiselection.js'); ?>
    <?php echo $this->Html->script('../lib/angular/angular-animate.min.js'); ?>
    <?php echo $this->Html->script('../lib/angular/loading-bar.min.js'); ?>

    <!-- Angular translate -->
    <?php echo $this->Html->script('../lib/angular/angular-translate.min.js'); ?>
    <?php echo $this->Html->script('../lib/angular/angular-translate-storage-cookie.min.js'); ?>
    <?php echo $this->Html->script('../lib/angular/angular-translate-storage-local.min.js'); ?>
    <?php echo $this->Html->script('../lib/angular/angular-translate-loader-static-files.min.js'); ?>
    <?php echo $this->Html->script('../lib/angular/angular-translate-handler-log.min.js'); ?>

    <!-- Bootstrap -->
    <?php echo $this->Html->script('../lib/bootstrap/js/bootstrap.min.js'); ?>

    <!-- Angular UI Bootstrap -->
    <?php echo $this->Html->script('../lib/angular/ui-bootstrap-tpls.min.js'); ?>

    <!-- Angular UI Bootstrap -->
    <?php echo $this->Html->script('../lib/angularstrap/angular-strap.min.js'); ?>
    <?php echo $this->Html->script('../lib/angularstrap/angular-strap.tpl.min.js'); ?>

    <!-- Openseadragon -->
    <?php echo $this->Html->script('../lib/openseadragon/openseadragon.min.js'); ?>
    <?php echo $this->Html->script('../lib/openseadragon/angular-openseadragon.js'); ?>
    <?php echo $this->Html->script('../lib/openseadragon/openseadragon-viewerinputhook.min.js'); ?>

    <!-- Snap.svg -->
    <?php echo $this->Html->script('../lib/snap.svg/snap.svg-min.js'); ?>

    <!-- ngTagsInput -->
    <?php echo $this->Html->script('../lib/angular/ng-tags-input.min.js'); ?>

    <!-- evol color picker -->
    <?php echo $this->Html->script('../lib/evol.colorpicker/js/evol.colorpicker.min.js'); ?>

    <!-- timeline -->
    <?php echo $this->Html->script('../lib/timeline/js/storyjs-embed.js'); ?>

    <!-- Application scripts -->
    <!-- Settings -->
    <?php echo $this->Html->script('configs/config.js'); ?>

    <!-- Common functions -->
    <?php echo $this->Html->script('commons/Utils.js'); ?>
    <?php echo $this->Html->script('commons/ImageViewer.js'); ?>

    <!-- Directives -->
    <?php echo $this->Html->script('directives/universalViewerDirective.js'); ?>
    <?php echo $this->Html->script('directives/uvLoadBackgroundImage.js'); ?>
    <?php echo $this->Html->script('directives/uvToggle.js'); ?>
    <?php echo $this->Html->script('directives/uvSidebarToggle.js'); ?>
    <?php echo $this->Html->script('directives/uvDisableAnimate.js'); ?>

    <!-- Classes -->
    <?php echo $this->Html->script('classes/Annotation.js'); ?>
    <?php echo $this->Html->script('classes/OriginalPage.js'); ?>
    <?php echo $this->Html->script('classes/BinderPage.js'); ?>
    <?php echo $this->Html->script('classes/Binder.js'); ?>

    <!-- Common services -->
    <?php echo $this->Html->script('services/common/SharedStoreService.js'); ?>
    <?php echo $this->Html->script('services/common/OriginalPageResourceService.js'); ?>
    <?php echo $this->Html->script('services/common/BinderResourceService.js'); ?>
    <?php echo $this->Html->script('services/common/BinderPageResourceService.js'); ?>
    <?php echo $this->Html->script('services/common/TagResourceService.js'); ?>
    <?php echo $this->Html->script('services/common/UserResourceService.js'); ?>
    <?php echo $this->Html->script('services/common/SearchService.js'); ?>
    <?php echo $this->Html->script('services/commonService.js'); ?>

    <!-- View services -->
    <?php echo $this->Html->script('services/view/homeViewService.js'); ?>
    <?php echo $this->Html->script('services/view/binderViewService.js'); ?>
    <?php echo $this->Html->script('services/view/originalPageViewService.js'); ?>
    <?php echo $this->Html->script('services/view/originalPageDetailViewService.js'); ?>
    <?php echo $this->Html->script('services/view/binderPageViewService.js'); ?>
    <?php echo $this->Html->script('services/view/binderPageDetailViewService.js'); ?>
    <?php echo $this->Html->script('services/view/timelineViewService.js'); ?>
    <?php echo $this->Html->script('services/universalViewerService.js'); ?>

    <!-- Controllers -->
    <?php echo $this->Html->script('controllers/universalViewerController.js'); ?>
    <!-- Applications -->
    <?php echo $this->Html->script('universalViewerApp.js'); ?>

    <style>
        [ng\:cloak], [ng-cloak], [data-ng-cloak], [x-ng-cloak], .ng-cloak, .x-ng-cloak {
            display: none !important;
        }
    </style>

</head>
<body>

    <div id="wrapper">

        <!-- Navigation -->
        <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
            <div class="navbar-header">
                <p class="navbar-brand homeBtn"><i title="TOP" data-placement="top" data-toggle="tooltip" class="fa fa-home  fa-fw"></i><a href="<?php echo Configure::read('server.integration_server_url'); ?>"  translate="integration_server.name"></a></p>
                <a class="navbar-brand" ui-sref="home"><span translate='application.name'></span></a>
            </div>
            <!-- /.navbar-header -->

            <ul class="nav navbar-top-links navbar-right">
                <li class="dropdown">
                    <a href="#">
                        <i class="fa fa-user fa-fw"></i>  <?php echo $user['User']['displayName']; ?><?php if ($user['User']['id'] != $loginUser['User']['id']) {?> ( <span translate='header.loginuser'></span>: <?php echo $loginUser['User']['userName']; ?> ) <?php } ?>
                    </a>
                </li><!-- /.dropdown -->
                <li class="dropdown">
                    <a href="<?php echo Configure::read('login.logout_uri'); ?>">
                        <i class="fa fa-sign-out fa-fw"></i>  <span translate='header.logout'></span>
                    </a>
                </li>
            </ul><!-- / .nav navbar-top-links navbar-right -->
        </nav><!-- /nav -->

        <div id="page-wrapper" ng-class="className" class="clearfix" ui-view="wrapper"></div>

    </div><!-- /#wrapper -->

    <span id="langId" style="display:none;"><?php echo Configure::read("server.language") ?></span>
    <span id="userId" style="display:none;"><?php echo $user['User']['id']; ?></span>


</body>
</html>
