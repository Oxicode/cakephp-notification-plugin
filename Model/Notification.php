<?php
App::uses('NotificationAppModel', 'Notification.Model');
/**
 * Notification Model
 *
 * @property User $User
 * @property Subject $Subject
 */
class Notification extends NotificationAppModel {

	/**
	 * Display field
	 *
	 * @var string
	 */
	public $displayField = 'type';

	/**
	 * Validation rules
	 *
	 * @var array
	 */
	public $validate = array(
		'type' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'read' => array(
			'boolean' => array(
				'rule' => array('boolean'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);

	public $order = array('Notification.created DESC');

	/**
	 * belongsTo associations
	 *
	 * @var array
	 */
	// public $belongsTo = array(
	// 	'User' => array(
	// 		'className' => 'User',
	// 		'foreignKey' => 'user_id',
	// 	)
	// );


	/**
	 * hasMany associations
	 *
	 * @var array
	 */
	public $hasMany = array(
		'Subject' => array(
			'className'  => 'Notification.Subject',
			'foreignKey' => 'notification_id',
			'dependent'  => true,
		)
	);

	public function getUnread($user_id = null, $limit = false){
		return $this->find('count', array(
			'conditions' => array(
				'Notification.read' => false,
				'Notification.user_id' => $user_id
			),
			'limit' => $limit,
		));
	}

	public function getLast($user_id, $limit = 20){
		$results = $this->find('all', array(
			'conditions' => array(
				'Notification.user_id' => $user_id,
				"Notification.created >" => date('Y-m-d', strtotime("-1 weeks"))
			),
			'limit' => $limit,
		));
		$ids = Set::classicExtract($results, '{n}.Notification.id');
		$subjects = $this->Subject->findAllByNotificationId($ids);
		foreach ($results as $k => $result) {
			$s = Set::extract('/.[notification_id='.$result['Notification']['id'].']', $subjects);
			foreach ($s as $t) {
				$results[$k][$t['model']] = $t[$t['model']];
			}
		}
		return $results;
	}

	public function markAllAsRead($user_id){
		return $this->updateAll(
			array('Notification.read' => true),
			array('Notification.user_id' => $user_id)
		);
	}

}
