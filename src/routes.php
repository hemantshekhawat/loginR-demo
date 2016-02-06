<?php
// Routes
use LoginRadiusSDK\LoginRadius;
use LoginRadiusSDK\LoginRadiusException;
use LoginRadiusSDK\SocialLogin\GetProvidersAPI;
use LoginRadiusSDK\SocialLogin\SocialLoginAPI;
//use LoginRadiusSDK\CustomerRegistration\UserAPI;
//use LoginRadiusSDK\CustomerRegistration\AccountAPI;
//use LoginRadiusSDK\CustomerRegistration\CustomObjectAPI;



$app->get('/', function ($request, $response) {
    // Render index view
    return $this->renderer->render($response, 'login.phtml');
});

$app->post('/login-callback', function ($request, $response, $args) {
    $this->logger->info("Callback ");
    $request_token=$request->getParams();
    try{


        $responseArr = ['status' => "failure", "msg" => "Something went wrong. Would you mind giving a try again?"];

        $socialLoginObject = new SocialLoginAPI (LR_API_KEY, LR_API_SECRET, array('authentication'=>false, 'output_format' => 'json'));
        $accesstoken = $socialLoginObject->exchangeAccessToken($request_token['token']);//$request_token loginradius token get from social/traditional interface after success authentication.
        $accesstoken = $accesstoken->access_token;
        $userProfileData = $socialLoginObject->getUserProfiledata($accesstoken);

        if(!empty($userProfileData->Email)) {

            $responseArr = ['status' => "success", "msg" => "Successfully logged in"];
            /*
             * Call MailChimp here
             */

            $userData = [
                'FNAME' => $userProfileData->FirstName,
                'LNAME' => $userProfileData->LastName
            ];

            foreach ($userProfileData->Email as $email) {
                $userData['EMAIL'] = $email->Value;
            }

            $mailChimpObj = new Mailchimp(MAILCHIMP_API_KEY);
            $mailChimpListsObj = new Mailchimp_Lists($mailChimpObj);//(MAILCHIMP_API_KEY);

            $allList=$mailChimpListsObj->getList();

            if(intval($allList["total"]) >= 1){

                $subscriberListId=null;
                foreach($allList['data'] as $lists){
                    if(stristr($lists['name'],"test")){
                        $subscriberListId=$lists['id'];
                    }
                    if(!empty($subscriberListId))
                        break;
                }

                if(!empty($subscriberListId)){
                    foreach ($userProfileData->Email as $email) {

                        try{
                            $data=$mailChimpListsObj->Subscribe($subscriberListId,array("email"=>trim($email->Value)),$userData);
                        }catch(Exception $exp){
                            $data=$mailChimpListsObj->updateMember($subscriberListId,array("email"=>trim($email->Value)),$userData);
                        }
                        if(empty($data['email'])){
                            $responseArr["subscription"] = ['status' => "failure", "msg" => "subscription failure"];
                        }else{
                            $responseArr["subscription"] = ['status' => "success", "msg" => "subscription success.  Please visit you email account {$email->Value} and verify your subscription."];
                        }
                    }
                }
            }

            /*
             * Alternate Implementation Using Directions from Login Radius Documentation
             */

//            $mailChimpObj = new MailChimpSubs();
//            $subscriptions = $mailChimpObj->addSubscriber($userData);
//
            /*
             * Response
             */

//            if(stristr($subscriptions,"all")) {
//                $responseArr["subscription"] = ['status' => "success", "msg" => "Successfully added to subscription list"];
//
//            }elseif(stristr($subscriptions,"partial")){
//                $responseArr["subscription"] = ['status' => "success", "msg" => "Added to subscription list but not all email IDs associated the social account"];
//
//            }elseif(stristr($subscriptions,"none")){
//                $responseArr["subscription"] = ['status' => "failure", "msg" => "failed to add you to subscription list"];
//
//            }elseif(stristr($subscriptions,"listidempty")){
//                $responseArr["subscription"] = ['status' => "failure", "msg" => "could not find the subscribers list"];
//
//            }elseif(stristr($subscriptions,"emptyemail")){
//                $responseArr["subscription"] = ['status' => "failure", "msg" => "could not find the subscribers list"];
//
//            }else{
//                $responseArr["subscription"] = ['status' => "failure", "msg" => "subscription failure"];
//
//            }
        }
    }
    catch (LoginRadiusException $e){
        $e->getMessage();
        $e->getErrorResponse();
    }
    $this->renderer->render($response, 'thankyou.phtml',["resp"=>$responseArr,"data"=>$userData]);
});
