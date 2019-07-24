<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Plugin\Announcements\Entry;

/**
 * Class Model
 * @package ILIAS\Plugin\Announcements\Entry
 * @author Michael Jansen <mjansen@databay.de>
 */
class Model extends \ActiveRecord
{
	/**
	 * @var int
	 * @con_is_primary  true
	 * @con_sequence    true
	 * @con_is_unique   true
	 * @con_has_field   true
	 * @con_fieldtype   integer
	 * @con_length      4
	 */
	protected $id = 0;

	/**
	 * @var int
	 * @con_has_field   true
	 * @con_fieldtype   integer
	 * @con_length      4
	 */
	protected $creator_usr_id = 0;

	/**
	 * @var int
	 * @con_has_field   true
	 * @con_fieldtype   integer
	 * @con_length      4
	 */
	protected $last_modifier_usr_id = 0;

	/**
	 * @var int
	 * @con_has_field   true
	 * @con_fieldtype   integer
	 * @con_length      4
	 */
	protected $created_ts = 0;

	/**
	 * @var int
	 * @con_has_field   true
	 * @con_fieldtype   integer
	 * @con_length      4
	 */
	protected $last_modified_ts = 0;

	/**
	 * @var int
	 * @con_has_field   true
	 * @con_fieldtype   integer
	 * @con_length      4
	 */
	protected $publish_ts = 0;

	/**
	 * @var int
	 * @con_has_field   true
	 * @con_fieldtype   integer
	 * @con_length      4
	 */
	protected $expiration_ts = 0;

	/**
	 * @var string
	 *
	 * @con_has_field   true
	 * @con_fieldtype   text
	 * @con_is_notnull  true
	 * @con_length      255
	 */
	protected $title = '';

	/**
	 * @var string
	 * @con_has_field   true
	 * @con_fieldtype   clob
	 * @con_is_notnull  true
	 */
	protected $content = '';

	/**
	 * @inheritDoc
	 */
	public static function returnDbTableName() {
		return 'pl_announcements';
	}
}