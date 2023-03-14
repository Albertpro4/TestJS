<?php


// обработка отправки формы
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $form = new Form($_POST['name'], $_POST['email'], $_POST['phone'], $_POST['price']);
    if ($form->validate()) {
        $amo = new AmoCRM($client_id, $client_secret, $redirect_uri);
        $amo->auth($code);
        $contact_id = $amo->createContact($form->getName(), $form->getEmail(), $form->getPhone());
        $amo->createDeal($contact_id, $form->getPrice());
        // вывод сообщения об успешной отправке заявки
    }
    
}



class AmoCRM {
  private $client_id;
  private $client_secret;
  private $redirect_uri;
  private $access_token;

  public function __construct($client_id, $client_secret, $redirect_uri) {
    $this->client_id = $client_id;
    $this->client_secret = $client_secret;
    $this->redirect_uri = $redirect_uri;
  }

  private function request($url, $params = array(), $method = 'GET') {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
    curl_setopt($ch, CURLOPT_URL, $url);

    if ($method == 'POST') {
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    }

    if ($method == 'PATCH') {
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
      curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
    }

    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      'Content-Type: application/json',
      'Authorization: Bearer ' . $this->access_token
    ));

    $result = curl_exec($ch);

    curl_close($ch);

    return json_decode($result, true);
  }

  private function authorize($code) {
    $params = array(
      'client_id' => $this->client_id,
      'client_secret' => $this->client_secret,
      'grant_type' => 'authorization_code',
      'code' => $code,
      'redirect_uri' => $this->redirect_uri
    );

    $result = $this->request('https://oauth2.amocrm.ru/access_token', $params, 'POST');

    if (isset($result['access_token'])) {
      $this->access_token = $result['access_token'];
      return true;
    }

    return false;
  }

  public function auth($code) {
    return $this->authorize($code);
  }

  public function createContact($name, $email, $phone) {
    $params = array(
      'add' => array(
        array(
          'name' => $name,
          'custom_fields' => array(
            array(
              'id' => 123456, // ID поля "Email"
              'values' => array(
                array(
                  'value' => $email,
                  'enum' => 'WORK'
                )
              )
            ),
            array(
              'id' => 123457, // ID поля "Телефон"
              'values' => array(
                array(
                  'value' => $phone,
                  'enum' => 'WORK'
                )
              )
            )
          )
        )
      )
    );

    $result = $this->request('https://api.amocrm.ru/v4/contacts', $params, 'POST');

  }

