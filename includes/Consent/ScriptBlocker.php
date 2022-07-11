<?php

namespace RRZE\Legal\Consent;

use stdClass;
use function RRZE\Legal\consentCookies;

class ScriptBlocker
{
    /**
     * Singleton instance.
     * @var mixed
     */
    private static $instance;

    /**
     * scriptBlocker.
     *
     * (default value: [])
     *
     * @var mixed
     */
    private $scriptBlocker = [];

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
        // Get all active script blocker
        $this->getScriptBlocker();
    }

    /**
     * blockHandles function.
     *
     * @param mixed $tag
     * @param mixed $handle
     * @param mixed $src
     */
    public function blockHandles($tag, $handle, $src)
    {
        if (Buffer::getInstance()->isBufferActive()) {
            if (!empty($this->scriptBlocker)) {
                foreach ($this->scriptBlocker as $data) {
                    if (!empty($data->handles)) {
                        if (
                            $handle !== 'rrze_legal_consent_banner' && $handle !== 'rrze_legal_consent_banner_prioritize'
                            && in_array(
                                $handle,
                                $data->handles,
                                true
                            )
                        ) {
                            $tag = str_replace(
                                [
                                    'text/javascript',
                                    'application/javascript',
                                    '<script',
                                    'src=',
                                ],
                                [
                                    'text/template',
                                    'text/template',
                                    '<script data-rrzelegal-script-blocker-js-handle="' . $handle
                                        . '" data-rrzelegal-script-blocker-id="' . $data->scriptBlockerId . '"',
                                    'data-rrzelegal-script-blocker-src=',
                                ],
                                $tag
                            );
                        }
                    }
                }
            }
        }

        return $tag;
    }

    /**
     * getScriptBlocker function.
     */
    public function getScriptBlocker()
    {
        $categories = consentCookies()->getAllCookieCategories();
        foreach ($categories as $key => $category) {
            if ($key === 'essential') {
                continue;
            } elseif (empty($category['cookies'])) {
                continue;
            }
            foreach ($category['cookies'] as $cookieData) {
                $scriptBlockerId = $cookieData['id'] ?? '';
                $handles = self::prepareHandlesList($cookieData['enqueued_script_handles'] ?? []);
                if (!empty($handles) && !empty($cookieData['block_enqueued_script'])) {
                    $this->scriptBlocker[$scriptBlockerId] = new stdClass();
                    $this->scriptBlocker[$scriptBlockerId]->scriptBlockerId = $scriptBlockerId;
                    $this->scriptBlocker[$scriptBlockerId]->handles = $handles;
                    $this->scriptBlocker[$scriptBlockerId]->blockPhrases = [];
                }
            }
        }
    }

    /**
     * Prepare handles list function.
     * @param mixed $handles
     */
    public static function prepareHandlesList($handles)
    {
        $handlesList = [];
        if (!empty($handles) && is_string($handles)) {
            $handles = explode(',', $handles);
            if (!empty($handles)) {
                foreach ($handles as $handle) {
                    $handle = trim($handle);
                    if (!empty($handle)) {
                        $handlesList[$handle] = $handle;
                    }
                }
            }
        }
        return $handlesList;
    }

    /**
     * handleJavaScriptTagBlocking function.
     */
    public function handleJavaScriptTagBlocking()
    {
        if (Buffer::getInstance()->isBufferActive()) {
            $buffer = &Buffer::getInstance()->getBuffer();
            $buffer = preg_replace_callback('/<script.*<\/script>/Us', [$this, 'blockJavaScriptTag'], $buffer);
            Buffer::getInstance()->endBuffering();
        }
    }

    /**
     * blockJavaScriptTag function.
     *
     * @param mixed $tag
     */
    public function blockJavaScriptTag($tag)
    {
        if (!empty($this->scriptBlocker)) {
            foreach ($this->scriptBlocker as $data) {
                // @todo
            }
        }
        return $tag[0];
    }

    /**
     * hasScriptBlocker function.
     */
    public function hasScriptBlocker()
    {
        return !empty($this->scriptBlocker) ? true : false;
    }
}
