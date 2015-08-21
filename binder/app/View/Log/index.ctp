<div id="admcontents">
<div class="panel panel-primary">
	<div class="panel-heading">
		<h1 class="panel-title"><?php echo __('ログ一覧');?></h1>
	</div>
	<div class="panel-body">


<?php 
echo $this->Form->create(false, array('type'=>'post','action'=>$this->action));
?>
<div class="form-group">
	<div class="form-inline">
<?php 
echo $this->Form->input('logDateFrom', array(
	'class'=>'form-control',
    'type' => 'date',

));
?>
<?php echo $this->Form->hidden("User.id")."\n"; ?>
	</div>
</div>

<div class="form-group">
	<div class="form-inline">
<?php 
echo $this->Form->input('logDateTo', array(
	'class'=>'form-control',
    'type' => 'date',
));
?>
	</div>
</div>

<div class="form-group">
	<label for="category" class="control-label"><?php echo __('種別'); ?></label><br />
<?php 
$options = array('SYS' => 'SYS', 'INFO' => 'INFO');
echo $this->Form->select('category', $options, array('class'=>'form-control','empty' => 'ALL','escape' => false));
?>
<div>
<div class="form-group">
	<label for="userId" class="control-label"><?php echo __('ユーザーID'); ?></label><br />
<?php 
echo $this->Form->text('userId', array('class'=>'form-control'));
echo $this->Form->error('userId');
?>
<div>
<?php
$value = "<i data-placement='top' data-toggle='tooltip' class='fa fa-list fa-fw'></i>" . __('検索') ;
echo $this->Form->button($value, array('type' => 'submit','class'=> 'btn btn-outline btn-primary'));

echo $this->Form->end(__(''));
 ?>

<br />
<?php
if(count($logs) > 0){
	echo "<br />";
	echo $this->Html->link(__('CSVダウンロード'),array('controller' => 'logs', 'action' => 'download'),array('class' => 'btn btn-outline btn-primary'));
}
?>
<br />

	<div class="table-responsive">
<table class="table table-striped">
<tr>
<th><?php echo __('ログID');?></th>
<th><?php echo __('日時');?></th>
<th><?php echo __('種別');?></th>
<th><?php echo __('レベル');?></th>
<th><?php echo __('メッセージID');?></th>
<th><?php echo __('メッセージ');?></th>
<th><?php echo __('ユーザーID');?></th>
<th><?php echo __('接続元IPアドレス');?></th>
</tr>
<?php
//echo "<pre>";
//print_r($logs);
//echo "</pre>";

foreach($logs as $key => $val) {
	foreach($val as $key2 => $val2) {
		echo "<tr>";
		foreach($val2 as $key3 => $val3) {
			echo "<td>";
			echo $val3;
			echo "</td>";
		}
	}
}
 ?>
</table>

	</div><!--table responsive-->
	</div><!--panel body-->
</div><!--panel primary-->
</div><!--container-->
