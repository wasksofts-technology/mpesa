<?php

namespace Wasksofts\Mpesa;

class Config
{
    static private $instance = NULL;
    private $settings;
    private $updated = FALSE;

    public static function getInstance()
    {
        if (self::$instance == NULL) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    public function get($name)
    {
        if (isset($this->settings[$name])) {
            return $this->settings[$name];
        } else {
            return (NULL);
        }
    }

    public function set($name, $value)
    {
        if (
            !isset($this->settings[$name]) or ($this->settings[$name] != $value)
        ) {
            $this->settings[$name] = $value;
            $this->updated = TRUE;
        }
    }
}
