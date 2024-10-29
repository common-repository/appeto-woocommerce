<?php
class appeto_push_notification {

    public function send($device, $url, $title, $desc) {
        if($device == "android") {
            return array(
                'android' => $this->send_for_android($url, $title, $desc),
                'ios' => false
            );
        }
        else if($device == "ios") {
            return array(
                'android' => false,
                'ios' => $this->send_for_ios($url, $title, $desc)
            );
        }
        else {
            $android = $this->send_for_android($url, $title, $desc);
            $ios = $this->send_for_ios($url, $title, $desc);
            return array(
                'android' => $android,
                'ios' => $ios
            );
        }
    }

    private function send_for_android($url, $title, $desc) {
        /*$appId = get_option('appeto_cheshmak_app_id', '');
        $tokenKey = get_option('appeto_cheshmak_token', '');
        if($appId != '' and $tokenKey != '') {
            return $this->cheshmak($url, $title, $desc, $appId, $tokenKey);
        }*/
        $tokenKey = get_option('appeto_pushe_notify_key', '');
        $package = get_option('appeto_pushe_package', '');
        if($package != '' and $tokenKey != '') {
            return $this->pushe($url, $title, $desc, $tokenKey, $package);
        }
        return false;
    }

    private function pushe($url, $title, $desc, $token, $package) {
        $info = array(
            "app_ids" => array($package),
            "data" => array(
                "title" => $title,
                "content" => $desc
            )
        );

        if($url != '') {
            $info['data']['action'] = array(
                "url" => $url,
                "action_type" => "U"
            );
        }
        else {
            $info['data']['action'] = array(
                "url" => '',
                "action_type" => "A"
            );
            $info['data']['show_app'] = true;
        }

        $info = json_encode($info);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.push-pole.com/v2/messaging/notifications/");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $info);
        curl_setopt($ch, CURLOPT_POST, 1);
        $headers = array();
        $headers[] = "Authorization: Token ".$token;
        $headers[] = "Content-Type: application/json";
        $headers[] = "Accept: application/json";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            return false;
        }
        curl_close ($ch);
        $res = json_decode($result);
        if(isset($res->wrapper_id) and (int) $res->wrapper_id > 0) {
            return true;
        }
        return false;
    }

    private function cheshmak($url, $title, $desc, $appId, $tokenKey) {
        $openType = 'openProgram';
        if($url != '') {
            $openType = 'url';
        }
        $info = array(
            "afterOpenType" => $openType,
            "pushData" => array(
                "title" => $title,
                "shortMessage" => $desc
            )
        );
        if($openType == "url") {
            $info["pushData"]["url"] = $url;
        }

        if($appId == '' or $tokenKey == '') {
            return false;
        }
        $info = json_encode($info);
        $response = wp_remote_post("https://api.cheshmak.me/v1/push/app/".$appId."/send", array(
                'method' => 'POST',
                'timeout' => 45,
                'redirection' => 5,
                'httpversion' => '1.0',
                'blocking' => true,
                'headers' => array(
                    "cache-control" => "no-cache",
                    "Content-type" => "application/json;charset=UTF-8",
                    "key" => $tokenKey
                ),
                'body' => $info,
            )
        );
        if ( is_wp_error( $response ) ) {
            return false;
        } else {
            $response = json_decode($response["body"]);
        }
        if(isset($response->success) and $response->success) {
            return true;
        }
        return false;
    }

    private function send_for_ios($url, $title, $desc) {
        $headings = array(
            "en" => $title
        );
        $content = array(
            "en" => $desc
        );
        $appId = get_option('appeto_onesignal_app_id', '');
        $tokenKey = get_option('appeto_onesignal_token', '');
        $fields = array(
            'app_id' => $appId,
            'isIos' => true,
            'included_segments' => array('All'),
            'data' => array('url' => $url),
            'contents' => $content,
            'headings' => $headings
        );
        if($url != '') {
            $fields["url"] = $url;
        }

        $fields = json_encode($fields);

        $response = wp_remote_post("https://onesignal.com/api/v1/notifications", array(
                'method' => 'POST',
                'timeout' => 45,
                'redirection' => 5,
                'httpversion' => '1.0',
                'blocking' => true,
                'headers' => array("Content-type" => "application/json;charset=UTF-8",
                    "Authorization" => "Basic ".$tokenKey),
                'body' => $fields,
            )
        );

        if ( is_wp_error( $response ) ) {
            return false;
        } else {
            $response = json_decode($response["body"]);
        }

        if(isset($response->id) and $response->id != "") {
            return true;
        }
        return false;
    }

}