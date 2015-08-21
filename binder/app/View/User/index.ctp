<div id="admcontents">
<div class="panel panel-primary">
	<div class="panel-heading">
		<h1 class="panel-title"><?php echo __('ユーザー一覧');?></h1>
	</div>
	<div class="panel-body">

<?php echo $this->Html->link(__('登録'),
array('controller' => 'users', 'action' => 'add'),array('class' => 'btn btn-outline btn-primary')); ?>

	<div class="table-responsive">
<table class="table table-striped">
<tr>
<th><?php echo __('ユーザーID');?></th>
<th><?php echo __('ユーザー名');?></th>
<th><?php echo __('研究室ID(未使用)');?></th>
<th><?php echo __('SSOログインID');?></th>
<th><?php echo __('ローカルログインID');?></th>
<th><?php echo __('ローカルログイン許可');?></th>
<th><?php echo __('ペンID');?></th>
<th><?php echo __('有効');?></th>
<th><?php echo __('権限');?></th>
<th><?php echo __('有効');?></th>
<th><?php echo __('参照');?></th>
<th><?php echo __('編集');?></th>
</tr>
<?php
//echo "<pre>";
//print_r($users);
//echo "</pre>";

foreach($users as $key => $val) {
	foreach($val as $key2 => $val2) {
		echo "<tr>";
		foreach($val2 as $key3 => $val3) {
			if($key3 == "localLoginPassword"){
				continue;
			}
			echo "<td>";
			echo $val3;
			echo "</td>";
		}
		echo "<td>";
        echo $this->Html->link(__('参照'),
			array('controller' => 'users', 'action' => 'view', $val2['id']),array('class' => 'btn btn-outline btn-primary'));
		echo "</td>";
		echo "<td>";
        echo $this->Html->link(__('編集'),
			array('controller' => 'users', 'action' => 'edit', $val2['id']),array('class' => 'btn btn-outline btn-primary'));
		echo "</td>";
		echo "</tr>";
	}
}
 ?>
</table>
	</div><!--table responsive-->
	</div><!--panel body-->
</div><!--panel primary-->
</div><!--container-->