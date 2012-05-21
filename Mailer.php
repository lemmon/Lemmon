<?php
/**
* 
*/


require_once Lemmon_Autoloader::getLibDir() . '/Swift/_/swift_init.php';


class Lemmon_Mailer
{
	protected $from;
	protected $replyTo;
	protected $to;
	protected $cc;
	protected $bcc;
	protected $subject;
	protected $body;
	protected $template;
	protected $data;
	protected $contentType;
	protected $charset;

	protected $transportType = 'smtp';
	protected $transportAddress;
	protected $transportPort = 25;
	protected $transportUsername;
	protected $transportPassword;
	
	private $_transport;
	
	
	final public static function __callStatic($class_name, $arguments)
	{
		$called_class_name = get_called_class();
		preg_match('/(batchSend|preview|send)(.*)/i', $class_name, $m);
		if ($m[1] and $m[2])
		{
			$mailer = new $called_class_name();
			call_user_func_array(array($mailer, $m[2]), $arguments);
			if (!$mailer->template) $mailer->template = Lemmon_String::classToFileName($m[2]);
			return $mailer->{$m[1]}();
		}
		else
		{
			return false;
		}
	}
	
	
	final public function prepareTransport()
	{
		if (!$this->_transport)
		{
			switch ($this->transportType)
			{
				case 'smtp':
					$this->_transport = Swift_SmtpTransport::newInstance($this->transportAddress, $this->transportPort)
						->setUsername($this->transportUsername)
						->setPassword($this->transportPassword);
					break;
					
				default:
					throw new Lemmon_Exception('Unknown Transport Type: ' . $this->transportType);
			}
		}
		return $this->_transport;
	}
	
	final public static function newMessage()
	{
		return Swift_Message::newInstance();
	}

	final public function prepareMessage()
	{
		$message = $this->newMessage();
		if ($this->from) $message->setFrom($this->from);
		if ($this->replyTo) $message->setReplyTo($this->replyTo);
		if ($this->to) $message->setTo($this->to);
		if ($this->cc) $message->setCc($this->cc);
		if ($this->bcc) $message->setBcc($this->bcc);
		if ($this->subject) $message->setSubject($this->subject);
		if ($this->body)
		{
			$message->setBody(
				$this->body,
				$this->contentType ? $this->contentType : 'text/plain');
		}
		elseif ($this->template)
		{
			$message->setBody(
				$this->body = Lemmon_Template::render('mailer_' . $this->template . '.html', $this->data),
				$this->contentType ? $this->contentType : 'text/html');
		}
		else
		{
			die('NO TEMPLATE!');
		}
		return $message;
	}

	
	final public function send()
	{
		$transport = $this->prepareTransport();
		$message = $this->prepareMessage();
		$swift_mailer = Swift_Mailer::newInstance($transport);
		return $swift_mailer->send($message, $failures);
	}
	
	
	final public function batchSend()
	{
		$transport = $this->prepareTransport();
		$message = $this->prepareMessage();
		$swift_mailer = Swift_Mailer::newInstance($transport);
		$res = $swift_mailer->batchSend($message, $failures);
		return $res;
	}


	final public function preview()
	{
		$this->prepareMessage();
		return $this;
	}


	final public function getSubject()
	{
		return $this->subject;
	}
	
	
	final public function getBody()
	{
		return $this->body;
	}
}
