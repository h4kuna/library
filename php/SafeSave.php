<?php

class SafeSave extends Object
{
    private $safe;

    public function __construct(&$path, $mode='w', $options=STREAM_USE_PATH)
    {
        $this->safe =new SafeStream();
        $this->safe->register();
        $this->safe->stream_open(SafeStream::PROTOCOL .'://'. $path, $mode, $options, $path);
    }

    /**
     *
     * @param $function
     * @param $args
     * @return SafeStream
     */
    public function stream_write($data)
    {
        return $this->safe->stream_write($data);
    }

    public function __destruct()
    {
        $this->safe->stream_close();
    }
}
