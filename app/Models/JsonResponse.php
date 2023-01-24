<?php
namespace App\Models;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

class JsonResponse implements \JsonSerializable
{
    const STATUS_SUCCESS = true;
    const STATUS_ERROR = false;

    /**
     * Data to be returned
     * @var mixed
     */
    private $data = [];

    /**
     * Error message in case process is not status. This will be a string.
     *
     * @var string
     */
    private $message = '';

    /**
     * @var bool
     */
    private $status = false;

    /**
     * JsonResponse constructor.
     * @param mixed $data
     * @param string $message
     */
    public function __construct($data = [], string $error = '')
    {
        if ($this->shouldBeJson($data)) {
            $this->data = $data;
        }

        $this->error = $error;
        $this->status = !empty($data);
    }


    /**
     * Success with data
     *
     * @param array $data
     */
    public function status($data = [])
    {
        $this->status = true;
        $this->data = $data;
        $this->message = '';
    }

    /**
     * Fail with message message
     * @param string $message
     */
    public function fail($message = '')
    {
        $this->status = false;
        $this->message = $message;
        $this->data = [];
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        return [
            'status' => $this->status,
            'data' => $this->data,
            'message' => $this->message,
        ];
    }


    /**
     * Determine if the given content should be turned into JSON.
     *
     * @param  mixed  $content
     * @return bool
     */
    private function shouldBeJson($content): bool
    {
        return $content instanceof Arrayable ||
            $content instanceof Jsonable ||
            $content instanceof \ArrayObject ||
            $content instanceof \JsonSerializable ||
            is_array($content);
    }
}
