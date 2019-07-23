<#1>
<?php
/** @var $ilDB \ilDBInterface */
if (!$ilDB->tableExists('pl_announcements')) {
	$ilDB->createTable('pl_announcements', [
		'id' => [
			'type'     => 'integer',
			'length'   => 4,
			'notnull'  => true,
			'default'  => 0
		],
		'creator_usr_id' => [
			'type'     => 'integer',
			'length'   => 4,
			'notnull'  => true,
			'default'  => 0
		],
		'last_modifier_usr_id' => [
			'type'     => 'integer',
			'length'   => 4,
			'notnull'  => true,
			'default'  => 0
		],
		'created_ts' => [
			'type'     => 'integer',
			'length'   => 4,
			'notnull'  => true,
			'default'  => 0
		],
		'last_modified_ts' => [
			'type'     => 'integer',
			'length'   => 4,
			'notnull'  => true,
			'default'  => 0
		],
		'publish_ts' => [
			'type'     => 'integer',
			'length'   => 4,
			'notnull'  => true,
			'default'  => 0
		],
		'expiration_ts' => [
			'type'     => 'integer',
			'length'   => 4,
			'notnull'  => true,
			'default'  => 0
		],
		'title' => [
			'type'    => 'text',
			'length'  => 255,
			'notnull' => true
		],
		'content' => [
			'type'    => 'clob',
			'notnull' => true
		],
	]);
	$ilDB->addPrimaryKey('pl_announcements', ['id']);
	$ilDB->createSequence('pl_announcements');
}
?>