<?php
 
// ヘッダー行設定
$this->Csv->addRow($th);
foreach($td as $t) {
$this->Csv->addField($t['Log']['id']);
 
$this->Csv->addField($t['Log']['logDate']);

$this->Csv->addField($t['Log']['category']);
 
$this->Csv->addField($t['Log']['level']);

$this->Csv->addField($t['Log']['messageID']);
 
$this->Csv->addField($t['Log']['message']);

$this->Csv->addField($t['Log']['userId']);

$this->Csv->addField($t['Log']['remoteAddress']);
 
// 行の終わりを宣言
$this->Csv->endRow();
}
$this->Csv->setFilename($filename);
echo $this->Csv->render(true, 'sjis', 'utf-8');