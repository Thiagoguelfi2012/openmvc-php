
<?PHP

class Onesignal extends Controller {

    public $app_id = null;
    public $api_key = null;

    public function init() {
        
    }

    public function sendMessage($message) {
        if (empty($this->app_id) || empty($this->api_key)) {
            die('Set $this->Onesignal->app_id and $this->Onesignal->api_key variables');
        }

        $content = array(
            "en" => $message
        );

        $fields = array(
            'app_id' => $this->app_id,
            'included_segments' => array('All'),
            'data' => array("foo" => "bar"),
            'large_icon' => "ic_launcher_round.png",
            'contents' => $content
        );

        $fields = json_encode($fields);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8',
            'Authorization: Basic ' . $this->api_key));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

}

//$this->load("components", "Onesignal");
//$this->Onesignal->app_id = ONESIGNAL_APP_IP;
//$this->Onesignal->api_key = ONESIGNAL_API_KEY;
//$this->Onesignal->sendMessage("teste AGORA FOI");
