<?php
/**
 * @var \App\View\AppView $this
 */
?>
<h1>TinyAuth Backend</h1>

<p>Make sure that these plugin controllers can never be accessed by anyone else than the "admin" role. You otherwise create security issues!</p>

<h2>Authentication</h2>

<p>What actions are public and which ones are protected?</p>

<?php
if (\TinyAuthBackend\Utility\AdapterConfig::isAllowEnabled()) {
	echo $this->Html->link('Manage Allow rules', ['controller' => 'Allow', 'action' => 'index']);
} else {
	echo '<i>disabled</i>';
}
?>


<h2>Authorization</h2>

<p>Who can access which protected action?</p>

<?php
if (\TinyAuthBackend\Utility\AdapterConfig::isAclEnabled()) {
	echo $this->Html->link('Manage ACL rules', ['controller' => 'Acl', 'action' => 'index']);
} else {
	echo '<i>disabled</i>';
}
?>
