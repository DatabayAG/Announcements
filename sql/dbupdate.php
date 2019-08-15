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
        'created_ts' => [
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
        'last_modified_ts' => [
            'type'     => 'integer',
            'length'   => 4,
            'notnull'  => true,
            'default'  => 0
        ],
        'publish_ts' => [
            'type'     => 'integer',
            'length'   => 4,
        ],
        'publish_timezone' => [
            'type'     => 'text',
            'length'   => 100,
        ],
        'expiration_ts' => [
            'type'     => 'integer',
            'length'   => 4,
        ],
        'expiration_timezone' => [
            'type'     => 'text',
            'length'   => 100,
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
        'category' => [
            'type'    => 'integer',
            'length'  => 4,
            'notnull' => true,
            'default' => 0
        ],
        'fixed' => [
            'type'    => 'integer',
            'length'  => 1,
            'notnull' => true,
            'default' => 0
        ],
    ]);
    $ilDB->addPrimaryKey('pl_announcements', ['id']);
    $ilDB->createSequence('pl_announcements');
}
?>