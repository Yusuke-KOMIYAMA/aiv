<!DOCTYPE html>
<?php $this->Html->loadConfig('html5_tags'); ?>
<html ng-app="uploadApp"
      ng-controller="uploadController"
      flow-init="config"
      flow-file-added="isAllowFile($file)">
<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title translate="upload.title"></title>

    <!-- JQuery UI Stylesheet -->
    <?php echo $this->Html->css('../lib/jquery.ui/jquery-ui.min.css'); ?>

    <!-- Bootstrap Stylesheet -->
    <?php echo $this->Html->css('../lib/bootstrap/css/bootstrap.min.css'); ?>
    <?php echo $this->Html->css('../lib/bootstrap/css/bootstrap-theme.min.css'); ?>
    <?php echo $this->Html->css('../lib/font-awesome/css/font-awesome.min.css'); ?>

    <!-- ngTagsInput -->
    <?php echo $this->Html->css('../lib/angular/ng-tags-input.min.css'); ?>
    <?php echo $this->Html->css('../lib/angular/ng-tags-input.bootstrap.min.css'); ?>

    <!-- Angular loading bar -->
    <?php echo $this->Html->css('../lib/angular/loading-bar.min.css'); ?>

    <!-- Application Stylesheet -->
    <?php echo $this->Html->css('style.css'); ?>

    <style>
        [ng\:cloak], [ng-cloak], [data-ng-cloak], [x-ng-cloak], .ng-cloak, .x-ng-cloak {
            display: none !important;
        }
    </style>

</head>
<body flow-prevent-drop
      ng-style="style">

    <div id="wrapper">

        <!-- Navigation -->
        <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0" ng-cloak>
            <div class="navbar-header">
                <p class="navbar-brand homeBtn"><i title="TOP" data-placement="top" data-toggle="tooltip" class="fa fa-home  fa-fw"></i><a href="<?php echo Configure::read('server.integration_server_url'); ?>"><span translate="integration_server.name"></span></a></p>
                <a href="<?php echo $this->Html->url('/binder/', true); ?>" class="navbar-brand" translate="application.name"></a>
            </div>
            <!-- /.navbar-header -->

            <ul class="nav navbar-top-links navbar-right">
                <li class="dropdown">
                    <a href="#">
                        <i class="fa fa-user fa-fw"></i>  <?php echo $user['User']['displayName']; ?><?php if ($user['User']['id'] != $loginUser['User']['id']) {?> ( <span transrate="header.loginuser"></span>: <?php echo $loginUser['User']['userName']; ?>) <?php } ?>
                    </a>
                </li><!-- /.dropdown -->
                <li class="dropdown">
                    <a href="<?php echo Configure::read('login.logout_uri'); ?>">
                        <i class="fa fa-sign-out fa-fw"></i>  <span translate="header.logout"></span>
                    </a>
                </li>
            </ul><!-- / .nav navbar-top-links navbar-right -->
        </nav><!-- /nav -->

        <div id="page-wrapper" class="upload">

            <div class="container">

                <div class="row">

                    <h1 translate="upload.header_title"></h1>
                    <hr class="soften"/>

                </div>

                <hr class="soften">

                <form method="post" name="upload_form">

                    <div class="row">

                        <div class="col-md-12" ng-cloak>
                            <button class="btn btn-outline btn-primary" ng-disabled="isDisable" flow-btn><i class="fa fa-file fa-fw"></i><span translate="upload.button.file_upload"></span></button>
                            <button class="btn btn-outline btn-primary" ng-disabled="isDisable" flow-btn flow-directory ng-show="$flow.supportDirectory"><i class="fa fa-folder-open fa-fw"></i><span translate="upload.button.folder_upload"></span></button>
                        </div>

                    </div>

                    <hr class="soften"/>

                    <div class="row" ng-switch="getFlowStatus($flow)" ng-cloak>

                        <div ng-switch-when="1"
                             class="alert alert-info"
                             ng-class="class">
                            <span class="glyphicon glyphicon-refresh glyphicon-spin" aria-hidden="true"></span> <span translate="upload.message.uploading"></span>
                        </div>
                        <div ng-switch-when="2"
                             class="alert alert-success"
                             ng-class="class">
                            <span translate="upload.message.uploaded"></span> <button class="btn btn-info" ng-click="reloadButton()"><span class="glyphicon glyphicon-upload" aria-hidden="true"></span> <span translate="upload.button.more_upload"></span> </button>
                        </div>
                        <div ng-switch-when="3"
                             class="alert alert-danger"
                             ng-class="class">
                            <span translate="upload.message.uploaded_with_error"></span> <button class="btn btn-info" ng-click="reloadButton()"><span class="glyphicon glyphicon-upload" aria-hidden="true"></span> <span translate="upload.button.more_upload"></span></button>
                        </div>
                        <div ng-switch-default
                             class="alert alert-warning" flow-drop flow-drag-enter="class='alert alert-success'" flow-drag-leave="class='alert alert-warning'"
                             ng-class="class">
                            <span translate="upload.message.drag_and_drop"></span>
                        </div>

                    </div>

                    <div class="row">

                        <table class="table table-hover table-bordered table-striped" flow-transfers ng-cloak>
                            <thead>
                            <tr>
                                <th class="uv_num" translate="upload.table.number"></th>
                                <th class="uv_state" translate="upload.table.state"></th>
                                <th class="uv_file_name" translate="upload.table.file_name"></th>
                                <th class="uv_thumbnail" translate="upload.table.thumbnail"></th>
                                <th class="uv_title" translate="upload.table.title"></th>
                                <th class="uv_content" translate="upload.table.content"></th>
                                <th class="uv_creator" translate="upload.table.creator"></th>
                                <th class="uv_confirmor" translate="upload.table.confirmor"></th>
                                <th class="uv_creation_date" translate="upload.table.creation_date"></th>
                                <th class="uv_tag" translate="upload.table.tag"></th>
                                <th class="uv_file_size" translate="upload.table.file_size"></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr
                                ng-repeat="file in transfers"
                                ng-init="initFile(file, '<?php echo $user['User']['displayName']; ?>');"
                                ng-class="{disabled:file.isComplete()}"
                                ng-cloak
                            >
                                <td class="uv_num">{{$index+1}}</td>
                                <td class="uv_state" ng-switch="file.uploadStatus" ng-cloak>
                                    <span ng-switch-when="1"><span class="glyphicon glyphicon-refresh glyphicon-spin" aria-hidden="true"></span></span>
                                    <span ng-switch-when="2"><span class="glyphicon glyphicon-ok text-success" aria-hidden="true"></span></span>
                                    <span ng-switch-when="3"><span class="glyphicon glyphicon-remove text-danger" aria-hidden="true"></span></span>
                                    <span ng-switch-default><span class="glyphicon glyphicon-minus" aria-hidden="true"></span></span>
                                </td>
                                <td class="uv_file_name">{{file.name}}</td>
                                <td class="uv_thumbnail thumbnail"><img flow-img="$flow.files[$index]" ng-if="file.getType() != 'pdf'" /><img src="../../app/webroot/img/dummy.png" ng-if="file.getType() == 'pdf'" /></td>
                                <td class="uv_title"><input type="text" ng-model="file.title" name="title" ng-disabled="isDisable" required></td>
                                <td class="uv_content"><input type="text" ng-model="file.text" name="text" ng-disabled="isDisable" required></td>
                                <td class="uv_creator"><input type="text" ng-model="file.creator" name="creator" ng-disabled="isDisable" required></td>
                                <td class="uv_confirmor"><input type="text" ng-model="file.confirmor" name="confirmor" ng-disabled="isDisable" required></td>
                                <td class="uv_creation_date"><input type="text" ng-model="file.creationDate" name="creationDate" ng-disabled="isDisable" required></td>
                                <td class="uv_tag">
                                    <tags-input ng-model="file.tag" ng-disabled="isDisable">
                                        <auto-complete source="suggestTags($query)" min-length="2"></auto-complete>
                                    </tags-input>
                                </td>
                                <td class="uv_file_size">{{file.size | bytes}}</td>
                            </tr>
                            </tbody>
                        </table>

                        <div class="col-md-12">

                            <p ng-cloak>
                                <a class="btn btn-small btn-success" ng-click="$flow.resume()" ng-disabled="upload_form.$invalid || isDisable" translate="upload.button.upload"></a>
                                <span class="label label-info">Size: {{$flow.getSize() | bytes}}</span>
                            </p>

                        </div>

                    </div>

                </form>

            </div>

        </div>

    </div><!-- /#wrapper -->


    <!-- JQuery -->
    <?php echo $this->Html->script('../lib/jquery/jquery-2.1.3.min.js'); ?>
    <!-- JQuery UI -->
    <?php echo $this->Html->script('../lib/jquery.ui/jquery-ui.min.js'); ?>
    <!-- AngularJS -->
    <?php echo $this->Html->script('../lib/angular/angular.min.js'); ?>
    <?php echo $this->Html->script('../lib/angular/angular-resource.min.js'); ?>
    <?php echo $this->Html->script('../lib/angular/angular-animate.min.js'); ?>
    <?php echo $this->Html->script('../lib/angular/loading-bar.min.js'); ?>

    <!-- Angular translate -->
    <?php echo $this->Html->script('../lib/angular/angular-translate.min.js'); ?>
    <?php echo $this->Html->script('../lib/angular/angular-translate-storage-cookie.min.js'); ?>
    <?php echo $this->Html->script('../lib/angular/angular-translate-storage-local.min.js'); ?>
    <?php echo $this->Html->script('../lib/angular/angular-translate-loader-static-files.min.js'); ?>
    <?php echo $this->Html->script('../lib/angular/angular-translate-handler-log.min.js'); ?>

    <!-- ng-flow -->
    <?php echo $this->Html->script('../lib/ng-flow/ng-flow-standalone.js'); ?>

    <!-- ngTagsInput -->
    <?php echo $this->Html->script('../lib/angular/ng-tags-input.min.js'); ?>

    <!-- Settings -->
    <?php echo $this->Html->script('commons/Utils.js'); ?>
    <?php echo $this->Html->script('configs/config.js'); ?>

    <!-- User scripts -->
    <?php echo $this->Html->script('services/common/TagResourceService.js'); ?>
    <?php echo $this->Html->script('uploadApp.js'); ?>

    <span id="langId" style="display:none;"><?php echo Configure::read("server.language") ?></span>

</body>
</html>



