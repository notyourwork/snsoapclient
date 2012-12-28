<?php
interface ServiceNowSoapRecord {
	
}

Class Record implements ServiceNowSoapRecord {
	
	public $soapRecord = array();
	public $tableType = NULL;
	
	/**
	 * 
	 * Create the record with the corresponding table type.
	 * 
	 * @param Array $SoapRecord
	 * @param String $TableType
	 */
	public function __construct($SoapRecord, $TableType){
		$this->soapRecord = $SoapRecord;
		$this->tableType = $TableType;
	}
	
	/**
	 * 
	 * @return Array of comments in the form of:
	 * 	comment[n]['TimeStamp']
	 * 	comment[n]['Author']
	 * 	comment[n]['CommentType']
	 * 	comment[n]['Comment']
	 */
	public function getComments(){
		$allComments = array();
		if(array_key_exists('dv_comments_and_work_notes', $this->soapRecord)){
			foreach(explode(chr(10) . chr(10), $this->soapRecord->dv_comments_and_work_notes) as $currentComment){
				if(strlen($currentComment) > 0){
					
					$timeStamp = explode(' ', $currentComment);
					$timeStamp = $timeStamp[0] . " " . $timeStamp[1];
					
					$author = substr($currentComment, strpos($currentComment, ' - ') + 3, strPos($currentComment, ' - ' + 3) - strlen(substr($currentComment, strpos($currentComment, '('))));
					
					$commentType = substr($currentComment, strpos($currentComment, '(') + 1, strpos($currentComment, ')') - strpos($currentComment, '(') - 1);
					
					$commentContent = trim(substr($currentComment, strpos($currentComment, chr(10))));
					
					array_push($allComments, array('TimeStamp' => $timeStamp, 'Author' => $author, 'CommentType' => $commentType, 'Comment' => $commentContent));
					
				
				}
			}
		}
		return $allComments;		
	}
}
?>
