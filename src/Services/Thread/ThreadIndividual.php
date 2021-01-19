<?php

namespace Dacastro4\LaravelGmail\Services\Thread;

use Carbon\Carbon;
use Dacastro4\LaravelGmail\GmailConnection;
use Dacastro4\LaravelGmail\Traits\HasDecodableBody;
use Dacastro4\LaravelGmail\Traits\HasParts;
use Dacastro4\LaravelGmail\Traits\Modifiable;
use Dacastro4\LaravelGmail\Traits\Replyable;
use Google_Service_Gmail;
use Google_Service_Gmail_MessagePart;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Class single thread
 *
 * @package Dacastro4\LaravelGmail\services
 */
class ThreadIndividual extends GmailConnection
{

	use HasDecodableBody;

	/**
	 * @var
	 */
	public $id;

    /**
     * @var
     */
    public $messages;

	/**
	 * @var Google_Service_Gmail
	 */
	public $service;

    /**
     * singleThread constructor.
     *
     * @param \Google_Service_Gmail_Thread|null $thread
     * @param bool $preload
     * @param int $userId
     */
	public function __construct(\Google_Service_Gmail_Thread $thread = null, $preload = false, $userId = null)
	{
		$this->service = new Google_Service_Gmail($this);
		parent::__construct(config(), $userId);

		if (!is_null($thread)) {
			if ($preload) {
				$thread = $this->service->users_threads->get('me', $thread->getId());
			}

			$this->setMessage($thread);

//			if ($preload) {
//				$this->setMetadata();
//			}
		}
	}

    /**
     * Sets data from thread
     *
     * @param \Google_Service_Gmail_Thread $threads
     */
	protected function setMessage(\Google_Service_Gmail_Thread $threads)
	{
		$this->id = $threads->getId();
		$this->messages = $threads->getMessages();
	}

}
