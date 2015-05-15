<?Php
class pswinsms
{
	private $xml;
	public $msglist;
	private $ch;
	public function __construct($username,$password)
	{
		$this->ch=curl_init();
		curl_setopt($this->ch,CURLOPT_URL,'https://secure.pswin.com/XMLHttpWrapper/process.aspx');
		curl_setopt($this->ch,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($this->ch,CURLOPT_HTTPHEADER,array('Content-Type: application/xml'));
		curl_setopt($this->ch,CURLOPT_POST,true);
		//curl_setopt($this->ch,CURLINFO_HEADER_OUT,true);
		
		$this->xml=new SimpleXMLElement('<?xml version="1.0"?><!DOCTYPE SESSION SYSTEM "pswincom_submit.dtd"><SESSION></SESSION>');
		$this->xml->addChild('CLIENT',$username);
		$this->xml->addChild('PW',$password);
		$this->msglist=$this->xml->addChild('MSGLST');
	}
	public function addmessage($to,$text,$from)
	{
		$msg=$this->msglist->addChild('MSG');
		$msg->addChild('TEXT',$text);
		$msg->addChild('RCV',$to);
		$msg->addChild('SND',$from);	
	}
	public function sendmessages()
	{
		curl_setopt($this->ch,CURLOPT_POSTFIELDS,$this->xml->asXML());
		return $this->checkresult(curl_exec($this->ch));
	}
	public function sendsinglemessage($to,$text,$from)
	{
		$this->addmessage($to,$text,$from);
		return $this->sendmessages();
	}
	public function checkresult($xmlstring)
	{
		if(empty($xmlstring))
			return false;
		$xml=simplexml_load_string($xmlstring);

		$msgqueue=(array)$this->msglist;
		$msgqueue=$msgqueue['MSG'];

		foreach($xml->MSGLST->MSG as $key=>$msg)
		{
			$id=(int)$msg->ID;
			$recipient=(string)$msgqueue[$id-1]->RCV;

			if((string)$msg->STATUS=='OK')
				$result[$recipient]=true;
			else
				$result[$recipient]=(string)$msg->STATUS;
		}
		return $result;
	}
}