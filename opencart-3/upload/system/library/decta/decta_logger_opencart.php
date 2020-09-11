<?php

Class DectaLoggerOpencart {
    public function __construct($oc_logger, $enabled = true) {
        $this->enabled = $enabled;
        $this->logger = $oc_logger;
    }

    public function log($message) {
        if ($this->enabled) {
            $this->logger->write($message);
        }
    }
}