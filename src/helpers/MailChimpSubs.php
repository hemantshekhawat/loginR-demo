<?php

/**
 * Created by PhpStorm.
 * User: Shekhawat
 * Date: 06/02/16
 * Time: 12:49 AM
 */


class MailChimpSubs
{
    private $_apiKey=MAILCHIMP_API_KEY;

    private $ApiCall=[
        'getLists'=>"https://us12.api.mailchimp.com/2.0/lists/list.json",
        'addSubscriber'=>"https://us12.api.mailchimp.com/2.0/lists/subscribe.json"
    ];

    public function getList($list){

        $response_body =$this->executeMailChimpApi($this->ApiCall['getLists']);
        if(!empty($response_body['data'])){
            foreach ($response_body['data'] as $item) {
                if(stristr($item['name'],$list)){
                    return $item['id'];
                }
            }
            return false;
        }else{
            return false;
        }
    }
    public function addSubscriber($data){
        if(!empty($data)){
            $subscriptionListId=$this->getList("Subscribers");
            if(!empty($subscriptionListId)){
                $counts=0;
                $TotalCounts=count($data['email']);
                foreach ($data['email'] as $item) {
                    $email = array("email" => $item);
                    $mergevars = array("FNAME" => $data['fname']);
                    $params = array("id" => $subscriptionListId, "email" => $email, "merge_vars" => $mergevars);

                    $mailChimpResponse = $this->executeMailChimpApi($this->ApiCall['addSubscriber'], $params);
                    if(!empty($mailChimpResponse) && isset($mailChimpResponse['email']) && isset($mailChimpResponse['leid'])){
                        $counts++;
                    }
                    echo "<pre> Subscription response";
                    print_r($mailChimpResponse);
                }
                if($counts == $TotalCounts){
                    return "all";
                }elseif($counts < $TotalCounts){
                    return "partial";
                }else{
                    return "none";
                }
            }else{
                return "listIdEmpty";
            }
        }else{
            return "emptyEmail";
        }
    }

    public function subscribe(){

    }

    public function executeMailChimpApi($url,$params=array()){
        $params['apikey']=$this->_apiKey;
        $params = json_encode($params);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        $response_body = curl_exec($ch);

        $response_body=json_decode($response_body,true);
        curl_close($ch);
        return $response_body;

    }
}