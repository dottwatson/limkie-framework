<?php 
namespace Limkie\Console;

use Garden\Cli\StreamLogger as StreamLoggerCore;
use Garden\Cli\TaskLogger;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Psr\Log\LogLevel;


class StreamLogger extends StreamLoggerCore{
    
    private $customWraps = [
        LogLevel::DEBUG => ["\033[0;37m", "\033[0m"],
        LogLevel::INFO => ['', ''],
        LogLevel::NOTICE => ["\033[0;36m", "\033[0m"],
        LogLevel::WARNING => ["\033[0;33m", "\033[0m"],
        LogLevel::ERROR => ["\033[0;31m", "\033[0m"],
        LogLevel::CRITICAL => ["\033[41m", "\033[0m"],
        LogLevel::ALERT => ["\033[30;43m", "\033[0m"],
        LogLevel::EMERGENCY => ["\033[1;35m", "\033[0m"],
        'success' => ["\033[0;32m", "\033[0m"],
    ];


    private $customOutputHandle;

    private $customInBegin;
    

    public function __construct($out = STDOUT){
        parent::__construct($out);

        $this->customOutputHandle = $out;
    }


    
    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function log($level, $message, array $context = array()) {
        if (!isset($this->customWraps[$level])) {
            throw new InvalidArgumentException("Invalid log level: $level", 400);
        }

        $msg = $this->customReplaceContext($message, $context);

        $eol = true;
        $fullLine = true;
        $str = ''; // queue everything in a string to avoid race conditions

        if ($this->bufferBegins()) {
            if (!empty($context[TaskLogger::FIELD_BEGIN])) {
                if ($this->customInBegin) {
                    $str .= $this->getEol();
                } else {
                    $this->customInBegin = true;
                }
                $eol = false;
            } elseif (!empty($context[TaskLogger::FIELD_END]) && strpos($msg, "\n") === false) {
                if ($this->customInBegin) {
                    $msg = ' '.$msg;
                    $fullLine = false;
                    $this->customInBegin = false;
                }
            } elseif ($this->customInBegin) {
                $str .= $this->getEol();
                $this->customInBegin = false;
            }
        }

        $str .= $this->customFullMessageStr($level, $msg, $context, $fullLine);

        if ($eol) {
            $str .= $this->getEol();
        }

        if (!is_resource($this->customOutputHandle) || feof($this->customOutputHandle)) {
            trigger_error('The StreamLogger output handle is not valid.', E_USER_WARNING);
        } else {
            fwrite($this->customOutputHandle, $str);
        }
    }

    /**
     * Replace a message format with context information.
     *
     * The message format contains fields wrapped in curly braces.
     *
     * @param string $format The message format to replace.
     * @param array $context The context data.
     * @return string Returns the formatted message.
     */
    private function customReplaceContext(string $format, array $context): string {
        $msg = preg_replace_callback('`({[^\s{}]+})`', function ($m) use ($context) {
            $field = trim($m[1], '{}');
            if (array_key_exists($field, $context)) {
                return $context[$field];
            } else {
                return $m[1];
            }
        }, $format);
        return $msg;
    }

    /**
     * Format a full message string.
     *
     * @param string $level The logging level.
     * @param string $message The message to format.
     * @param array $context Variable replacements for the message.
     * @param bool $fullLine Whether or not this is a full line message.
     * @return string Returns a formatted message.
     */
    private function customFullMessageStr(string $level, string $message, array $context, bool $fullLine = true): string {
        $levelStr = call_user_func($this->getLevelFormat(), $level);

        $timeStr = call_user_func($this->getTimeFormat(), $context[TaskLogger::FIELD_TIME] ?? microtime(true));

        $indent = $context[TaskLogger::FIELD_INDENT] ?? 0;
        if ($indent <= 0) {
            $indentStr = '';
        } else {
            $indentStr = str_repeat('  ', $indent - 1).'- ';
        }

        // Explode on "\n" because the input string may have a variety of newlines.
        $lines = explode("\n", $message);
        if ($fullLine) {
            foreach ($lines as &$line) {
                $line = rtrim($line);
                $line = $this->customReplaceContext($this->getLineFormat(), [
                    'level' => $levelStr,
                    'time' => $timeStr,
                    'message' => $indentStr.$line
                ]);
            }
        }
        $result = implode($this->getEol(), $lines);

        $wrap = $this->customWraps[$level] ?? ['', ''];
        $result = $this->customFormatString($result, $wrap);

        if (isset($context[TaskLogger::FIELD_DURATION]) && $this->showDurations()) {
            if ($result && !preg_match('`\s$`', $result)) {
                $result .= ' ';
            }

            $result .= $this->customFormatString($this->customFormatDuration($context[TaskLogger::FIELD_DURATION]), ["\033[1;34m", "\033[0m"]);
        }

        return $result;
    }

    /**
     * Format some text for the console.
     *
     * @param string $text The text to format.
     * @param string[] $wrap The format to wrap in the form ['before', 'after'].
     * @return string Returns the string formatted according to {@link Cli::$format}.
     */
    private function customFormatString(string $text, array $wrap): string {
        if ($this->colorizeOutput()) {
            return "{$wrap[0]}$text{$wrap[1]}";
        } else {
            return $text;
        }
    }


        /**
     * Format a time duration.
     *
     * @param float $duration The duration in seconds and fractions of a second.
     * @return string Returns the duration formatted for humans.
     * @see microtime()
     */
    private function customFormatDuration(float $duration): string {
        if ($duration < 1.0e-3) {
            $n = number_format($duration * 1.0e6, 0);
            $sx = 'μs';
        } elseif ($duration < 1) {
            $n = number_format($duration * 1000, 0);
            $sx = 'ms';
        } elseif ($duration < 60) {
            $n = number_format($duration, 1);
            $sx = 's';
        } elseif ($duration < 3600) {
            $n = number_format($duration / 60, 1);
            $sx = 'm';
        } elseif ($duration < 86400) {
            $n = number_format($duration / 3600, 1);
            $sx = 'h';
        } else {
            $n = number_format($duration / 86400, 1);
            $sx = 'd';
        }

        $result = rtrim($n, '0.').$sx;
        return $result;
    }

}
?>