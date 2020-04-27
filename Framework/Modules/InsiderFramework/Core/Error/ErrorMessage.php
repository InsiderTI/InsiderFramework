<?php

namespace Modules\InsiderFramework\Core\Error;

/**
 * Class of error message object
 *
 * @package \Modules\InsiderFramework\Core\Error\ErrorMessage
 *
 * @author Marcello Costa
 */
class ErrorMessage
{
    private $type;
    private $message;
    private $text;
    private $file;
    private $line;
    private $fatal;
    private $subject;

    /**
     * Construct function of the class. Can receive an array and
     * with this array can set the properties of object.
     *
     * @author Marcello Costa
     *
     * @package \Modules\InsiderFramework\Core\Error\ErrorMessage
     *
     * @param array $properties Array to set properties of object
     *
     * @return void
     */
    public function __construct(array $properties = null)
    {
        if (is_array($properties)) {
            if (isset($properties['type'])) {
                $this->setType($properties['type']);
            }
            if (isset($properties['text'])) {
                $this->setText($properties['text']);
            }
            if (isset($properties['file'])) {
                $this->setFile($properties['file']);
            }
            if (isset($properties['line'])) {
                $this->setLine($properties['line']);
            }
            if (isset($properties['fatal'])) {
                $this->setFatal($properties['fatal']);
            }
            if (isset($properties['subject'])) {
                $this->setSubject($properties['subject']);
            }
        }

        $validationErrors = $this->validateAllProperties();
        if (count($validationErrors) !== 0) {
            $debugbacktrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);

            $file = $debugbacktrace[0]['file'];
            $line = $debugbacktrace[0]['line'];
            if (isset($debugbacktrace[2])) {
                if (isset($debugbacktrace[2]['file']) && isset($debugbacktrace[2]['line'])) {
                    $file = $debugbacktrace[2]['file'];
                    $line = $debugbacktrace[2]['line'];
                }
            }
            \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError(
                'Invalid ErrorMessage object. Errors: ' . json_encode($validationErrors)
            );
        }
    }

    /**
     * Get property of object
     *
     * @author Marcello Costa
     *
     * @package \Modules\InsiderFramework\Core\Error\ErrorMessage
     *
     * @return string Type
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Get property of object
     *
     * @author Marcello Costa
     *
     * @package \Modules\InsiderFramework\Core\Error\ErrorMessage
     *
     * @return string|null Message
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * Get message or text of object
     *
     * @author Marcello Costa
     *
     * @package \Modules\InsiderFramework\Core\Error\ErrorMessage
     *
     * @return string Message or text
     */
    public function getMessageOrText(): string
    {
        if ($this->getMessage() !== "" && $this->getMessage() !== null) {
            return $this->getMessage();
        } else {
            return $this->getText();
        }
    }

    /**
     * Get file of error
     *
     * @author Marcello Costa
     *
     * @package \Modules\InsiderFramework\Core\Error\ErrorMessage
     *
     * @return string File
     */
    public function getFile(): string
    {
        return $this->file;
    }

    /**
     * Get line of error
     *
     * @author Marcello Costa
     *
     * @package \Modules\InsiderFramework\Core\Error\ErrorMessage
     *
     * @return int Line of error
     */
    public function getLine(): int
    {
        return $this->line;
    }

    /**
     * Get if error is fatal
     *
     * @author Marcello Costa
     *
     * @package \Modules\InsiderFramework\Core\Error\ErrorMessage
     *
     * @return bool Fatal or not fatal error
     */
    public function getFatal(): bool
    {
        return $this->fatal;
    }

    /**
     * Get subject of error message
     *
     * @author Marcello Costa
     *
     * @package \Modules\InsiderFramework\Core\Error\ErrorMessage
     *
     * @return string Subject
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * Get text of error message
     *
     * @author Marcello Costa
     *
     * @package \Modules\InsiderFramework\Core\Error\ErrorMessage
     *
     * @return string Text of error message
     */
    public function getText(): ?string
    {
        return $this->text;
    }

    /**
     * Set type of error message
     *
     * @author Marcello Costa
     *
     * @package \Modules\InsiderFramework\Core\Error\ErrorMessage
     *
     * @param string $type String that specifies the type of error
     *
     * @return void
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * Set error message
     *
     * @author Marcello Costa
     *
     * @package \Modules\InsiderFramework\Core\Error\ErrorMessage
     *
     * @param string $message Message of error (can be an HTML)
     *
     * @return void
     */
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    /**
     * Set file path that triggered the erro
     *
     * @author Marcello Costa
     *
     * @package \Modules\InsiderFramework\Core\Error\ErrorMessage
     *
     * @param string $file Path of file that triggered the error
     *
     * @return void
     */
    public function setFile(string $file): void
    {
        $this->file = $file;
    }

    /**
     * Set line of error
     *
     * @author Marcello Costa
     *
     * @package \Modules\InsiderFramework\Core\Error\ErrorMessage
     *
     * @param int $line Line on file that triggered the error
     *
     * @return void
     */
    public function setLine(int $line): void
    {
        $this->line = $line;
    }

    /**
     * Set if error is fatal
     *
     * @author Marcello Costa
     *
     * @package \Modules\InsiderFramework\Core\Error\ErrorMessage
     *
     * @param bool $fatal Defines if is an fatal error
     *
     * @return void
     */
    public function setFatal(bool $fatal): void
    {
        $this->fatal = $fatal;
    }

    /**
     * Set subject of error
     *
     * @author Marcello Costa
     *
     * @package \Modules\InsiderFramework\Core\Error\ErrorMessage
     *
     * @param string $subject Subject of mail that will be send to MAILBOX
     *
     * @return void
     */
    public function setSubject(string $subject): void
    {
        $this->subject = $subject;
    }

    /**
     * Set text of error
     *
     * @author Marcello Costa
     *
     * @package \Modules\InsiderFramework\Core\Error\ErrorMessage
     *
     * @param string $text Text of error that will be displayed to user (if
     *                     message dont exists or cannot be displayed)
     *
     * @return void
     */
    public function setText(string $text): void
    {
        $this->text = $text;
    }

    /**
     * Function to validate properties of the class
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Error
     *
     * @return array Array of errors
     */
    public function validateAllProperties(): array
    {
        $errors = [];
        if (!is_bool($this->getFatal())) {
            $errors[] = "Value " . "'Fatal'" . " not found";
        }

        if (trim($this->getFile()) === "") {
            $errors[] = "Value " . "'File'" . " not found";
        }

        if (trim($this->getLine()) === "") {
            $errors[] = "Value " . "'Line'" . " not found";
        }

        if ((trim($this->getMessage()) === "" && trim($this->getText()) === "")) {
            $this->setText('(Error message not specified)');
        }

        if (trim($this->getSubject()) === "") {
            $errors[] = "Value " . "'Subject'" . " not found";
        }

        if (trim($this->getType()) === "") {
            $errors[] = "Value " . "'Type'" . " not found";
        }

        return $errors;
    }
}
