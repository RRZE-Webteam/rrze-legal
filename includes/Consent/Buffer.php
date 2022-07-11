<?php

namespace RRZE\Legal\Consent;

class Buffer
{
    /**
     * Singleton instance.
     * @var mixed
     */
    private static $instance;

    private $buffer = '';

    private $bufferActive = false;

    /**
     * Singleton
     * @return object
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct()
    {
        // Nothing to do here.
    }

    /**
     * Get the buffer content.
     */
    public function &getBuffer()
    {
        $this->buffer = ob_get_contents();
        return $this->buffer;
    }

    /**
     * End & clean the buffer.
     */
    public function endBuffering()
    {
        ob_end_clean();
        echo $this->buffer;
        unset($this->buffer);
        $this->bufferActive = false;
    }

    /**
     * Handle the buffer.
     */
    public function handleBuffering()
    {
        $this->startBuffering();
    }

    /**
     * Is the buffer active?
     * @return boolean True if the buffer is active.
     */
    public function isBufferActive()
    {
        return $this->bufferActive;
    }

    /**
     * Start buffering.
     */
    public function startBuffering()
    {
        if (ScriptBlocker::getInstance()->hasScriptBlocker()) {
            ob_start();
            // Allow to disable the buffering.
            $this->bufferActive = apply_filters('rrze_legal_consent_buffer_active', true);
        }
    }
}
