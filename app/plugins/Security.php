<?php
class Security extends Phalcon\Mvc\User\Plugin
{

	public function beforeExecuteRoute(Phalcon\Events\Event $event, Phalcon\Mvc\Dispatcher $dispatcher)
	{
		//获取用户的ID
		$role = "Guests";

		//获取用户的authtoken
		$userModel = new Users();
		$authToken = isset($_REQUEST['authToken'])?trim($_REQUEST['authToken']):'';
		$user_id = $this->cookies->get('gcsbbUid')->getValue();

		if( $authToken ){
			$userInfo = $userModel->getUserInfoByToken($authToken);
			if($userInfo){
				$user_id = $userInfo['user_id'];
				$this->cookies->get('gcsbbUid')->delete();
				$this->cookies->set('gcsbbUid', $user_id, time() + 15 * 86400);
				$this->cookies->send();
			}
		}

		if($user_id){
			$status = $this->member_service->getUserRoleStatus($user_id,1)->status;
			if($status == 1){
				$role = "Admins";
			}
		}else{
			$role = "Guests";
		}

		$controller = $dispatcher->getControllerName();
		$action = $dispatcher->getActionName();

		$acl = $this->acl;

		$allowed = $acl->isAllowed($role, $controller, $action);

		if ($allowed != Phalcon\Acl::ALLOW) {
			header("Location:".$this->config->web->loginUrl."?ret_url=".urlencode($this->config->web->siteUrl));
			return false;
		}


	}

}