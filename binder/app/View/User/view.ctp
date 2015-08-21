<div id="admcontents"
<div class="panel panel-primary">
	<div class="panel-heading">
		<h1 class="panel-title"><?php echo __('ユーザー参照');?></h1>
	</div>
	<div class="panel-body">

<table class="table table-striped">
<tr>
<th width="20%">key</th>
<th>val</th>
</tr>

<?php 
//echo "<pre>";
//print_r($data);
//echo "</pre>";
foreach($data['User'] as $key => $val) {
	if($key == "localLoginPassword"){
		continue;
	}

	echo "<tr>";
	echo "<td>";
	echo $key;
	echo "</td>";
	echo "<td>";
	echo h($val);
	echo "</td>";
	echo "</tr>";

}

echo "<tr>";
echo "<td>";
echo __('');
echo "</td>";
echo "<td>";
echo $this->Html->link(__('編集'),
	array('controller' => 'users', 'action' => 'edit', $data['User']['id']),array('class' => 'btn btn-outline btn-primary'));
echo "</td>";
echo "</tr>";

 ?>
</table>
	</div><!--table responsive-->
	</div><!--panel body-->
</div><!--panel primary-->
</div><!--container-->
