<?php
/**
 * Handles displaying member's public profiles.
 *
 * @package    silverstripe-memberprofiles
 * @subpackage controllers
 */
class MemberProfileViewer extends Page_Controller {

	public static $url_handlers = array(
		'' => 'handleList'
	);

	protected $parent, $name;

	/**
	 * @param RequestHandler $parent
	 * @param string $name
	 */
	public function __construct($parent, $name) {
		$this->parent = $parent;
		$this->name   = $name;

		parent::__construct();
	}

	/**
	 * Displays a list of all members on the site that belong to the selected
	 * groups.
	 *
	 * @return string
	 */
	public function handleList($request) {
		if (!$this->parent->AllowProfileViewing) {
			return ErrorPage::response_for(404);
		}

		$sort = $request->getVar('sort');

		if ($sort && singleton('Member')->hasDatabaseField($sort)) {
			$sort = sprintf('"%s"', Convert::raw2sql($sort));
		} else {
			$sort = '"ID"';
		}

		$groups = $this->parent->Groups();
		$fields = $this->parent->Fields('"MemberListVisible" = 1');

		// List all members that are in at least one of the groups on the
		// parent page.
		if (count($groups)) {
			$groups = implode(',' , array_keys($groups->map()));
			$filter = "\"Group_Members\".\"GroupID\" IN ($groups)";
			$join   = 'LEFT JOIN "Group_Members" ' .
				'ON "Member"."ID" = "Group_Members"."MemberID"';
		} else {
			$filter = $join = null;
		}

		$members = DataObject::get('Member', $filter, $sort, $join, array(
			'start' => $this->getPaginationStart(),
			'limit' => 25
		));

		if ($members && $fields) foreach ($members as $member) {
			$data = new DataObjectSet();

			foreach ($fields as $field) {
				$data->push(new ArrayData(array(
					'MemberID' => $member->ID,
					'Name'     => $field->MemberField,
					'Title'    => $field->Title,
					'Value'    => $member->{$field->MemberField},
					'Sortable' => $member->hasDatabaseField($field->MemberField)
				)));
			}

			$member->setField('Fields', $data);
		}

		$this->data()->Title  = _t('MemberProfiles.MEMBERLIST', 'Member List');
		$this->data()->Parent = $this->parent;

		$controller = $this->customise(array(
			'Members' => $members
		));
		return $controller->renderWith(array(
			'MemberProfileViewer_list', 'MemberProfileViewer', 'Page'
		));
	}

	/**
	 * @return int
	 */
	public function getPaginationStart() {
		if ($start = $this->request->getVar('start')) {
			if (ctype_digit($start) && (int) $start > 0) return (int) $start;
		}

		return 0;
	}

	/**
	 * @return string
	 */
	public function Link() {
		return Controller::join_links($this->parent->Link(), $this->name);
	}

}