
<h2>ユーザー参照</h2>

<table border="1">
<tr>
<th>key</th>
<th>val</th>
</tr>

<?php 
//echo "<pre>";
//print_r($data);
//echo "</pre>";
foreach($data['User'] as $key => $val) {
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
echo "編集";
echo "</td>";
echo "<td>";
echo $this->Html->link("編集",
	array('controller' => 'users', 'action' => 'edit', $data['User']['id']));
echo "</td>";
echo "</tr>";

 ?>
