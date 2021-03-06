<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace Components\Members\Models\Quota;

use Hubzero\Database\Relational;
use User;
use Lang;

include_once __DIR__ . DS . 'group.php';
include_once __DIR__ . DS . 'log.php';

/**
 * Quota class model
 */
class Category extends Relational
{
	/**
	 * The table namespace
	 *
	 * @var  string
	 */
	protected $namespace = 'users_quotas';

	/**
	 * The table to which the class pertains
	 *
	 * This will default to #__{namespace}_{modelName} unless otherwise
	 * overwritten by a given subclass. Definition of this property likely
	 * indicates some derivation from standard naming conventions.
	 *
	 * @var  string
	 */
	protected $table = '#__users_quotas_classes';

	/**
	 * Default order by for model
	 *
	 * @var  string
	 */
	public $orderBy = 'id';

	/**
	 * Default order direction for select queries
	 *
	 * @var  string
	 */
	public $orderDir = 'asc';

	/**
	 * Fields and their validation criteria
	 *
	 * @var  array
	 */
	protected $rules = array(
		'alias' => 'notempty'
	);

	/**
	 * Sets up additional custom rules
	 *
	 * @return  void
	 */
	public function setup()
	{
		$this->addRule('alias', function($data)
		{
			if (!$data['alias'])
			{
				return Lang::txt('COM_MEMBERS_QUOTA_CLASS_MUST_HAVE_ALIAS');
			}

			if ($data['alias'] == 'custom')
			{
				return Lang::txt('COM_MEMBERS_QUOTA_CLASS_CUSTOM');
			}

			if (!$data['id'])
			{
				$row = self::all()
					->whereEquals('alias', $data['alias'])
					->row();

				if ($row->get('id'))
				{
					return Lang::txt('COM_MEMBERS_QUOTA_CLASS_NON_UNIQUE_ALIAS');
				}
			}

			return false;
		});
	}

	/**
	 * Get a list of groups
	 *
	 * @return  object
	 */
	public function groups()
	{
		return $this->oneToMany('Components\Members\Models\Quota\Group', 'class_id');
	}

	/**
	 * Override save to add logging
	 *
	 * @return  boolean
	 */
	public function save()
	{
		$action = ($this->get('id') ? 'modify' : 'add');

		$result = parent::save();

		if ($result)
		{
			$log = Log::blank();
			$log->set('object_type', 'class');
			$log->set('object_id', $this->get('id'));
			$log->set('name', $this->get('alias'));
			$log->set('action', $action);
			$log->set('actor_id', User::get('id'));
			$log->set('soft_blocks', $this->get('soft_blocks'));
			$log->set('hard_blocks', $this->get('hard_blocks'));
			$log->set('soft_files', $this->get('soft_files'));
			$log->set('hard_files', $this->get('hard_files'));
			$log->save();
		}

		return $result;
	}

	/**
	 * Override destroy to add logging
	 *
	 * @return  boolean
	 */
	public function destroy()
	{
		foreach ($this->groups()->rows() as $group)
		{
			if (!$group->destroy())
			{
				$this->addError($group->getError());
				return false;
			}
		}

		$result = parent::destroy();

		if ($result)
		{
			$log = Log::blank();
			$log->set('object_type', 'class');
			$log->set('object_id', $this->get('id'));
			$log->set('name', $this->get('alias'));
			$log->set('action', 'delete');
			$log->set('actor_id', User::get('id'));
			$log->set('soft_blocks', $this->get('soft_blocks'));
			$log->set('hard_blocks', $this->get('hard_blocks'));
			$log->set('soft_files', $this->get('soft_files'));
			$log->set('hard_files', $this->get('hard_files'));
			$log->save();
		}

		return $result;
	}

	/**
	 * Set group IDs
	 *
	 * @param   array  $groups
	 * @return  boolean
	 */
	public function setGroupIds($groups=array())
	{
		if (!is_array($groups))
		{
			$groups = array($groups);
		}

		$groups = array_map('intval', $groups);

		// Clear old records
		foreach ($this->groups()->rows() as $group)
		{
			if (!$group->destroy())
			{
				$this->addError($group->getError());
				return false;
			}
		}

		// Save new records
		foreach ($groups as $group)
		{
			$entry = Group::blank();
			$entry->set('class_id', $this->get('id'));
			$entry->set('group_id', $group);

			if (!$entry->save())
			{
				$this->addError($entry->getError());
				return false;
			}
		}

		return true;
	}

	/**
	 * Get the default entry
	 *
	 * @return  object
	 */
	public static function defaultEntry()
	{
		return self::all()
			->whereEquals('alias', 'default')
			->row();
	}
}
