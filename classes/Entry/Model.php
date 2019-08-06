<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Plugin\Announcements\Entry;

/**
 * Class Model
 * @package ILIAS\Plugin\Announcements\Entry
 * @author  Michael Jansen <mjansen@databay.de>
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
	 * @con_is_notnull  true
	 * @con_length      4
	 */
	protected $creator_usr_id = 0;

	/**
	 * @var int
	 * @con_has_field   true
	 * @con_fieldtype   integer
	 * @con_is_notnull  true
	 * @con_length      4
	 */
	protected $last_modifier_usr_id = 0;

	/**
	 * @var int
	 * @con_has_field   true
	 * @con_fieldtype   integer
	 * @con_is_notnull  true
	 * @con_length      4
	 */
	protected $created_ts = 0;

	/**
	 * @var int
	 * @con_has_field   true
	 * @con_fieldtype   integer
	 * @con_is_notnull  true
	 * @con_length      4
	 */
	protected $last_modified_ts = 0;

	/**
	 * @var int
	 * @con_has_field   true
	 * @con_fieldtype   integer
	 * @con_is_notnull  true
	 * @con_length      4
	 */
	protected $publish_ts = 0;

	/**
	 * @var \DateTimeImmutable
	 * @con_has_field   true
	 * @con_fieldtype   timestamp
	 * @con_is_notnull  true
	 */
	protected $publish_datetime;

	/**
	 * @var string
	 * @con_has_field   true
	 * @con_fieldtype   text
	 * @con_is_notnull  true
	 * @con_length      100
	 */
	protected $publish_timezone = '';

	/**
	 * @var \DateTimeImmutable
	 * @con_has_field   false
	 */
	protected $publish_on;

	/**
	 * @var int
	 * @con_has_field   true
	 * @con_fieldtype   integer
	 * @con_is_notnull  true
	 * @con_length      4
	 */
	protected $expiration_ts = 0;

	/**
	 * @var string
	 * @con_has_field   true
	 * @con_fieldtype   timestamp
	 * @con_is_notnull  true
	 */
	protected $expiration_datetime = '';

	/**
	 * @var \DateTimeImmutable
	 * @con_has_field   false
	 */
	protected $expired_on;

	/**
	 * @var string
	 * @con_has_field   true
	 * @con_fieldtype   text
	 * @con_is_notnull  true
	 * @con_length      100
	 */
	protected $expiration_timezone = '';

    /**
     * @var int
     * @con_has_field   true
     * @con_fieldtype   integer
     * @con_is_notnull  true
     * @con_length      1
     */
    protected $is_room_change = 0;

	/**
	 * @var string
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
	public static function returnDbTableName()
	{
		return 'pl_announcements';
	}

	/**
	 * @inheritDoc
	 */
	public function buildFromArray(array $array)
	{
		$value = parent::buildFromArray($array);

		$this->publish_on = new \DateTimeImmutable(
			$this->publish_datetime,
			new \DateTimeZone($this->publish_timezone)
		);

		$this->expired_on = new \DateTimeImmutable(
			$this->expiration_datetime,
			new \DateTimeZone($this->expiration_timezone)
		);

		return $value;
	}

	/**
	 * @inheritDoc
	 */
	public function read()
	{
		parent::read();

		$this->publish_on = new \DateTimeImmutable(
			$this->publish_datetime,
			new \DateTimeZone($this->publish_timezone)
		);

		$this->expired_on = new \DateTimeImmutable(
			$this->expiration_datetime,
			new \DateTimeZone($this->expiration_timezone)
		);
	}

	/**
	 * @inheritDoc
	 */
	public function create()
	{
		$this->publish_datetime    = $this->publish_on->format('Y-m-d H:i:s');
		$this->publish_timezone    = $this->publish_on->getTimezone()->getName();
		$this->expiration_datetime = $this->expired_on->format('Y-m-d H:i:s');
		$this->expiration_timezone = $this->expired_on->getTimezone()->getName();

		parent::create();
	}

	/**
	 * @inheritDoc
	 */
	public function update()
	{
		$this->publish_datetime    = $this->publish_on->format('Y-m-d H:i:s');
		$this->publish_timezone    = $this->publish_on->getTimezone()->getName();
		$this->expiration_datetime = $this->expired_on->format('Y-m-d H:i:s');
		$this->expiration_timezone = $this->expired_on->getTimezone()->getName();

		parent::update();
	}

	/**
	 * @return \DateTimeImmutable
	 */
	public function getPublishOn() : \DateTimeImmutable
	{
		return $this->publish_on;
	}

	/**
	 * @return \DateTimeImmutable
	 */
	public function getExpiredOn() : \DateTimeImmutable
	{
		return $this->expired_on;
	}

	/**
	 * @param \DateTimeImmutable $publish_on
	 */
	public function setPublishOn(\DateTimeImmutable $publish_on)
	{
		$this->publish_on = $publish_on;
	}

	/**
	 * @param \DateTimeImmutable $expired_on
	 */
	public function setExpiredOn(\DateTimeImmutable $expired_on)
	{
		$this->expired_on = $expired_on;
	}
}