<div id="admcontents">
<div class="panel panel-primary">
	<div class="panel-heading">
		<h1 class="panel-title"><?php echo __('データベースバックアップ');?></h1>
	</div>
	<div class="panel-body">



<?php echo $this->Form->create('Post'); ?>

<div class="form-group">
	<label for="penID" class="control-label"><?php echo __('バックアップ対象'); ?></label>
	<p class="help-block"><?php echo __('データベース名');?> : <?php echo __($dbName);?></p>
<?php echo $this->Form->hidden("doBackup",array("value" => "1"))."\n"; ?>
</div>

<?php echo $this->Form->submit(__('送信'),array('class'=>'btn btn-primary'))."\n"; ?><br />

<div class="well">
■<?php echo __('結果');?><br />
<?php echo __('メッセージ');?> : <?php echo __($msg);?><br />
<?php echo __('保存ファイル');?> : <?php echo __($bkFullPath);?><br />
<?php echo $this->Form->end(); ?>
</div>

	</div><!--panel body-->
</div><!--panel primary-->
</div><!--container-->
