<?php

namespace Dacastro4\LaravelGmail\Services;

use Illuminate\Support\Collection;

class ThreadCollection extends Collection
{
	/**
	 * @var Message
	 */
	private $thread;

    /**
     * MessageCollection constructor.
     *
     * @param array $items
     * @param Thread|null $thread
     */
	public function __construct( $items = [], Thread $thread = null )
	{
		parent::__construct( $items );
		$this->thread = $thread;
	}

	public function next()
	{
		return $this->thread->next();
	}

	/**
	 * Returns boolean if the page token variable is null or not
	 *
	 * @return bool
	 */
	public function hasNextPage()
	{
		return !!$this->thread->pageToken;
	}
}
