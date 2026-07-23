<?php

/**
 * Direct to SMTP Delivery script
 */
class smtpSend
{

    private $fromEmail;
    private $error;
    private $debug; // 1 - 9

    public function __construct($fromEmail)
    {
        $this->fromEmail = $fromEmail;
        $this->error = false;
        $this->debug = 0;
    }

    public function getError()
    {
        return $this->error;
    }

    protected function _die($msg, $line, $file)
    {
        $this->error = trim($msg) . " @ $file:$line ";
    }

    protected function _sockPut(&$socket, $msg, $level = 1)
    {
        if ($this->debug >= $level)
            echo $msg;
        fputs($socket, $msg);
    }

    protected function _getMxHosts($to)
    {
        $response = array('mx' => array(), 'weight' => array());
        list($user, $host) = explode('@', $to);
        if (getmxrr($host, $response['mx'], $response['weight'])) {
            return $response;
        }
        return false;
    }

    protected function _parse($socket, $response, $line = __LINE__, $level = 1)
    {
        while (@substr($server_response, 3, 1) != ' ') {
            if (!($server_response = fgets($socket, 256))) {
                $this->_die("Couldn't get mail server response codes", $line, __FILE__);
            }
            if ($this->debug >= $level)
                echo $server_response;
        }

        if (!(substr($server_response, 0, 3) == $response)) {
            $this->_die("Ran into problems sending Mail. Response: $server_response", $line, __FILE__);
        } else {
            return substr($server_response, 4);
        }
    }

    public function send($to, $subject, $message, $headers = '')
    {

        $message = preg_replace("#(?<!\r)\n#si", "\r\n", $message);
        $subject = trim($subject);

        if ($subject == '') {
            $this->_die("No email Subject specified", __LINE__, __FILE__);
        }

        if (trim($message) == '') {
            $this->_die("Email message was blank", __LINE__, __FILE__);
        }

        $socket = fsockopen('127.0.0.1', 25, $errno, $errstr, 20);

        if ($this->error !== false)
            return false;
        // Wait for reply
        $mxReady = $this->_parse($socket, "220", __LINE__, 5);
        $mxHost = (!empty($mxReady)) ? substr($mxReady, 0, strpos($mxReady, ' ')) : $mxhosts[0];

        $this->_sockPut($socket, "HELO " . $mxHost . "\r\n", 1);
        $this->_parse($socket, "250", __LINE__, 5);

        if ($this->error !== false) {
            fclose($socket);
            return false;
        }

        // Specify who the mail is from....
        $this->_sockPut($socket, "MAIL FROM: " . $this->fromEmail . "\r\n", 2);
        $this->_parse($socket, "250", __LINE__, 5);

        if ($this->error !== false) {
            fclose($socket);
            return false;
        }
        $this->_sockPut($socket, "RCPT TO: " . $to . "\r\n", 2);
        $this->_parse($socket, "250", __LINE__, 5);

        if ($this->error !== false) {
            fclose($socket);
            return false;
        }
        // Ok now we tell the server we are ready to start sending data
        $this->_sockPut($socket, "DATA\r\n", 5);

        // This is the last response code we look for until the end of the message.
        $this->_parse($socket, "354", __LINE__, 5);

        if ($this->error !== false) {
            fclose($socket);
            return false;
        }
        // Send the Subject Line...
        $this->_sockPut($socket, "Subject: $subject\r\n", 8);

        if (!empty($headers)) {
            // Now any custom headers....
            $this->_sockPut($socket, "$headers\r\n", 8);
        }

        $this->_sockPut($socket, "\r\n", 8);

        // Ok now we are ready for the message...
        $this->_sockPut($socket, "$message\r\n", 9);

        // Ok the all the ingredients are mixed in let's cook this puppy...
        $this->_sockPut($socket, ".\r\n", 9);

        $this->_parse($socket, "250", __LINE__, 9);

        if ($this->error !== false) {
            fclose($socket);
            return false;
        }
        // Now tell the server we are done and close the socket...
        $this->_sockPut($socket, "QUIT\r\n", 8);
        fclose($socket);

        return TRUE;
    }

}

class mimeEmail
{

    private $message;
    private $headers;
    private $finfo;
    private $rhash;

    public function __construct()
    {
        $this->headers = array();
        $this->message = array();
        $const = defined('FILEINFO_MIME_TYPE') ? FILEINFO_MIME_TYPE : FILEINFO_MIME;
        $this->finfo = finfo_open($const);
        $this->rhash = array('id' => uniqid(""));
    }

    public function __destruct()
    {
        finfo_close($this->finfo);
    }

    public function setBody($text, $isHtml = false)
    {
        $this->msgAdd(strip_tags($text), 'text/plain', 1);
        if ($isHtml) {
            $this->rhash['alt'] = 'boundary-' . md5(microtime());
            $text = $this->remapImages($text);
            $this->msgAdd($text, 'text/html', 2);
        }
    }

    private function remapImages($html)
    {
        if (preg_match_all('/<img([^>]+?)src=("|\')([^"\']+)("|\')/isxmU', $html, $patterns)) {
            $repl = array();
            array_unique($patterns[3]);
            foreach ($patterns[3] as $img) {
                $imgId = basename($img) . '@' . $this->rhash['id'];
                $repl[] = 'cid:' . $imgId;
                $this->addFile($img, 'Content-ID: <' . $imgId . '>');
            }
            $html = str_replace($patterns[3], $repl, $html);
        }
        return $html;
    }

    public function addFile($path, $cd = 'Content-Disposition: attachment')
    {
        (!isset($this->rhash['mixed'])) && $this->rhash['mixed'] = 'boundary-' . md5(microtime());
        $fC = file_get_contents($path);
        $content = chunk_split(base64_encode($fC));
        $mime = @finfo_file($this->finfo, $path);
        if (empty($mime)) {
            file_put_contents('/tmp/' . basename($path), $fC);
            $mime = finfo_file($this->finfo, '/tmp/' . basename($path));
            unlink('/tmp/' . basename($path));
        }
        $this->msgAdd($content, $mime, 9, 'base64', basename($path), $cd);
    }

    public function getMailMime()
    {
        usort($this->message, array("mimeEmail", "xsort"));

        if (isset($this->rhash['mixed'])) {
            $headers = 'Content-Type: multipart/mixed; boundary="' . $this->rhash['mixed'] . '"' . "\r\n";
            $mimeEmail = '--' . $this->rhash['mixed'] . "\r\n"
                    . 'Content-Type: multipart/alternative; boundary="' . $this->rhash['alt'] . '"' . "\r\n\r\n";
        } elseif (isset($this->rhash['alt'])) {
            $headers = 'Content-Type: multipart/alternative; boundary="' . $this->rhash['alt'] . '"' . "\r\n";
        }


        $thash = 'alt';
        foreach ($this->message as $item) {
            if ($thash == 'alt' && $item['s'] == 9) {
                $mimeEmail .= '--' . $this->rhash[$thash] . "--\r\n\r\n";
                $thash = 'mixed';
            }
            $mimeEmail .= '--' . $this->rhash[$thash] . "\r\n"
                    . 'Content-Type: ' . $item['t'] . ';' . (($item['n']) ? 'name="' . $item['n'] . '"' : '') . "\r\n"
                    . 'Content-Transfer-Encoding: ' . $item['e'] . "\r\n"
                    . (($item['d']) ? $item['d'] . "\r\n" : '') . "\r\n"
                    . $item['c'] . "\r\n\r\n";
        }
        $mimeEmail .= '--' . $this->rhash[$thash] . "--\r\n\r\n";

        return array('headers' => $headers, 'mail' => $mimeEmail);
    }

    private function msgAdd($content, $type, $sort, $enc = '7bit', $name = false, $cd = false)
    {
        $this->message[] = array('t' => $type, 'c' => $content, 'n' => $name, 'e' => $enc, 'd' => $cd, 's' => $sort);
    }

    public static function xsort($a, $b)
    {
        return ($a['s'] == $b['s']) ? 0 : ($a['s'] - $b['s']);
    }

}

?>
