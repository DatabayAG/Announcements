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
<#2>
<?php
if ($ilDB->tableExists('pl_announcements')) {
	if (!$ilDB->tableColumnExists('pl_announcements', 'publish_datetime')) {
		$ilDB->addTableColumn(
			'pl_announcements',
			'publish_datetime',
			[
				'type'    => 'timestamp',
				'notnull' => true,
			]
		);
	}

	if (!$ilDB->tableColumnExists('pl_announcements', 'publish_timezone')) {
		$ilDB->addTableColumn(
			'pl_announcements',
			'publish_timezone',
			[
				'type'    => 'text',
				'notnull' => true,
				'length' => 100,
			]
		);
	}
}
?>
<#3>
<?php
if ($ilDB->tableExists('pl_announcements')) {
	if (!$ilDB->tableColumnExists('pl_announcements', 'expiration_datetime')) {
		$ilDB->addTableColumn(
			'pl_announcements',
			'expiration_datetime',
			[
				'type'    => 'timestamp',
				'notnull' => true,
			]
		);
	}

	if (!$ilDB->tableColumnExists('pl_announcements', 'expiration_timezone')) {
		$ilDB->addTableColumn(
			'pl_announcements',
			'expiration_timezone',
			[
				'type'    => 'text',
				'notnull' => true,
				'length' => 100,
			]
		);
	}
}
?>