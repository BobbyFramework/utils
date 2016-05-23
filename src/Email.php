<?php

namespace BobbyFramework\Utils;

use BobbyFramework\Utils\Traits\Hydrator;
use BobbyFramework\Utils\Traits\Uid;

class Email
{
    use Hydrator;
    use Uid;

    /**
     * @var int $_wrap
     */
    protected $_wrap;

    /**
     * @var array $_to
     */
    protected $_to = array();

    /**
     * @var string $_subject
     */
    protected $_subject;

    /**
     * @var string $_message
     */
    protected $_message;

    /**
     * @var array $_headers
     */
    protected $_headers = array();

    /**
     * @var string $_parameters
     */
    protected $_params;

    /**
     * @var array $_attachments
     */
    protected $_attachments = array();

    /**
     * @var string $_uid
     */
    protected $_uid;

    /**
     * @var int $_wrapDefault
     */
    private $_wrapDefault = 78;

    /**
     * __construct
     *
     * Resets the class properties.
     */
    public function __construct()
    {
        $this->reset();
    }

    /**
     * reset
     *
     * Resets all properties to initial state.
     *
     * @return Email
     */
    public function reset()
    {
        $this->_to = [];
        $this->_headers = [];
        $this->_subject = null;
        $this->_message = null;
        $this->_wrap = $this->_wrapDefault;
        $this->_params = null;
        $this->_attachments = [];
        $this->_uid = $this->getUniqueId();
        return $this;
    }

    /**
     * getTo
     *
     * Return an array of formatted To addresses.
     *
     * @return array
     */
    public function getTo()
    {
        return $this->_to;
    }

    /**
     * setTo
     *
     * @param string $email The email address to send to.
     * @param string $name The name of the person to send to.
     *
     * @return Email
     */
    public function setTo($email, $name)
    {
        $this->_to[] = $this->formatHeader((string)$email, (string)$name);
        return $this;
    }

    /**
     * formatHeader
     *
     * Formats a display address for emails according to RFC2822 e.g.
     * Name <address@domain.tld>
     *
     * @param string $email The email address.
     * @param string $name The display name.
     *
     * @return string
     */
    public function formatHeader($email, $name = null)
    {
        $email = $this->filterEmail($email);
        if (empty($name)) {
            return $email;
        }
        $name = $this->encodeUtf8($this->filterName($name));
        return sprintf('"%s" <%s>', $name, $email);
    }

    /**
     * filterEmail
     *
     * Removes any carriage return, line feed, tab, double quote, comma
     * and angle bracket characters before sanitizing the email address.
     *
     * @param string $email The email to filter.
     *
     * @return string
     */
    public function filterEmail($email)
    {
        $rule = array(
            "\r" => '',
            "\n" => '',
            "\t" => '',
            '"' => '',
            ',' => '',
            '<' => '',
            '>' => ''
        );
        $email = strtr($email, $rule);
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        return $email;
    }

    /**
     * encodeUtf8
     *
     * @param string $value The value to encode.
     *
     * @return string
     */
    public function encodeUtf8($value)
    {
        $value = trim($value);
        if (preg_match('/(\s)/', $value)) {
            return $this->encodeUtf8Words($value);
        }
        return $this->encodeUtf8Word($value);
    }

    /**
     * encodeUtf8Words
     *
     * @param string $value The words to encode.
     *
     * @return string
     */
    public function encodeUtf8Words($value)
    {
        $words = explode(' ', $value);
        $encoded = array();
        foreach ($words as $word) {
            $encoded[] = $this->encodeUtf8Word($word);
        }
        return join($this->encodeUtf8Word(' '), $encoded);
    }

    /**
     * encodeUtf8Word
     *
     * @param string $value The word to encode.
     *
     * @return string
     */
    public function encodeUtf8Word($value)
    {
        return sprintf('=?UTF-8?B?%s?=', base64_encode($value));
    }

    /**
     * filterName
     *
     * Removes any carriage return, line feed or tab characters. Replaces
     * double quotes with single quotes and angle brackets with square
     * brackets, before sanitizing the string and stripping out html tags.
     *
     * @param string $name The name to filter.
     *
     * @return string
     */
    public function filterName($name)
    {
        $rule = array(
            "\r" => '',
            "\n" => '',
            "\t" => '',
            '"' => "'",
            '<' => '[',
            '>' => ']',
        );
        $filtered = filter_var(
            $name,
            FILTER_SANITIZE_STRING,
            FILTER_FLAG_NO_ENCODE_QUOTES
        );
        return trim(strtr($filtered, $rule));
    }

    /**
     * getSubject function.
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->_subject;
    }

    /**
     * setSubject
     *
     * @param string $subject The email subject
     *
     * @return Email
     */
    public function setSubject($subject)
    {
        $this->_subject = $this->encodeUtf8(
            $this->filterOther((string)$subject)
        );
        return $this;
    }

    /**
     * filterOther
     *
     * Removes ASCII control characters including any carriage return, line
     * feed or tab characters.
     *
     * @param string $data The data to filter.
     *
     * @return string
     */
    public function filterOther($data)
    {
        return filter_var($data, FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW);
    }

    /**
     * getMessage
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->_message;
    }

    /**
     * setMessage
     *
     * @param string $message The message to send.
     *
     * @return Email
     */
    public function setMessage($message)
    {
        $this->_message = str_replace("\n.", "\n..", (string)$message);
        return $this;
    }

    /**
     * addAttachment
     *
     * @param string $path The file path to the attachment.
     * @param string $filename The filename of the attachment when emailed.
     *
     * @return Email
     */
    public function addAttachment($path, $filename = null)
    {
        $filename = empty($filename) ? basename($path) : $filename;
        $this->_attachments[] = array(
            'path' => $path,
            'file' => $filename,
            'data' => $this->getAttachmentData($path)
        );
        return $this;
    }

    /**
     * getAttachmentData
     *
     * @param string $path The path to the attachment file.
     *
     * @return string
     */
    public function getAttachmentData($path)
    {
        $filesize = filesize($path);
        $handle = fopen($path, "r");
        $attachment = fread($handle, $filesize);
        fclose($handle);
        return chunk_split(base64_encode($attachment));
    }

    /**
     * setFrom
     *
     * @param string $email The email to send as from.
     * @param string $name The name to send as from.
     *
     * @return Email
     */
    public function setFrom($email, $name)
    {
        $this->addMailHeader('From', (string)$email, (string)$name);
        return $this;
    }

    /**
     * addMailHeader
     *
     * @param string $header The header to add.
     * @param string $email The email to add.
     * @param string $name The name to add.
     *
     * @return Email
     */
    public function addMailHeader($header, $email = null, $name = null)
    {
        $address = $this->formatHeader((string)$email, (string)$name);
        $this->_headers[] = sprintf('%s: %s', (string)$header, $address);
        return $this;
    }

    /**
     * addGenericHeader
     *
     * @param string $header The generic header to add.
     * @param mixed $value The value of the header.
     *
     * @return Email
     */
    public function addGenericHeader($header, $value)
    {
        $this->_headers[] = sprintf(
            '%s: %s',
            (string)$header,
            (string)$value
        );
        return $this;
    }

    /**
     * getHeaders
     *
     * Return the headers registered so far as an array.
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->_headers;
    }

    /**
     * setAdditionalParameters
     *
     * Such as "-youremail@yourserver.com
     *
     * @param string $additionalParameters The addition mail parameter.
     *
     * @return Email
     */
    public function setParameters($additionalParameters)
    {
        $this->_params = (string)$additionalParameters;
        return $this;
    }

    /**
     * getAdditionalParameters
     *
     * @return string
     */
    public function getParameters()
    {
        return $this->_params;
    }

    /**
     * getWrap
     *
     * @return int
     */
    public function getWrap()
    {
        return $this->_wrap;
    }

    /**
     * setWrap
     *
     * @param int $wrap The number of characters at which the message will wrap.
     *
     * @return Email
     */
    public function setWrap($wrap = null)
    {
        if (is_null($wrap) || (int)$wrap < 1) {
            $wrap = (int)$this->_wrapDefault;
        }
        $this->_wrap = $wrap;
        return $this;
    }

    /**
     * send
     *
     * @throws \RuntimeException on no 'To: ' address to send to.
     * @return boolean
     */
    public function send()
    {
        $to = (empty($this->_to)) ? '' : join(', ', $this->_to);
        $headers = (empty($this->_headers)) ? '' : join(PHP_EOL, $this->_headers);

        if (empty($to)) {
            throw new \RuntimeException(
                'Unable to send, no To address has been set.'
            );
        }

        if ($this->hasAttachments()) {
            $message = $this->assembleAttachmentBody();
            $headers .= PHP_EOL . $this->assembleAttachmentHeaders();
        } else {
            $message = $this->getWrapMessage();
        }

        return mail($to, $this->_subject, $message, $headers, $this->_params);
    }

    /**
     * hasAttachments
     *
     * Checks if the email has any registered attachments.
     *
     * @return bool
     */
    public function hasAttachments()
    {
        return !empty($this->_attachments);
    }

    /**
     * assembleAttachmentBody
     *
     * @return string
     */
    public function assembleAttachmentBody()
    {
        $body = array();
        $body[] = "This is a multi-part message in MIME format.";
        $body[] = "--{$this->_uid}";
        $body[] = "Content-type:text/html; charset=\"utf-8\"";
        $body[] = "Content-Transfer-Encoding: 7bit";
        $body[] = "";
        $body[] = $this->_message;
        $body[] = "";
        $body[] = "--{$this->_uid}";

        foreach ($this->_attachments as $attachment) {
            $body[] = $this->getAttachmentMimeTemplate($attachment);
        }

        return implode(PHP_EOL, $body);
    }

    /**
     * getAttachmentMimeTemplate
     *
     * @param array $attachment An array containing 'file' and 'data' keys.
     * @param string $uid A unique identifier for the boundary.
     *
     * @return string
     */
    public function getAttachmentMimeTemplate($attachment)
    {
        $file = $attachment['file'];
        $data = $attachment['data'];

        $head = array();
        $head[] = "Content-Type: application/octet-stream; name=\"{$file}\"";
        $head[] = "Content-Transfer-Encoding: base64";
        $head[] = "Content-Disposition: attachment; filename=\"{$file}\"";
        $head[] = "";
        $head[] = $data;
        $head[] = "";
        $head[] = "--{$this->_uid}";

        return implode(PHP_EOL, $head);
    }

    /**
     * assembleAttachment
     *
     * @return string
     */
    public function assembleAttachmentHeaders()
    {
        $head = array();
        $head[] = "MIME-Version: 1.0";
        $head[] = "Content-Type: multipart/mixed; boundary=\"{$this->_uid}\"";

        return join(PHP_EOL, $head);
    }

    /**
     * getWrapMessage
     *
     * @return string
     */
    public function getWrapMessage()
    {
        return wordwrap($this->_message, $this->_wrap);
    }
}