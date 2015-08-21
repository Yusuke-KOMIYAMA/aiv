<div id="admcontents">
<div class="panel panel-primary">
	<div class="panel-heading">
		<h1 class="panel-title"><?php echo __('ユーザー登録');?></h1>
	</div>
	<div class="panel-body">
<?php echo $this->Form->create('Post'); ?>

<div class="form-group">
	<label for="userName" class="control-label"><?php echo __('ユーザー名'); ?></label>
<?php echo $this->Form->text("User.userName",array('class'=>'form-control'))."\n"; ?><br />
<?php echo $this->Form->error("User.userName")."\n"; ?>
</div>
<div class="form-group">
	<label for="ssoLoginID" class="control-label"><?php echo __('SSOログインID'); ?></label>
<?php echo $this->Form->text("User.ssoLoginID",array('class'=>'form-control'))."\n"; ?><br />
<?php echo $this->Form->error("User.ssoLoginID")."\n"; ?>
</div>
<div class="form-group">
	<label for="localLoginID" class="control-label"><?php echo __('ローカルログインID'); ?></label>
<?php echo $this->Form->text("User.localLoginID",array('class'=>'form-control'))."\n"; ?><br />
<?php echo $this->Form->error("User.localLoginID")."\n"; ?>
</div>
<div class="form-group">
	<label for="localLoginPassword" class="control-label"><?php echo __('ローカルログインパスワード'); ?></label>
<?php echo $this->Form->text("User.localLoginPassword",array('class'=>'form-control'))."\n"; ?><br />
<?php echo $this->Form->error("User.localLoginPassword")."\n"; ?>
<?php echo $this->Form->hidden("User.localLoginPassword_old", array('value' => ''))."\n"; ?>
</div>
<div class="form-group">
	<div class="checkbox">
<?php echo $this->Form->checkbox("User.allowLocalLogin",
	array(
		'class'=>'form-control',
		'checked'=>false,
	)
); ?>
	</div>
	<label for="allowLocalLogin" class="control-label"><?php echo __('ローカル認証を許可する');?></label>
</div>

<div class="form-group">
	<label for="authority" class="control-label"><?php echo __('権限を選択'); ?></label><br />
<?php echo $this->Form->radio("User.authority",
	array(
		'0'=>__('一般ユーザー'),
		'1'=>__('監督者'),
		'9'=>__('システム管理者'),
	),
	array(
		'legend'=>false,
		'value'=>'0',
	)
); ?>
</div>
<div class="form-group">
	<label for="penID" class="control-label"><?php echo __('ペンID'); ?></label>
<?php echo $this->Form->text("User.penID")."\n"; ?><br />
<?php echo $this->Form->error("User.penID")."\n"; ?>
</div>
<div class="form-group">
	<div class="checkbox">
<?php echo $this->Form->checkbox("User.available",
	array(
		'class'=>'form-control',
		'checked'=>true,
	)

); ?>
	</div>
	<label for="available" class="control-label"><?php echo __('ユーザーを有効にする');?></label>
</div>
<?php echo $this->Form->submit(__('送信'),array('class'=>'btn btn-primary'))."\n"; ?><br />
<?php echo $this->Form->end(); ?>

	</div><!--panel body-->
</div><!--panel primary-->
</div><!--container-->