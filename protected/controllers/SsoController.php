<?php

class SsoController extends Controller
{

	public function actions()
	{
		return array();
	}


	public function actionIndex()
	{
		$this->redirect(Yii::app()->homeUrl);
	}
		

	public function actionTwitterLogin(){
		//JUST TO BUILD A SESSION	
		$isguest = Yii::app()->user->getIsGuest();
		//JUST TO BUILD A SESSION		
		
		//grab twitter object and request token
		$twitter = Yii::app()->twitter->getTwitter();	
		$request_token = $twitter->getRequestToken();
		
		//set some session info
		$_SESSION['oauth_token'] = $token = $request_token['oauth_token'];
		$_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];

		if($twitter->http_code == 200){
			//get twitter connect url
			$url = $twitter->getAuthorizeURL($token);
			//send them
			$this->redirect($url);
		}else{
			//error here
			$this->redirect(Yii::app()->homeUrl);
		}
	
	}
	
	

	public function actionTwitterCallBack()	{
		//JUST TO BUILD A SESSION	
		$isguest = Yii::app()->user->getIsGuest();
		//JUST TO BUILD A SESSION
	
		/* SOME COMMENTS FROM TWITTER API EXAMPLES
	
	
		/* If the oauth_token is old redirect to the connect page. */
		if (isset($_REQUEST['oauth_token']) && $_SESSION['oauth_token'] !== $_REQUEST['oauth_token']) {
			$_SESSION['oauth_status'] = 'oldtoken';
			//header('Location: ./clearsessions.php');
			$this->redirect(Yii::app()->homeUrl);
		}

		/* Create TwitteroAuth object with app key/secret and token key/secret from default phase */
		$twitter = Yii::app()->twitter->getTwitterTokened($_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);	
		
		/* Request access tokens from twitter */
		$access_token = $twitter->getAccessToken($_REQUEST['oauth_verifier']);
	
		/* Save the access tokens. Normally these would be saved in a database for future use. */
		$_SESSION['access_token'] = $access_token;

		/* Remove no longer needed request tokens */
		unset($_SESSION['oauth_token']);
		unset($_SESSION['oauth_token_secret']);

		if (200 == $twitter->http_code) {
			/* The user has been verified and the access tokens can be saved for future use */
			$_SESSION['status'] = 'verified';

			//get an access twitter object
			$twitter = Yii::app()->twitter->getTwitterTokened($access_token['oauth_token'],$access_token['oauth_token_secret']);

			//get user details
			$twuser= $twitter->get("account/verify_credentials");
	
			
			//get matching twid if exists
			$olduser=User::model()->notsafe()->findByAttributes(array('twid'=>$twuser->id));
			
			//this could be better
			$fakepassword = "salt".$twuser->id;
			
			//is this a new twitter accout			
			if($olduser ===null){
				//yes
				
				$model = new User;			//make the user module
				$profile=new Profile;		//since im using the yii user module i have a profile model as well
				$profile->regMode = true;
							
				//lets make some general information			
				$userdata=array(
					"username"=>"tw_".$twuser->screen_name,
					"password"=>$fakepassword,
					"email"=>$twuser->screen_name.'@twitter.com',	//we cant get email from twitter								
					
				);

									
				$model->attributes=$userdata;
				
				

				$model->password=UserModule::encrypting($fakepassword);	//set the password				
				$model->lastvisit=$model->createtime=time();			//set create andvisit time
				$model->superuser = 0;									//probably not
				$model->status = 1;										//active
				$model->regmethod = "twitter";							//stats for me
				$model->twid = $twuser->id;						//set the twid
				
				
				//these are profile feilds (you can probably omit)
				$twname = explode(" ",$twuser->name);
				$profile->firstname = $twname[0];//$user_info['first_name'];
				$profile->lastname =  $twname[1];//$user_info['last_name'];
				$profile->birthday = "2000-01-01"; 
				

				//try and save the new user
				if ($model->save()) {
					//word it saved 
					//again since i have a profile for each user aswell
					$profile->user_id=$model->id;
					$profile->save();
					
					//now lets log them in					
					$identity=new UserIdentity($userdata['username'],$fakepassword);
					$identity->authenticate();
					$duration=3600*24*30;
					Yii::app()->user->login($identity,$duration);
					
					//e.t. phone home
					$this->redirect(Yii::app()->homeUrl);
				}else{
					echo "FAIL<br><pre>";
					print_r($model->getErrors());						
					die();
				}
			}else{
			
				//this user exists so lets log them in
				$identity=new UserIdentity("tw_".$twuser->screen_name,$fakepassword);
				$identity->authenticate();
				$duration=3600*24*30;
				Yii::app()->user->login($identity,$duration);
				//e.t. phone home
				$this->redirect(Yii::app()->homeUrl);
			}
			
			
			
		} else {
			/* Save HTTP status for error dialog on connnect page.*/
			//header('Location: /clearsessions.php');
			$this->redirect(Yii::app()->homeUrl);
		}
		
	}
	
}












