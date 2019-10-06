<?php
namespace Modules\insiderErrorHandler;

/**
  Class of object used in manageError method
 
  @package \Modules\insiderErrorHandler

  @author Marcello Costa
 */
class manageErrorMsg {
    private $type;
    private $message;
    private $text;
    private $file;
    private $line;
    private $fatal;
    private $subject;
    
    /**
      Construct function of the class. Can receive an array and
      with this array can set the properties of object.

      @author Marcello Costa

      @package \Modules\insiderErrorHandler\manageErrorMsg

      @param  array  $properties    Array to set properties of object

      @return void
     */
    function __construct(array $properties = null) {
        if (is_array($properties)){
            if (isset($properties['type'])){
                $this->setType($properties['type']);
            }
            if (isset($properties['text'])){
                $this->setText($properties['text']);
            }
            if (isset($properties['file'])){
                $this->setFile($properties['file']);
            }
            if (isset($properties['line'])){
                $this->setLine($properties['line']);
            }
            if (isset($properties['fatal'])){
                $this->setFatal($properties['fatal']);
            }
            if (isset($properties['subject'])){
                $this->setSubject($properties['subject']);
            }
            
            if (!$this->validateAllProperties()){
                primaryError('Invalid ManageErrorMsg object '.json_encode($properties));
            }
        }
    }
    
    /**
      Get property of object

      @author Marcello Costa

      @package \Modules\insiderErrorHandler\manageErrorMsg

      @return string Property
     */
    public function getType() {
        return $this->type;
    }

    /**
      Get property of object

      @author Marcello Costa

      @package \Modules\insiderErrorHandler\manageErrorMsg

      @return string Property
     */
    public function getMessage() {
        return $this->message;
    }

    /**
      Get message or text of object

      @author Marcello Costa

      @package \Modules\insiderErrorHandler\manageErrorMsg

      @return string Property
     */
    public function getMessageOrText(){
        if ($this->getMessage() !== "" && $this->getMessage() !== null){
            return $this->getMessage();
        }
        else{
            return $this->getText();
        }
    }
    
    /**
      Get property of object

      @author Marcello Costa

      @package \Modules\insiderErrorHandler\manageErrorMsg

      @return string Property
     */
    public function getFile() {
        return $this->file;
    }
    
    /**
      Get property of object

      @author Marcello Costa

      @package \Modules\insiderErrorHandler\manageErrorMsg

      @return string Property
     */
    public function getLine() {
        return $this->line;
    }

    /**
      Get property of object

      @author Marcello Costa

      @package \Modules\insiderErrorHandler\manageErrorMsg

      @return bool Property
     */
    public function getFatal() {
        return $this->fatal;
    }
    
    /**
      Get property of object

      @author Marcello Costa

      @package \Modules\insiderErrorHandler\manageErrorMsg

      @return string Property
     */
    public function getSubject() {
        return $this->subject;
    }
    
    /**
      Get property of object

      @author Marcello Costa

      @package \Modules\insiderErrorHandler\manageErrorMsg

      @return string Property
     */
    public function getText() : string {
        return $this->text;
    }

    /**
      Set property of object

      @author Marcello Costa

      @package \Modules\insiderErrorHandler\manageErrorMsg

      @param  string  $type    String that specifies the type of error

      @return void
     */
    public function setType(string $type) : void {
        $this->type = $type;    
    }

    /**
      Set property of object

      @author Marcello Costa

      @package \Modules\insiderErrorHandler\manageErrorMsg

      @param  string  $message Message of error (can be an HTML)

      @return void
     */
    public function setMessage(string $message) : void {
        $this->message = $message;
    }

    /**
      Set property of object

      @author Marcello Costa

      @package \Modules\insiderErrorHandler\manageErrorMsg

      @param  string  $file Path of file that triggered the error

      @return void
     */
    public function setFile(string $file) : void {
        $this->file = $file;
    }

    /**
      Set property of object

      @author Marcello Costa

      @package \Modules\insiderErrorHandler\manageErrorMsg

      @param  string  $line Line on file that triggered the error

      @return void
     */
    public function setLine(string $line) : void {
        $this->line = $line;
    }

    /**
      Set property of object

      @author Marcello Costa

      @package \Modules\insiderErrorHandler\manageErrorMsg

      @param  bool  $fatal Defines if is an fatal error

      @return void
     */
    public function setFatal(bool $fatal) : void {
        $this->fatal = $fatal;
    }

    /**
      Set property of object

      @author Marcello Costa

      @package \Modules\insiderErrorHandler\manageErrorMsg

      @param  string  $subject Subject of mail that will be send to MAILBOX

      @return void
     */
    public function setSubject(string $subject) : void {
        $this->subject = $subject;
    }
    
    /**
      Set property of object

      @author Marcello Costa

      @package \Modules\insiderErrorHandler\manageErrorMsg

      @param  string  $text Text of error that will be displayed to user (if
                            message dont exists or cannot be displayed)

      @return void
     */
    public function setText(string $text) : void {
        $this->text = $text;
    }
    
    /**
      Function to validate properties of the class

      @author Marcello Costa

      @package KeyClass\Error

      @return bool Return of validation
     */
    public function validateAllProperties() : bool {
        if (
            trim($this->getFatal()) === "" ||
            trim($this->getFile()) === "" ||
            trim($this->getLine()) === "" ||
            (trim($this->getMessage()) === "" && trim($this->getText()) === "") ||
            trim($this->getSubject()) === "" ||
            trim($this->getType()) === ""
           ){
         return false;   
        }
        return true;
    }
}