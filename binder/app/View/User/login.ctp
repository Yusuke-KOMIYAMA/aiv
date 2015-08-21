<h1>ログインフォーム</h1>
<h2>独自認証ログイン</h2>
<?php echo $this->Form->create(false,array('type'=>'post','action'=>'./login')); ?>
<?php echo $this->Form->text("localLoginForm.loginID")."\n"; ?><br />
<?php echo $this->Form->password("localLoginForm.password")."\n"; ?><br />
<?php echo $this->Form->submit("login")."\n"; ?><br />
<?php echo $this->Form->end(); ?>


<?php echo $message ?><br />

<h2>統合システムSSO</h2>
<p>仕様待ち</p>
