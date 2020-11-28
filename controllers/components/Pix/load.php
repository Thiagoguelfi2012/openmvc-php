<?php

class Pix extends Controller {
    const ID_PAYLOAD_FORMAT_INDICATOR                 = '00';
    const ID_MERCHANT_ACCOUNT_INFORMATION             = '26';
    const ID_MERCHANT_ACCOUNT_INFORMATION_GUI         = '00';
    const ID_MERCHANT_ACCOUNT_INFORMATION_KEY         = '01';
    const ID_MERCHANT_ACCOUNT_INFORMATION_DESCRIPTION = '02';
    const ID_MERCHANT_CATEGORY_CODE                   = '52';
    const ID_TRANSACTION_CURRENCY                     = '53';
    const ID_TRANSACTION_AMOUNT                       = '54';
    const ID_COUNTRY_CODE                             = '58';
    const ID_MERCHANT_NAME                            = '59';
    const ID_MERCHANT_CITY                            = '60';
    const ID_ADDITIONAL_DATA_FIELD_TEMPLATE           = '62';
    const ID_ADDITIONAL_DATA_FIELD_TEMPLATE_TXID      = '05';
    const ID_CRC16                                    = '63';

    private $pixKey;
    private $description;
    private $merchantName;
    private $merchantCity;
    private $txId;
    private $amount;
    private $payload;

    public function setAmount($amount) {
        $this->amount = $amount;
        return $this;
    }

    public function setPixKey($pixKey) {
        $this->pixKey = $pixKey;
        return $this;
    }

    public function setDescription($description) {
        $this->description = $description;
        return $this;
    }

    public function setMerchantName($merchantName) {
        $this->merchantName = substr($merchantName, 0, 26);
        return $this;
    }

    public function setMerchantCity($merchantCity) {
        $this->merchantCity = $merchantCity;
        return $this;
    }

    public function setTxId($txId) {
        $this->txId = $txId;
        return $this;
    }

    private function getValue($id, $value) {
    	$size = str_pad(mb_strlen($value), 2, '0', STR_PAD_LEFT);
    	return $id.$size.$value;
    }

    private function getMerchantAccountInformation() {
    	$gui 		 = $this->getValue(self::ID_MERCHANT_ACCOUNT_INFORMATION_GUI, 'BR.GOV.BCB.PIX');
    	$key 		 = $this->getValue(self::ID_MERCHANT_ACCOUNT_INFORMATION_KEY, $this->pixKey);
    	$description = $this->getValue(self::ID_MERCHANT_ACCOUNT_INFORMATION_DESCRIPTION, $this->description);
    	return $this->getValue(self::ID_MERCHANT_ACCOUNT_INFORMATION, $gui.$key.$description);
    }

    private function getAdditionalDataFildTemplate() {
    	$txid = $this->getValue(self::ID_ADDITIONAL_DATA_FIELD_TEMPLATE_TXID, $this->txId);
    	return $this->getValue(self::ID_ADDITIONAL_DATA_FIELD_TEMPLATE, $txid);
    }

    private function getCRC16($payload) {
        $payload .= self::ID_CRC16 . '04';
        $polinomio = 0x1021;
        $resultado = 0xFFFF;
        if (($length = strlen($payload)) > 0) {
            for ($offset = 0; $offset < $length; $offset++) {
                $resultado ^= (ord($payload[$offset]) << 8);
                for ($bitwise = 0; $bitwise < 8; $bitwise++) {
                    if (($resultado <<= 1) & 0x10000) {
                        $resultado ^= $polinomio;
                    }
                    $resultado &= 0xFFFF;
                }
            }
        }
        return self::ID_CRC16 . '04' . strtoupper(dechex($resultado));
    }

    private function buildPayload() {
    	$this->payload = $this->getValue(self::ID_PAYLOAD_FORMAT_INDICATOR, '01');
    	$this->payload .= $this->getMerchantAccountInformation();
    	$this->payload .= $this->getValue(self::ID_MERCHANT_CATEGORY_CODE, "0000");
    	$this->payload .= $this->getValue(self::ID_TRANSACTION_CURRENCY, "986");
    	$this->payload .= $this->getValue(self::ID_TRANSACTION_AMOUNT, $this->amount);
    	$this->payload .= $this->getValue(self::ID_COUNTRY_CODE, "BR");
    	$this->payload .= $this->getValue(self::ID_MERCHANT_NAME, $this->merchantName);
    	$this->payload .= $this->getValue(self::ID_MERCHANT_CITY, $this->merchantCity);
    	$this->payload .= $this->getAdditionalDataFildTemplate();
    	$this->payload .= $this->getCRC16($this->payload);
    }

    public function getQrCodeHash() {
        if (empty($this->payload)) {
            $this->buildPayload();
        }
        return $this->payload;
    }

    public function getQrCodePng($output = null, $level = 0, $size = 8, $margin = 4, $back_color = 0xFFFFFF, $fore_color = 0x000000) {
        if (empty($this->payload)) {
            $this->buildPayload();
        }

        $this->load("components", "QrCode");

        if (!$output) {
            return $this->QrCode->png($this->payload, false, $level, $size, $margin);
        }

        $this->QrCode->png($this->payload, $output, $level, $size, $margin, false, $back_color, $fore_color);
    }
}