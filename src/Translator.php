<?php

namespace Prihod\Translate;

class Translator
{
    const BASE_URL = 'https://pts.promt.ru/pts/Services/v1/rest.svc/';
    const MESSAGE_UNKNOWN_ERROR = 'Unknown error';
    const MESSAGE_JSON_ERROR = 'JSON parse error';
    const MESSAGE_INVALID_RESPONSE = 'Invalid response from service';
    /**
     * @var string
     */
    protected $key;

    /**
     * @var resource
     */
    protected $handler;

    /**
     * @param string $key The API key
     */
    public function __construct($key)
    {
        $this->key = $key;
        $this->handler = curl_init();
        curl_setopt($this->handler, CURLOPT_RETURNTRANSFER, true);
    }

    /**
     * @param string $text The text to detect the language for.
     *
     * @return string
     * @throws Exception
     */
    public function detect($text)
    {
        return (string)$this->execute('DetectTextLanguage', array(
            'text' => $text
        ));
    }

    /**
     * @param string $text
     * @param string $from
     * @param string $to
     * @param string $profile
     * @param string $format
     * @return Translation
     * @throws Exception
     */
    public function translate($text, $from, $to, $profile = '', $format = 'text/html')
    {
        $params = array(
            'text' => $text,
            'from' => $from,
            'to' => $to,
            'profile' => $profile,
            'format' => $format,
        );
        $data = $this->execute('TranslateFormattedText', $params);
        return new Translation($text, $data, array($from, $to));
    }

    /**
     * @param string $uri
     * @param array $params
     *
     * @return array
     * @throws Exception
     */
    protected function execute($uri, array $params)
    {
        $url = static::BASE_URL . $uri;
        $params = json_encode($params);
        curl_setopt($this->handler, CURLOPT_URL, $url);
        curl_setopt($this->handler, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($this->handler, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($this->handler, CURLOPT_POST, true);
        curl_setopt($this->handler, CURLOPT_POSTFIELDS, $params);
        curl_setopt($this->handler, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->handler, CURLOPT_HTTPHEADER,
            array(
                'ptsapikey:' . $this->key,
                'Accept:application/json',
                'Content-Type:application/json',
            )
        );
        $remoteResult = curl_exec($this->handler);
        if ($remoteResult === false) {
            throw new Exception(curl_error($this->handler), curl_errno($this->handler));
        }
        $result = json_decode($remoteResult, true);
        if (!$result) {
            $errorMessage = self::MESSAGE_UNKNOWN_ERROR;
            if (version_compare(PHP_VERSION, '5.3', '>=')) {
                if (json_last_error() !== JSON_ERROR_NONE) {
                    if (version_compare(PHP_VERSION, '5.5', '>=')) {
                        $errorMessage = json_last_error_msg();
                    } else {
                        $errorMessage = self::MESSAGE_JSON_ERROR;
                    }
                }
            }
            throw new Exception(sprintf('%s: %s', self::MESSAGE_INVALID_RESPONSE, $errorMessage));
        } elseif (is_array($result) && isset($result['Message'])) {
            throw new Exception($result['Message']);
        }
        return $result;
    }
}
